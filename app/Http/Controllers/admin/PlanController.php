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
        $plans = Plan::with('features')->withCount('features')->latest()->get();

        return view('plans.planlist', compact('plans'));
    }

    public function create()
    {
        return view('plans.addplan');
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);
        $features  = $this->sanitizeFeatures($request->input('features', []));

        DB::transaction(function () use ($validated, $features) {
            $plan = Plan::create([
                'name'          => $validated['name'],
                'price'         => $validated['price']         ?? null,
                'duration'      => $validated['duration'],
                'subtitle'      => $validated['subtitle']      ?? null,
                'user_limit'    => $validated['user_limit']    ?? null,
                'branch_limit'  => $validated['branch_limit']  ?? null,
                'storage_limit' => $validated['storage_limit'] ?? null,
                'is_active'     => $validated['is_active'],
            ]);
            $this->syncFeatures($plan, $features);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan created successfully.');
    }

    public function edit(Plan $plan)
    {
        $plan->load('features');

        return view('plans.editplan', compact('plan'));
    }

    public function show(Plan $plan)
    {
        return redirect()->route('plans.edit', $plan);
    }

    public function update(Request $request, Plan $plan)
    {
        $validated = $this->validatePlan($request);
        $features = $this->sanitizeFeatures($request->input('features', []));

        DB::transaction(function () use ($plan, $validated, $features) {
            $plan->update($validated);
            $plan->features()->delete();
            $this->syncFeatures($plan, $features);
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan updated successfully.');
    }

    public function destroy(Plan $plan)
    {
        DB::transaction(function () use ($plan) {
            $plan->features()->delete();
            $plan->delete();
        });

        return redirect()->route('plans.planlist')->with('success', 'Plan deleted successfully.');
    }

    private function validatePlan(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'price' => ['nullable', 'numeric'],
            'duration' => ['required', 'string', 'in:month,year'],
            'subtitle' => ['nullable', 'string'],
            'user_limit' => ['nullable', 'integer'],
            'branch_limit' => ['nullable', 'integer'],
            'storage_limit' => ['nullable', 'integer'],
            'is_active' => ['required', 'boolean'],
            'features' => ['nullable', 'array'],
            'features.*' => ['nullable', 'string', 'max:255'],
        ]);
    }

    private function sanitizeFeatures(array $features): array
    {
        return collect($features)
            ->map(fn ($feature) => trim((string) $feature))
            ->filter()
            ->values()
            ->all();
    }

    private function syncFeatures(Plan $plan, array $features): void
    {
        foreach ($features as $feature) {
            $plan->features()->create([
                'feature' => $feature,
            ]);
        }
    }
}
