<?php
// =============================================
// app/Http/Controllers/Admin/TemplateSuratController.php
// =============================================

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TemplateSuratController extends Controller
{
    // Folder tempat simpan template
    const FOLDER = 'templates';

    public function index()
    {
        // Ambil semua file di folder templates
        $files = collect(Storage::disk('public')->files(self::FOLDER))
            ->map(function ($path) {
                return [
                    'path'     => $path,
                    'nama'     => basename($path),
                    'ukuran'   => $this->formatBytes(Storage::disk('public')->size($path)),
                    'diupload' => \Carbon\Carbon::createFromTimestamp(
                                    Storage::disk('public')->lastModified($path)
                                  )->format('d M Y'),
                    'url'      => Storage::disk('public')->url($path),
                ];
            })
            ->sortBy('nama')
            ->values();

        return view('admin.template.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file_template' => 'required|file|mimes:docx,doc|max:10240',
            'nama_file'     => 'required|string|max:100',
        ]);

        $namaFile = \Str::slug($request->nama_file) . '.docx';
        $request->file('file_template')->storeAs(self::FOLDER, $namaFile, 'public');

        return redirect()->route('admin.template.index')
                         ->with('success', "Template '{$namaFile}' berhasil diupload.");
    }

    public function destroy(Request $request)
    {
        $request->validate(['path' => 'required|string']);
        Storage::disk('public')->delete($request->path);

        return redirect()->route('admin.template.index')
                         ->with('success', 'Template berhasil dihapus.');
    }

    private function formatBytes($bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        return round($bytes / 1024, 1) . ' KB';
    }
}