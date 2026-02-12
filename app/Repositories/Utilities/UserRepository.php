<?php

namespace App\Repositories\Utilities;

use App\Interfaces\Utilities\UserInterface; 
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserRepository implements UserInterface
{
    public function paginateUsers($perPage)
    {
        return User::orderBy('id', 'asc')
            ->paginate($perPage);
    }   

    public function createUser($data)
    {
        $data['password'] = Hash::make($data['password']);

        return User::create($data);
    }

    public function updateUser($id, $data)
    {
        $user = User::findOrFail($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $user->update($data);

        return $user;
    }

    public function getAllUsers()
    {
        return User::orderBy('id', 'asc')->get();
    }



    public function createUserMenu($data) {}
    public function createUserMenuAction($data) {}
    public function getUsers($request) {}
    
    public function updateUserMenus($userId, $menuIds) {}
    public function updateUserMenuActions($userId, $menuActionIds) {}
    public function deleteUser($id) {}
    public function export() {}
    public function print() {}
}
