<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    public function edit()
    {
        // JIKA sedang login pakai ID user, JIKA belum (testing) paksa pakai ID 1
        $userId = auth()->check() ? auth()->id() : 1;

        $document = Document::firstWhere('user_id', $userId);

        return Inertia::render('Editor', [
            'document' => $document,
        ]);
    }

    public function save(Request $request)
    {
        // 1. Ambil konten teks dari editor
        $content = $request->input('content');

        try {
            // JIKA sedang login pakai ID user, JIKA belum (testing) paksa pakai ID 1
            $userId = auth()->check() ? auth()->id() : 1;

            $document = Document::firstOrNew([
                'user_id' => $userId,
            ]);

            if (! $document->exists) {
                $document->title = 'Untitled Document';
            }

            $document->content = $content;
            $document->save();

            return response()->json([
                'status' => 'success',
                'message' => 'Data sukses disimpan!'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400); 
        }
    }
}