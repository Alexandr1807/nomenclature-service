<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the audit logs with optional filters.
     */
    public function index(Request $request): JsonResponse
    {
        $qb = AuditLog::query();

        if ($request->filled('user_id')) {
            $qb->where('user_id', $request->input('user_id'));
        }

        if ($request->filled('entity_type')) {
            $qb->where('entity_type', $request->input('entity_type'));
        }

        if ($request->filled('entity_id')) {
            $qb->where('entity_id', $request->input('entity_id'));
        }

        if ($request->filled('action')) {
            $qb->where('action', $request->input('action'));
        }

        if ($request->filled('from_date')) {
            $qb->where('created_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $qb->where('created_at', '<=', $request->input('to_date'));
        }

        $sort      = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');
        $allowed   = ['created_at', 'user_id', 'entity_type'];
        if (in_array($sort, $allowed)) {
            $qb->orderBy($sort, $direction);
        }

        $perPage   = (int) $request->input('per_page', 20);
        $paginated = $qb->paginate($perPage);

        return response()->json([
            'message'   => 'Успешно',
            'data'      => $paginated,
            'timestamp' => now()->toIso8601ZuluString(),
            'success'   => true,
        ]);
    }
}
