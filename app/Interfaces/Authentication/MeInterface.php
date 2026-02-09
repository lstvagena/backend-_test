<?php 

namespace App\Interfaces\Authentication;

interface MeInterface
{
    public function getPersonalAccessToken($token);
    public function getUserMenuCodes($userId);
    public function getAccessibleMenus($userMenuCodes);
    public function getParentMenus($newParentCodes);
    public function getChildMenus($parentCodes);
    public function getUserMenuActionIds($userId);
}