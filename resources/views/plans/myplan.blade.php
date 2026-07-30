@extends('layout.app')

@section('title', 'My Plan Details')

@section('content')
<div class="content">
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div class="page-title">
            <h4 style="font-weight: 700; color: #333; margin-bottom: 5px;">My Plan Details</h4>
            <h6 style="color: #777; font-weight: 400;">Overview of your active subscription plan and limits</h6>
        </div>
        <div class="d-flex gap-2">
            <!-- <a href="{{ route('plans.planlist') }}" class="btn" style="color: #f39c12; border: 1px solid #f39c12; background-color: #fff; font-weight: 600; border-radius: 6px;">
                <i class="fas fa-list me-1"></i> All Plans
            </a> -->
            <a href="javascript:history.back()" class="btn" style="background-color: #d27936; color: #fff; font-weight: 600; border-radius: 6px;">
                <i class="fas fa-arrow-left me-1"></i> Back
            </a>
        </div>
    </div>

    @php
        $planDaysRemaining = 0;
        if ($plan->end_date) {
            $endDate = \Carbon\Carbon::parse($plan->end_date)->startOfDay();
            $now = \Carbon\Carbon::now()->startOfDay();
            $planDaysRemaining = $now->diffInDays($endDate, false); 
        }
    @endphp

    <div class="card mb-4 border-0" style="background: linear-gradient(to right, #24355c, #d27936); color: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
        <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap">
            <div class="mb-3 mb-md-0">
                <div class="d-flex align-items-center mb-3">
                    <h2 class="mb-0 text-white me-3 text-capitalize" style="font-weight: 700;">{{ $plan->name }}</h2>
                    @if($planDaysRemaining <= 30)
                    <span class="badge rounded-pill" style="background-color: rgba(243, 156, 18, 0.2); color: #ffd166; border: 1px solid #ffd166; padding: 6px 12px; font-weight: 600;">
                        <i class="fas fa-exclamation-triangle me-1"></i> Expiring Soon
                    </span>
                    @endif
                </div>
                <p class="mb-4 text-capitalize" style="opacity: 0.8; font-size: 14px;">{{ $plan->subtitle ?? '' }}</p>
                <div class="d-flex gap-4" style="font-size: 13px; font-weight: 500;">
                    <div><i class="far fa-calendar-alt" style="color: #ffd166;"></i> Start Date: {{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d-m-Y') : 'N/A' }}</div>
                    <div><i class="far fa-calendar-alt" style="color: #ffd166;"></i> End Date: {{ $plan->end_date ? \Carbon\Carbon::parse($plan->end_date)->format('d-m-Y') : 'N/A' }}</div>
                </div>
            </div>
            
            <div style="background-color: rgba(255,255,255,0.15); padding: 15px 30px; border-radius: 8px; text-align: center; border: 1px solid rgba(255,255,255,0.1);">
                <div style="font-size: 11px; text-transform: uppercase; letter-spacing: 1px; opacity: 0.8; margin-bottom: 5px; font-weight: 600;">Total Amount</div>
                <h2 class="mb-1 text-white" style="font-weight: 700;">₹{{ number_format($plan->total_amount ?? ($plan->price ?? 0), 2) }}</h2>
                <div style="font-size: 13px; color: #ffd166; font-weight: 500; margin-bottom: 5px;">
                    @if($plan->duration == 'month')
                        1 Month
                    @elseif($plan->duration == 'year')
                        1 Year
                    @else
                        {{ $plan->duration }}
                    @endif
                </div>
                <!-- <div style="font-size: 12px; opacity: 0.8; font-weight: 500;">
                    (Price: ₹{{ number_format($plan->price ?? 0, 2) }})
                </div> -->
            </div>
        </div>
    </div>



    <div class="card border-0 shadow-sm" style="border-radius: 12px; border: 1px solid #f1f1f1 !important;">
        <div class="card-header bg-white border-bottom-0 pt-4 pb-2 px-4">
            <h5 style="font-weight: 700; color: #333; font-size: 16px;">
                <i class="fas fa-check-circle text-success me-2" style="font-size: 18px;"></i> Included Features
            </h5>
        </div>
        <div class="card-body p-4">
            <div class="row">
            @if(is_array($plan->features) && count($plan->features) > 0)
                @foreach($plan->features as $feature)
                    <div class="col-md-3 mb-3">
                        <div class="p-3" style="background-color: #fafbfc; border-radius: 8px; border: 1px solid #f1f1f1; color: #555; font-weight: 500;">
                            <i class="fas fa-check text-success me-2" style="background-color: #e6f9ed; padding: 5px; border-radius: 50%; font-size: 10px;"></i> {{ $feature }}
                        </div>
                    </div>
                @endforeach
            @else
                <div class="col-md-12 mb-3">
                    <div class="p-3" style="background-color: #fafbfc; border-radius: 8px; border: 1px solid #f1f1f1; color: #555; font-weight: 500;">
                        <i class="fas fa-check text-success me-2" style="background-color: #e6f9ed; padding: 5px; border-radius: 50%; font-size: 10px;"></i> No specific features listed for this plan.
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>
</div>
@endsection
