<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Private channel untuk notifikasi guru.
| Hanya guru dengan ID yang sesuai yang dapat subscribe ke channel-nya.
|
*/

Broadcast::channel('guru.{guruId}', function ($user, int $guruId) {
    return (int) $user->id === $guruId;
});