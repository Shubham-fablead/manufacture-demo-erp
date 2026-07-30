<?php

namespace App\Http\Controllers\admin;

use App\Models\Plan;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PlanController extends Controller
{
    public function index()
    {
        $plans = Plan::latest()->get();

        return view('plans.planlist', compact('plans'));
    }

    public function create()
    {
        return view('plans.addplan');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        DB::transaction(function () use ($validated, $featuresArray) {
            $plan = Plan::create([
                'name'          => $validated['name'],
                'price'         => $validated['price']         ?? null,
                'total_amount'  => $validated['total_amount']  ?? null,
                'duration'      => $validated['duration'],
                'subtitle'      => $validated['subtitle']      ?? null,
                'user_limit'    => $validated['user_limit']    ?? null,
                'branch_limit'  => $validated['branch_limit']  ?? null,
                'storage_limit' => $validated['storage_limit'] ?? null,
                'is_active'     => $validated['is_active'],
                'features'      => $featuresArray,
            ]);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        return view('plans.editplan', compact('plan'));
    }

    public function show(Plan $plan)
    {
        return redirect()->route('plans.edit', $plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request);
        $featuresArray = [];
        if (!empty($validated['features'])) {
            $featuresArray = array_values(array_filter(array_map('trim', preg_split('/[\n,]+/', $validated['features']))));
        }

        DB::transaction(function () use ($plan, $validated, $featuresArray) {
            $updateData = $validated;
            $updateData['features'] = $featuresArray;
            $plan->update($updateData);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        DB::transaction(function () use ($plan) {
            $plan->delete();
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan deleted successfully.');
    }

    public function myplan()
    {
        $user = auth()->user();
        if (!$user->plan_id) {
            return redirect()->route('auth.profile')->with('error', 'No active plan found.');
        }

        $plan = Plan::find($user->plan_id);

        return view('plans.myplan', compact('user', 'plan'));
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric'],
            'total_amount' => ['nullable', 'numeric'],
            'duration' => ['required', 'string', 'in:month,year'],
            'subtitle' => ['nullable', 'string'],
            'user_limit' => ['nullable', 'integer'],
            'branch_limit' => ['nullable', 'integer'],
            'storage_limit' => ['nullable', 'integer'],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'string'],
        ]);
    }


}
