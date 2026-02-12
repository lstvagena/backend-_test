<?php

namespace App\Services\Shared;

use Laravel\Sanctum\PersonalAccessToken;
use App\Models\General\MenuAction;
use App\Models\General\UserMenuAction;

class PaginationService
{
    private function getMainTableAlias($query)
    {
        try {
            $sql = $query->toSql();
            if (preg_match('/FROM\s+[`\']?(\w+)[`\']?\s+(?:AS\s+)?[`\']?(\w+)[`\']?/i', $sql, $matches)) {
                if (isset($matches[2]) && strtolower($matches[2]) !== strtolower($matches[1])) {
                    return $matches[2];
                }
                return $matches[1];
            }
        } catch (\Exception $e) {
        }

        if (method_exists($query, 'getModel')) {
            try {
                $model = $query->getModel();
                if ($model) {
                    return $model->getTable();
                }
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    private function normalizeColumn($column, $tableAlias)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        $ambiguousColumns = ['created_at', 'updated_at', 'record_id', 'id'];

        if (in_array($column, $ambiguousColumns) && $tableAlias) {
            return $tableAlias . '.' . $column;
        }

        return $column;
    }

    public function paginate($request, $query, $searchableColumns = [])
    {
        $token = $request->cookie('auth_token');
        $menuCode = $request->get('menu_code');
        $user = PersonalAccessToken::findToken($token)->tokenable;
        $actions = [];
        $actionQuery = MenuAction::query()
            ->select('id', 'label', 'icon', 'type', 'position')
            ->where('menu_code', $menuCode);
        if ($user->user_type !== 'Supervisor') {
            $permitted = UserMenuAction::query()
                ->where('user_id', $user->user_id)
                ->pluck('user_menu_action_id');
            $actions = $actionQuery->whereIn('id', $permitted)->get();
        } else {
            $actions = $actionQuery->get();
        }
        $page = $request->get('page') ?? 1;
        $perPage = $request->get('per_page') ?? 10;
        $sortBy = $request->get('sort_by') ?? 'record_id';
        $sortOrder = $request->get('sort_order') ?? 'asc';
        $search = $request->get('search') ?? '';
        $sortOrder = in_array($sortOrder, ['asc', 'desc']) ? $sortOrder : 'asc';

        $tableAlias = $this->getMainTableAlias($query);

        $sortBy = $this->normalizeColumn($sortBy, $tableAlias);

        if (!empty($search) && !empty($searchableColumns)) {
            $query->where(function ($q) use ($search, $searchableColumns, $tableAlias) {
                foreach ($searchableColumns as $column) {
                    $normalizedColumn = $this->normalizeColumn($column, $tableAlias);
                    $q->orWhere($normalizedColumn, 'like', "%{$search}%");
                }
            });
        }
        $paginated = $query->orderBy($sortBy, $sortOrder)
            ->paginate($perPage, ['*'], 'page', $page);
        if ($paginated->isEmpty() && $page > 1) {
            $lastPage = $paginated->lastPage();
            if ($lastPage < $page) {
                $paginated = $query->orderBy($sortBy, $sortOrder)
                    ->paginate($perPage, ['*'], 'page', $lastPage);
            }
        }
        return [
            'items' => $paginated->items(),
            'items_per_page' => $paginated->perPage(),
            'total_pages' => $paginated->lastPage(),
            'current_page' => $paginated->currentPage(),
            'next_page' => $paginated->currentPage() < $paginated->lastPage()
                ? $paginated->currentPage() + 1
                : null,
            'previous_page' => $paginated->currentPage() > 1
                ? $paginated->currentPage() - 1
                : null,
            'has_next_page' => $paginated->hasMorePages(),
            'has_previous_page' => $paginated->currentPage() > 1,
            'actions' => $actions,
        ];
    }
}
