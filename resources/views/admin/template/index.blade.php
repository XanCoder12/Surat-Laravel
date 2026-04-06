@extends('layouts.admin')
@section('title', 'Kelola Template Surat')

@section('content')

<div style="display:grid; grid-template-columns:1fr 320px; gap:16px; align-items:start;">

    {{-- DAFTAR TEMPLATE --}}
    <div class="card">
        <div class="section-header">
            <div>
                <h2>📄 Template Surat</h2>
                <small>File .docx contoh untuk pegawai</small>
            </div>
        </div>

        @if($files->isEmpty())
            <div style="text-align:center; padding:40px; color:#9ca3af; font-size:13px;">
                📭 Belum ada template. Upload sekarang →
            </div>
        @else
            @foreach($files as $file)
                <div style="display:flex; align-items:center; gap:12px; padding:12px 0; border-bottom:1px solid #f3f4f6;">
                    <div style="font-size:28px;">📄</div>
                    <div style="flex:1; min-width:0;">
                        <div style="font-size:13px; font-weight:600; color:#111827;">{{ $file['nama'] }}</div>
                        <div style="font-size:11px; color:#9ca3af; margin-top:2px;">
                            {{ $file['ukuran'] }} · Diupload {{ $file['diupload'] }}
                        </div>
                    </div>
                    <div style="display:flex; gap:6px; flex-shrink:0;">
                        <a href="{{ $file['url'] }}" target="_blank" class="btn btn-sm">⬇ Unduh</a>
                        <form action="{{ route('admin.template.destroy') }}" method="POST"
                              onsubmit="return confirm('Hapus template {{ $file['nama'] }}?')">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="path" value="{{ $file['path'] }}">
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- FORM UPLOAD --}}
    <div class="card">
        <h2 style="font-size:14px; font-weight:600; margin-bottom:14px;">⬆ Upload Template Baru</h2>
        <form action="{{ route('admin.template.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div style="margin-bottom:10px;">
                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">
                    Nama Template <span style="color:#b91c1c;">*</span>
                </label>
                <input type="text" name="nama_file" required
                       placeholder="Contoh: Nota Dinas"
                       value="{{ old('nama_file') }}"
                       style="width:100%; padding:7px 10px; border:1px solid #e5e7eb;
                              border-radius:7px; font-size:13px; box-sizing:border-box;">
                @error('nama_file')
                    <div style="color:#b91c1c; font-size:11px; margin-top:3px;">{{ $message }}</div>
                @enderror
            </div>

            <div style="margin-bottom:14px;">
                <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">
                    File (.docx) <span style="color:#b91c1c;">*</span>
                </label>
                <input type="file" name="file_template" required accept=".docx,.doc"
                       style="width:100%; font-size:13px;">
                @error('file_template')
                    <div style="color:#b91c1c; font-size:11px; margin-top:3px;">{{ $message }}</div>
                @enderror
                <div style="font-size:11px; color:#9ca3af; margin-top:4px;">Format: .docx · Maks. 10MB</div>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%;">
                ⬆ Upload Template
            </button>
        </form>

        @if(session('success'))
            <div style="margin-top:12px; padding:8px 12px; background:#dcfce7;
                        border-radius:7px; font-size:12px; color:#15803d;">
                ✅ {{ session('success') }}
            </div>
        @endif
    </div>

</div>

@endsection