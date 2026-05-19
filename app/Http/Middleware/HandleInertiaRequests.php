<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            // KUNCI PERBAIKAN: Membagikan data autentikasi ke Vue
            'auth' => [
                'user' => $request->user(),
            ],
            // Jika Anda menggunakan flash messages (opsional, bawaan Laravel Breeze)
            'flash' => [
                'message' => $request->session()->get('message'),
            ],
        ]);
    }
}