<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Imports\ProductsCsvImport;
use App\Models\Product;
use App\Traits\Auditable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    use Auditable;

    protected function success($data, string $message = 'Успешно', int $status = 200): JsonResponse
    {
        return response()->json([
            'message'   => $message,
            'data'      => $data,
            'timestamp' => now()->toIso8601ZuluString(),
            'success'   => true,
        ], $status);
    }

    protected function error(string $message, $errors = [], int $status = 422): JsonResponse
    {
        return response()->json([
            'message'   => $message,
            'data'      => $errors,
            'timestamp' => now()->toIso8601ZuluString(),
            'success'   => false,
        ], $status);
    }

    /**
     * Display a paginated list of active products.
     */
    public function index(Request $request)
    {
        $qb = Product::query();

        foreach (['name', 'category_id', 'supplier_id', 'price'] as $field) {
            if ($request->filled($field)) {
                $qb->where($field, $request->input($field));
            }
        }

        if ($request->filled('search')) {
            $s = $request->input('search');
            $qb->where(function($q) use ($s) {
                $q->where('name', 'ILIKE', "%{$s}%")
                    ->orWhere('description', 'ILIKE', "%{$s}%");
            });
        }

        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $allowed = ['name', 'price', 'created_at'];
        if (in_array($sort, $allowed)) {
            $qb->orderBy($sort, $direction);
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginated = $qb->paginate($perPage);

        return $this->success($paginated);
    }

    /**
     * Store a newly created product.
     */
    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->storePublicly('products', 's3');
            $data['file_url'] = Storage::disk('s3')->url($path);
        }

        $product = Product::create($data);

        return $this->success($product, 'Создано', 201);
    }

    /**
     * Display the specified product.
     */
    public function show(string $id): JsonResponse
    {
        $product = Product::where('id', $id)->first();

        if (! $product) {
            return $this->error('Запись не найдена.', null, 404);
        }

        return $this->success($product);
    }

    /**
     * Update the specified product.
     */
    public function update(UpdateProductRequest $request, string $id): JsonResponse
    {
        $product = Product::where('id', $id)->first();

        if (! $product) {
            return $this->error('Запись не найдена.', null, 404);
        }

        $data = $request->validated();

        if ($request->hasFile('file')) {
            $path = $request->file('file')->store('products', 's3');
            Storage::disk('s3')->setVisibility($path, 'public');
            $data['file_url'] = Storage::disk('s3')->url($path);
        }

        $all = array_merge(
            $product->only(['name','description','category_id','supplier_id','price','file_url']),
            $data,
            [
                'updated_by' => auth()->id(),
                'updated_at' => now()->toIso8601ZuluString(),
            ]
        );

        $product->update($all);
        $product->refresh();

        return $this->success($product, 'Обновлено');
    }

    /**
     * Soft delete via is_active flag.
     */
    public function destroy(string $id): JsonResponse
    {
        $product = Product::where('id', $id)->first();

        if (! $product) {
            return $this->error('Запись не найдена.', null, 404);
        }

        $product->update(['is_active' => false]);

        return $this->success([], 'Запись удалена', 200);
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        // 1) Сохраняем файл в storage/app/imports
        $path = $request->file('file')->store('imports');

        // 2) Берём ID текущего пользователя
        $userId = $request->user()->id;

        Log::info('Starting import', ['path' => $path, 'user' => $userId]);

        // 3) Запускаем синхронный import — он не упадёт в очереди, но создаст наши job-и
        Excel::import(
            new ProductsCsvImport($userId), $path
        );

        Log::info('Import finished');

        return $this->success([], 'Импорт запущен, проверьте очередь');
    }

    public function export(Request $request): StreamedResponse
    {
        $products = Product::query()
            ->when($request->filled('category_id'), fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->filled('supplier_id'), fn($q) => $q->where('supplier_id', $request->supplier_id))
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'description',
                'category_id',
                'supplier_id',
                'price',
                'file_url',
                'created_at',
            ]);

        return (new FastExcel($products))->download('products-'.now()->format('Ymd_His').'.xlsx');
    }
}
