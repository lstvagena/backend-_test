<?php

namespace App\Repositories\Reports;

use Illuminate\Support\Collection;

abstract class BaseReportRepository
{
    abstract public static function getData(array $filters): array;

    abstract public static function transformToRows(array $data, array $filters): Collection;

    protected static function applyCommonFilters($query, array $filters): void
    {
        if (!empty($filters['dept_from']) && !empty($filters['dept_to'])) {
            $range = [$filters['dept_from'], $filters['dept_to']];
            sort($range, SORT_STRING);
            $query->whereBetween('dept.department_desc', $range);
        }

        if (!empty($filters['div_from']) && !empty($filters['div_to'])) {
            $range = [$filters['div_from'], $filters['div_to']];
            sort($range, SORT_STRING);
            $query->whereBetween('div.division_desc', $range);
        }

        if (!empty($filters['branch'])) {
            $branchRecord = \Illuminate\Support\Facades\DB::table('mf_branches')
                ->where('branch_desc', $filters['branch'])
                ->orWhere('branch_id', $filters['branch'])
                ->first();
            if ($branchRecord) {
                $query->where('emp.branch_id', $branchRecord->branch_id);
            }
        }

        if (!empty($filters['emp_from']) && !empty($filters['emp_to'])) {
            $searchBy = $filters['search_by'] ?? 'name';
            $field = match ($searchBy) {
                'code' => 'employee_code',
                'name' => 'full_name',
                default => 'full_name',
            };
            $range = [$filters['emp_from'], $filters['emp_to']];
            sort($range, SORT_STRING);
            $query->whereBetween("emp.$field", $range);
        }
    }
}
