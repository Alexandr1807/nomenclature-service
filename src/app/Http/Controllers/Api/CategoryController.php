<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use App\Traits\Auditable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
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
     * Отдаём иерархию категорий (дерево) или фильтрованный список.
     */
    public function index(Request $request): JsonResponse
    {
        $qb = Category::query();

        if ($request->filled('name')) {
            $qb->where('name', $request->input('name'));
        }
        if ($request->filled('search')) {
            $s = $request->input('search');
            $qb->where('name', 'ILIKE', "%{$s}%");
        }

        $sort      = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $allowed   = ['name','created_at'];
        if (in_array($sort, $allowed)) {
            $qb->orderBy($sort, $direction);
        }

        if ($request->boolean('hierarchy')) {
            $tree = $qb->whereNull('parent_id')
                ->with('childrenRecursive')
                ->get();
            return $this->success($tree);
        }

        if ($request->boolean('all')) {
            $all = $qb->get();
            return $this->success($all);
        }

        $perPage   = (int)$request->input('per_page', 15);
        $paginated = $qb->paginate($perPage);

        return $this->success($paginated);
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = Category::create($request->validated());
        return $this->success($category, 'Запись создана', 200);
    }

    public function show(string $id): JsonResponse
    {
        $category = Category::where('id', $id)->first();
        if (! $category) {
            return $this->error('Запись не найдена.', null, 404);
        }
        $category->load('children');
        return $this->success($category);
    }

    public function update(StoreCategoryRequest $request, string $id): JsonResponse
    {
        $category = Category::where('id', $id)->first();
        if (! $category) {
            return $this->error('Запись не найдена.', null, 404);
        }

        $data = $request->validated();

        $data['updated_by'] = auth()->id();

        $category->update($data);

        $category->refresh();

        return $this->success($category, 'Обновлено');
    }

    public function destroy(string $id): JsonResponse
    {
        $category = Category::where('id', $id)->first();
        if (! $category) {
            return $this->error('Запись не найдена.', null, 404);
        }

        Category::where('parent_id', $category->id)
            ->update(['parent_id' => null]);

        $category->delete();

        return $this->success([], 'Запись удалена', 200);
    }
}
