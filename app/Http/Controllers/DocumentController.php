<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Events\DocumentUpdated;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DocumentController extends Controller
{
    /**
     * Menampilkan Dashboard beserta daftar dokumen milik user
     */
    public function index()
    {
        // Menarik data dokumen dari database MySQL
        $documents = Document::where('user_id', auth()->id())
            ->orderBy('updated_at', 'desc')
            ->get();

        return Inertia::render('Dashboard', [
            'documents' => $documents
        ]);
    }

    public function show($id)
    {
        $document = Document::findOrFail($id);

        return Inertia::render('Editor', [
            'document' => $document
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'content' => 'nullable|string',
        ]);

        $document = Document::findOrFail($id);
        $document->update([
            'content' => $request->content
        ]);

        event(new DocumentUpdated($id, $request->content));

        return redirect()->back();
    }

    /**
     * Fungsi menyimpan dokumen baru (rute documents.store)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
        ]);

        Document::create([
            'title' => $request->title,
            'content' => '',
            'user_id' => auth()->id(),
        ]);

        return redirect()->route('dashboard');
    }
}