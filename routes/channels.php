<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Kita buat channel room ini mengembalikan nilai true (selalu diizinkan masuk)
// tanpa peduli apakah user sudah login atau belum (anonim/HP)
Broadcast::channel('document-room.{id}', function () {
    return true; 
});