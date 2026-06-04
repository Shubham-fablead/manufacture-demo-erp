<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class LeaveModel extends Model
{
    use HasFactory;

    protected $table = 'leaves';
    protected $primaryKey = 'id';

    protected $fillable = [
        'firstname',
        'user_id',
        'start_date',
        'end_date',
        'from_date',
        'to_date',
        'no_of_day',
        'no_of_days',
        'reason',
        'leave_id',
        'leave_type_id',
        'created_by',
        'status',
        'half_day',
        'half_day_type',
    ];

    public $timestamps = true;

    protected $casts = [
        'user_id' => 'integer',
        'leave_id' => 'integer',
        'created_by' => 'integer',
        'no_of_day' => 'integer',
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
        'half_day' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveTypeModel::class, 'leave_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getEmployeeReport($employee_id = null, $start_date = null, $end_date = null, $year = null, $month = null, $leave_type = null, $status = null)
    {
        $usernameSelect = Schema::hasColumn('users', 'username')
            ? 'users.username as username'
            : 'users.name as username';

        $builder = DB::table('leaves')
            ->leftJoin('users', 'users.id', '=', 'leaves.user_id')
            ->leftJoin('leave_type', 'leave_type.id', '=', 'leaves.leave_type_id')
            ->selectRaw($usernameSelect)
            ->addSelect(
                'leave_type.leave_type',
                'leaves.start_date',
                'leaves.end_date',
                'leaves.status'
            );

        if (Schema::hasColumn('users', 'role')) {
            $builder->whereIn('users.role', ['staff', 'hr']);
        }

        if ($employee_id) {
            $builder->where('leaves.user_id', (int) $employee_id);
        }

        if ($start_date) {
            $builder->whereDate('leaves.start_date', '>=', $start_date);
        }

        if ($end_date) {
            $builder->whereDate('leaves.end_date', '<=', $end_date);
        }

        if ($year) {
            $builder->whereYear('leaves.start_date', (int) $year);
        }

        if ($month) {
            $builder->whereMonth('leaves.start_date', (int) $month);
        }

        if ($leave_type) {
            $builder->where('leaves.leave_type_id', (int) $leave_type);
        }

        if ($status) {
            $builder->where('leaves.status', $status);
        }

        return $builder
            ->orderByDesc('leaves.start_date')
            ->get()
            ->map(static fn ($row) => (array) $row)
            ->all();
    }

    public function getLeavesCount($userId, $month)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', $month . '-01')->startOfDay();
        $endDate = (clone $startDate)->endOfMonth()->endOfDay();

        $leaves = self::query()
            ->where('user_id', $userId)
            ->whereIn('status', ['approved', 'Approved'])
            ->whereDate('start_date', '<=', $endDate->toDateString())
            ->whereDate('end_date', '>=', $startDate->toDateString())
            ->get(['start_date', 'end_date']);

        $totalDays = 0;

        foreach ($leaves as $leave) {
            $leaveStart = Carbon::parse($leave->start_date)->startOfDay();
            $leaveEnd = Carbon::parse($leave->end_date)->endOfDay();

            $effectiveStart = $leaveStart->gt($startDate) ? $leaveStart : $startDate;
            $effectiveEnd = $leaveEnd->lt($endDate) ? $leaveEnd : $endDate;

            if ($effectiveStart->lte($effectiveEnd)) {
                $totalDays += $effectiveStart->diffInDays($effectiveEnd) + 1;
            }
        }

        return $totalDays;
    }
}
