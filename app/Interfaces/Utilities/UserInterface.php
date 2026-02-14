<?php

namespace App\Interfaces\Utilities;

interface UserInterface 
{
    public function paginateUsers(int $perPage);
    public function updateUser($id, $data);
    public function createUser($data);
    public function exportUsers(); 

    public function createUserMenu($data);
    public function createUserMenuAction($data);
    public function getUsers($request);
    
    public function updateUserMenus($userId, $menuIds);
    public function updateUserMenuActions($userId, $menuActionIds);
    public function deleteUser($id);
    public function export();
    public function print();
}