@extends('layouts.admin')
@section('title', 'Preview Word')

@section('content')
<div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
        <div>
            <h2 style="font-size:18px; font-weight:700; color:#111827;">
                📄 Preview File Word - {{ $surat->judul }}
            </h2>
            <p style="font-size:13px; color:#6b7280; margin-top:4px;">
                Jika preview tidak muncul, Anda bisa download file-nya langsung.
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.surat.show', $surat) }}" class="btn btn-sm">
                ← Kembali ke Detail
            </a>
            <a href="{{ route('admin.surat.download', [$surat, 'word']) }}" class="btn btn-sm btn-primary">
                ⬇ Download .docx
            </a>
        </div>
    </div>

    {{-- Microsoft Office Online Viewer Embed --}}
    <div style="width:100%; height:calc(100vh - 280px); min-height:600px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
        <iframe 
            src="{{ $viewerUrl }}" 
            width="100%" 
            height="100%" 
            frameborder="0"
            style="border:none;"
            onerror="this.parentElement.innerHTML='<div style=\'padding:40px; text-align:center; color:#6b7280;\'><div style=\'font-size:48px; margin-bottom:16px;\'>📄</div><div style=\'font-size:14px; margin-bottom:16px;\'>Preview tidak dapat dimuat. File memerlukan koneksi internet untuk Microsoft Office Online Viewer.</div><a href=\'{{ route('admin.surat.download', [$surat, 'word']) }}\' class=\'btn btn-primary\'>⬇ Download File</a></div>'">
        </iframe>
    </div>

    {{-- Fallback message --}}
    <div style="margin-top:16px; padding:16px; background:#fef3c7; border-radius:8px; border-left:4px solid #f59e0b;">
        <div style="font-size:13px; color:#92400e;">
            <strong>💡 Catatan:</strong> Preview menggunakan Microsoft Office Online Viewer yang memerlukan:
            <ul style="margin:8px 0 0 20px; font-size:12px;">
                <li>Koneksi internet aktif</li>
                <li>File harus dapat diakses publik (jika server lokal, gunakan tunnel seperti ngrok)</li>
                <li>Browser modern (Chrome, Edge, Firefox)</li>
            </ul>
            Jika preview tidak berhasil, silakan download file untuk melihat isinya.
        </div>
    </div>
</div>
@endsection
