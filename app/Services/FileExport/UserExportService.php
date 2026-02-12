<?php

namespace App\Services\FileExport;

use App\Repositories\Utilities\UserRepository;
use App\Exports\UsersExport;
use Maatwebsite\Excel\Facades\Excel;
use PDF;

class UserExportService
{
    protected $repository;

    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    public function exportExcel()   
    {
        $users = $this->repository->getAllUsers();

        return Excel::download(
            new UsersExport($users),
            'users.xlsx'
        );
    }

    public function exportPdf()
    {
        $users = $this->repository->getAllUsers();

        $pdf = PDF::loadView('exports.users', [
            'users' => $users
        ]);

        return $pdf->download('users.pdf');
    }
}
