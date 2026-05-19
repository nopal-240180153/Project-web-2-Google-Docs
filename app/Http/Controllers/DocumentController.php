<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    // Menampilkan halaman depan / welcome jika user belum login
    public function welcome()
    {
        return Inertia::render('Welcome', [
            'canLogin' => Route::has('login'),
            'canRegister' => Route::has('register'),
        ]);
    }

    // Menampilkan daftar dokumen di Dashboard resmi
    public function index()
    {
        return Inertia::render('Dashboard', [
            'documents' => Document::where('user_id', auth()->id())->latest()->get()
        ]);
    }

    // Membuat dokumen baru dari Dashboard
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        $document = Document::create([
            'title' => $request->title,
            'content' => '',
            'user_id' => auth()->id(), 
        ]);

        return redirect()->route('editor.show', $document->id);
    }

    // Membuka halaman editor (Bisa diakses Tamu & User Login)
    public function show($id)
    {
        $document = Document::findOrFail($id);

        return Inertia::render('Editor', [
            'document' => $document,
            'isLoggedIn' => auth()->check(), 
        ]);
    }

    // Menyimpan konten dokumen saat user mengetik (Auto-save)
    public function update(Request $request, $id)
    {
        $document = Document::findOrFail($id);

        // Validasi konten
        $request->validate([
            'content' => 'nullable|string',
        ]);

        // Update konten dokumen
        $document->update([
            'content' => $request->content ?? ''
        ]);

        return response()->json([
            'status' => 'Tersimpan',
            'message' => 'Dokumen berhasil disimpan',
            'saved_at' => $document->updated_at,
        ]);
    }
}