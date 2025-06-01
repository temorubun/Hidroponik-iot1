<?php

namespace App\Policies;

use App\Models\Device;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DevicePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the device.
     */
    public function view(User $user, Device $device): bool
    {
        return $user->id === $device->user_id;
    }

    /**
     * Determine whether the user can create devices.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the device.
     */
    public function update(User $user, Device $device): bool
    {
        return $user->id === $device->user_id;
    }

    /**
     * Determine whether the user can delete the device.
     */
    public function delete(User $user, Device $device): bool
    {
        return $user->id === $device->user_id;
    }
} 