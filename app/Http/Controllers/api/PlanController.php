<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\PlanFeature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PlanController extends Controller
{
    /* ==========================================================
     | 1️⃣  LIST — GET /api/getAllPlans
     ========================================================== */
    public function getAllPlans(Request $request)
    {
        $perPage       = (int) $request->input('per_page', 10);
        $page          = (int) $request->input('page', 1);
        $search        = trim((string) $request->input('search', ''));
        $shouldPaginate = $request->has('page') || $request->has('per_page') || $request->filled('search');

        $subBranchId = $request->input('sub_branch_id');

        $query = Plan::query()
            ->with('features')
            ->withCount('features')
            ->when($subBranchId, fn ($q) => $q->where('sub_branch_id', $subBranchId))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('subtitle', 'LIKE', "%{$search}%");
                });
            })
            ->when($request->filled('is_active'), fn ($q) => $q->where('is_active', $request->is_active))
            ->when($request->filled('duration'),  fn ($q) => $q->where('duration', $request->duration));

        $pagination = null;

        if ($shouldPaginate) {
            $paginated  = $query->latest('id')->paginate($perPage, ['*'], 'page', $page);
            $plans      = $paginated->items();
            $pagination = [
                'current_page' => $paginated->currentPage(),
                'last_page'    => $paginated->lastPage(),
                'per_page'     => $paginated->perPage(),
                'total'        => $paginated->total(),
                'next_page_url' => $paginated->nextPageUrl(),
                'prev_page_url' => $paginated->previousPageUrl(),
            ];
        } else {
            $plans = $query->latest('id')->get();
        }

        return response()->json([
            'status'     => true,
            'data'       => $plans,
            'pagination' => $pagination,
        ]);
    }

    /* ==========================================================
     | 2️⃣  SHOW — GET /api/getPlanById/{id}
     ========================================================== */
    public function getPlanById($id)
    {
        $plan = Plan::with('features')->find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        return response()->json(['status' => true, 'plan' => $plan], 200);
    }

    /* ==========================================================
     | 3️⃣  CREATE — POST /api/createPlan
     ========================================================== */
    public function createPlan(Request $request)
    {
        /* -------------------------------------------------
         | Validation
         -------------------------------------------------*/
        $rules = [
            'name'          => 'required|string|max:255|unique:plans,name',
            'price'         => 'nullable|numeric|min:0',
            'duration'      => 'required|in:month,year',
            'subtitle'      => 'nullable|string',
            'user_limit'    => 'nullable|integer|min:0',
            'branch_limit'  => 'nullable',
            'storage_limit' => 'nullable|integer|min:0',
            'is_active'     => 'required|boolean',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string|max:255',
            'sub_branch_id' => 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'is_active'     => 'status',
            'user_limit'    => 'user limit',
            'branch_limit'  => 'branch limit',
            'storage_limit' => 'storage limit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        /* -------------------------------------------------
         | Create Plan + Features (atomic)
         -------------------------------------------------*/
        DB::transaction(function () use ($validated, &$plan) {
            $plan = Plan::create([
                'name'          => $validated['name'],
                'price'         => $validated['price']         ?? null,
                'duration'      => $validated['duration'],
                'subtitle'      => $validated['subtitle']      ?? null,
                'user_limit'    => $validated['user_limit']    ?? null,
                'branch_limit'  => $validated['branch_limit']  ?? null,
                'storage_limit' => $validated['storage_limit'] ?? null,
                'is_active'     => $validated['is_active'],
                'sub_branch_id' => $validated['sub_branch_id'] ?? null,
            ]);

            $this->syncFeatures($plan, $validated['features'] ?? []);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan created successfully',
            'plan'    => $plan->load('features'),
        ], 200);
    }

    /* ==========================================================
     | 4️⃣  UPDATE — POST /api/updatePlan
     ========================================================== */
    public function updatePlan(Request $request)
    {
        /* -------------------------------------------------
         | Validation
         -------------------------------------------------*/
        $rules = [
            'plan_id'       => 'required|exists:plans,id',
            'name'          => 'required|string|max:255|unique:plans,name,' . $request->plan_id,
            'price'         => 'nullable|numeric|min:0',
            'duration'      => 'required|in:month,year',
            'subtitle'      => 'nullable|string',
            'user_limit'    => 'nullable|integer|min:0',
            'branch_limit'  => 'nullable',
            'storage_limit' => 'nullable|integer|min:0',
            'is_active'     => 'required|boolean',
            'features'      => 'nullable|array',
            'features.*'    => 'nullable|string|max:255',
            'sub_branch_id' => 'nullable|integer',
        ];

        $validator = Validator::make($request->all(), $rules, [], [
            'plan_id'       => 'plan',
            'is_active'     => 'status',
            'user_limit'    => 'user limit',
            'branch_limit'  => 'branch limit',
            'storage_limit' => 'storage limit',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        /* -------------------------------------------------
         | Update Plan + re-sync Features (atomic)
         -------------------------------------------------*/
        $plan = Plan::findOrFail($validated['plan_id']);

        DB::transaction(function () use ($plan, $validated) {
            $plan->update([
                'name'          => $validated['name'],
                'price'         => $validated['price']         ?? null,
                'duration'      => $validated['duration'],
                'subtitle'      => $validated['subtitle']      ?? null,
                'user_limit'    => $validated['user_limit']    ?? null,
                'branch_limit'  => $validated['branch_limit']  ?? null,
                'storage_limit' => $validated['storage_limit'] ?? null,
                'is_active'     => $validated['is_active'],
                'sub_branch_id' => $validated['sub_branch_id'] ?? null,
            ]);

            // Full replace of features (mirrors admin web controller)
            $plan->features()->delete();
            $this->syncFeatures($plan, $validated['features'] ?? []);
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan updated successfully',
            'plan'    => $plan->fresh()->load('features'),
        ], 200);
    }

    /* ==========================================================
     | 5️⃣  DELETE — POST /api/deletePlan/{id}
     ========================================================== */
    public function deletePlan($id)
    {
        $plan = Plan::find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        DB::transaction(function () use ($plan) {
            $plan->features()->delete();
            $plan->delete();
        });

        return response()->json([
            'status'  => true,
            'message' => 'Plan deleted successfully',
        ], 200);
    }

    /* ==========================================================
     | 6️⃣  TOGGLE STATUS — POST /api/togglePlanStatus/{id}
     ========================================================== */
    public function togglePlanStatus($id)
    {
        $plan = Plan::find($id);

        if (! $plan) {
            return response()->json(['status' => false, 'error' => 'Plan not found'], 404);
        }

        $plan->is_active = ! $plan->is_active;
        $plan->save();

        return response()->json([
            'status'    => true,
            'message'   => 'Plan status updated successfully',
            'is_active' => $plan->is_active,
        ], 200);
    }

    /* ==========================================================
     | Private Helpers
     ========================================================== */

    /**
     * Sync feature strings to plan_features table.
     */
    private function syncFeatures(Plan $plan, array $features): void
    {
        $sanitized = collect($features)
            ->map(fn ($f) => trim((string) $f))
            ->filter()
            ->values();

        foreach ($sanitized as $feature) {
            $plan->features()->create(['feature' => $feature]);
        }
    }
}
