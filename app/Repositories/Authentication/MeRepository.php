<?php

namespace App\Repositories\Authentication;

use App\Interfaces\Authentication\MeInterface;
use Laravel\Sanctum\PersonalAccessToken;
use App\Models\General\Menu;
use App\Models\General\UserMenu;
use App\Models\General\UserMenuAction;

class MeRepository implements MeInterface
{
    public function getPersonalAccessToken($token)
    {
        return PersonalAccessToken::findToken($token);
    }

    public function getUserMenuCodes($userId)
    {
        return UserMenu::where('user_id', $userId)->pluck('menu_id')->toArray();
    }

    public function getAccessibleMenus($userMenuCodes)
    {
        return Menu::whereIn('code', $userMenuCodes)->with('actions')->get();
    }

    public function getParentMenus($newParentCodes)
    {
        return Menu::whereIn('code', $newParentCodes)
            ->with('actions')
            ->get();
    }

    public function getChildMenus($parentCodes)
    {
        return Menu::whereIn('parent_code', $parentCodes)
            ->with('actions')
            ->get();
    }

    public function getUserMenuActionIds($userId) 
    {
        return UserMenuAction::where('user_id', $userId)
            ->pluck('user_menu_action_id')
            ->toArray();
    }
}