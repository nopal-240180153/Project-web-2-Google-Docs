<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
*/

// Membuka gerbang autentikasi untuk Presence Channel dokumen
Broadcast::channel('document.{documentId}', function ($user, $documentId) {
    if ($user) {
        // Presence channel wajib mengembalikan array berisi info data user
        return [
            'id' => $user->id,
            'name' => $user->name
        ];
    }
    return false;
});