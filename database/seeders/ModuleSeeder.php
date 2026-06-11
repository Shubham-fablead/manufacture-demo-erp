<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            ['id' => 1,  'module' => 'Products',          'created_at' => null, 'updated_at' => null],
            ['id' => 2,  'module' => 'Sales and Orders',  'created_at' => null, 'updated_at' => null],
            ['id' => 3,  'module' => 'Purchases',         'created_at' => null, 'updated_at' => null],
            ['id' => 4,  'module' => 'Invoices',          'created_at' => null, 'updated_at' => null],
            ['id' => 5,  'module' => 'Expenses',          'created_at' => null, 'updated_at' => null],
            ['id' => 6,  'module' => 'Categories',        'created_at' => null, 'updated_at' => null],
            ['id' => 8,  'module' => 'Staff',             'created_at' => null, 'updated_at' => null],
            ['id' => 9,  'module' => 'Customers',         'created_at' => null, 'updated_at' => null],
            ['id' => 10, 'module' => 'Vendors',           'created_at' => null, 'updated_at' => null],
            ['id' => 16, 'module' => 'Manage Accounting', 'created_at' => null, 'updated_at' => null],
            ['id' => 17, 'module' => 'Manage Inventory',  'created_at' => null, 'updated_at' => null],
            ['id' => 23, 'module' => 'Advance Pay',       'created_at' => null, 'updated_at' => null],
            ['id' => 26, 'module' => 'Attendance',        'created_at' => '2025-11-04 16:25:19', 'updated_at' => '2025-11-04 16:25:19'],
            ['id' => 27, 'module' => 'Transaction',       'created_at' => null, 'updated_at' => null],
            ['id' => 28, 'module' => 'Leaves',            'created_at' => null, 'updated_at' => null],
            ['id' => 29, 'module' => 'Payroll',           'created_at' => null, 'updated_at' => null],
            ['id' => 30, 'module' => 'FollowUps',         'created_at' => null, 'updated_at' => null],
            ['id' => 31, 'module' => 'Meetings',          'created_at' => null, 'updated_at' => null],
            ['id' => 32, 'module' => 'Manage Leads',      'created_at' => null, 'updated_at' => null],
        ];

        foreach ($modules as $module) {
            DB::table('modules')->updateOrInsert(
                ['id' => $module['id']],
                $module
            );
        }
    }
}
