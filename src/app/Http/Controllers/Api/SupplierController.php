<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSupplierRequest;
use App\Models\Supplier;
use App\Traits\Auditable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupplierController extends Controller
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

    public function index(Request $request)
    {
        $qb = Supplier::query();

        if ($request->filled('name')) {
            $qb->where('name', $request->input('name'));
        }
        if ($request->filled('search')) {
            $s = $request->input('search');
            $qb->where('name', 'ILIKE', "%{$s}%")
                ->orWhere('contact_name', 'ILIKE', "%{$s}%");
        }

        $sort = $request->input('sort', 'name');
        $dir  = $request->input('direction', 'asc');
        $allowed = ['name', 'created_at'];
        if (in_array($sort, $allowed)) {
            $qb->orderBy($sort, $dir);
        }

        $perPage = (int) $request->input('per_page', 15);
        $paginated = $qb->paginate($perPage);

        return $this->success($paginated);
    }

    public function store(StoreSupplierRequest $request)
    {
        $supplier = Supplier::create($request->validated());
        return $this->success($supplier, 'Запись создана', 201);
    }

    public function show(string $id): JsonResponse
    {
        $supplier = Supplier::where('id', $id)->first();

        if (! $supplier) {
            return $this->error('Запись не найдена.', null, 404);
        }

        return $this->success($supplier);
    }

    /**
     * Обновляем поставщика.
     */
    public function update(StoreSupplierRequest $request, string $id): JsonResponse
    {
        $supplier = Supplier::where('id', $id)->first();

        if (! $supplier) {
            return $this->error('Запись не найдена.', null, 404);
        }

        $supplier->update($request->validated());

        return $this->success($supplier, 'Запись обновлена');
    }

    /**
     * Удаляем поставщика.
     */
    public function destroy(string $id): JsonResponse
    {
        $supplier = Supplier::where('id', $id)->first();

        if (! $supplier) {
            return $this->error('Запись не найдена.', null, 404);
        }

        $supplier->delete();

        return $this->success([], 'Запись удалена', 200);
    }
}
