<?php

namespace App\Services\Shared;

use App\Services\Shared\PaginationService;
use App\Helpers\QueryResultHelper;
use App\Models\Masterfile\Payroll\DefTaxClass;
use App\Models\Employee201\Transaction\MfOffboardingStatus;
use App\Models\Masterfile\Employees\MfSeparationReason;
use App\Http\Requests\Api\V1\Shared\PaginationRequest;

class DropDownService
{
    protected $paginationService;

    public function __construct(PaginationService $paginationService)
    {
        $this->paginationService = $paginationService;
    }

    public function fetchTaxClass($request): array
    {
        $typeMap = [
            'allowance' => 'mf_allowance',
            'leave' => 'mf_leave',
            'deduction' => 'mf_other_deduction',
            'earning' => 'mf_other_earning',
            'piece_rate' => 'mf_piece_rate',
            'rate' => 'mf_rate',
        ];

        $type = $request->get('type');

        if (!isset($typeMap[$type])) {
            return QueryResultHelper::onCustomError("Invalid Type");
        }

        $query = DefTaxClass::where($typeMap[$type], 1);
        $searchableColumns = ['tax_class_desc'];
        $result = $this->paginationService->paginate($request, $query, $searchableColumns);
        return QueryResultHelper::onSuccessGet($result);
    }

    public function fetchOffboardingStatus($request)
    {
        $query = MfOffboardingStatus::query()
            ->select('offboarding_status_id', 'offboarding_status_desc');
        $searchableColumns = ['offboarding_status_desc'];
        $result = $this->paginationService->paginate($request, $query, $searchableColumns);
        return QueryResultHelper::onSuccessGet($result);
    }

    public function fetchSeparationReason($request)
    {
        $query = MfSeparationReason::query()
            ->select('separation_reason_id', 'separation_reason_desc', 'record_id');
        $searchableColumns = ['separation_reason_desc'];
        $result = $this->paginationService->paginate($request, $query, $searchableColumns);
        return QueryResultHelper::onSuccessGet($result);
    }
}
