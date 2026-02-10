<?php

namespace App\Repositories\Utilities;

use App\Interfaces\Utilities\UserInterface; 
use App\Models\User;

class UserRepository implements UserInterface
{
    public function paginateUsers($perPage)
    {
        return User::orderBy('id', 'asc')
            ->paginate($perPage);
    }

    // REQUIRED because interface defines them
    public function createUser($data) {}
    public function createUserMenu($data) {}
    public function createUserMenuAction($data) {}
    public function getUsers($request) {}
    public function updateUser($id, $data) {}
    public function updateUserMenus($userId, $menuIds) {}
    public function updateUserMenuActions($userId, $menuActionIds) {}
    public function deleteUser($id) {}
    public function export() {}
    public function print() {}
}
