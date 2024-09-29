<?php

namespace App\Policies;

use App\Models\User;

class AssetPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function manageAssets(User $user)
    {
        return $user->isAdmin(); // Replace with your admin check logic
    }

}
