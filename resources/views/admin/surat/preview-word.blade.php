@extends('layouts.admin')
@section('title', 'Preview Dokumen')

@section('content')
<div class="card">
    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
        <div>
            <h2 style="font-size:18px; font-weight:700; color:#111827;">
                📄 Preview Dokumen - {{ $surat->judul }}
            </h2>
            <p style="font-size:13px; color:#6b7280; margin-top:4px;">
                File: <strong>{{ $fileName ?? '—' }}</strong>
            </p>
        </div>
        <div style="display:flex; gap:8px;">
            <a href="{{ route('admin.surat.show', $surat) }}" class="btn btn-sm">
                ← Kembali ke Detail
            </a>
            @if($tipe === 'word')
                <a href="{{ route('admin.surat.download', [$surat, 'word']) }}" class="btn btn-sm btn-primary">
                    ⬇ Download .docx
                </a>
            @else
                <a href="{{ route('admin.surat.download', [$surat, 'lampiran']) }}" class="btn btn-sm btn-primary">
                    ⬇ Download Lampiran
                </a>
            @endif
        </div>
    </div>

    {{-- Word Document HTML Preview --}}
    @if(isset($htmlContent))
        <div style="width:100; background:#fff; border:1px solid #e5e7eb; border-radius:8px; padding:32px; overflow:auto; max-height:calc(100vh - 280px); min-height:400px;">
            <div style="max-width:800px; margin:0 auto; font-family: 'Times New Roman', serif; font-size:14pt; line-height:1.6; color:#000;">
                {!! $htmlContent !!}
            </div>
        </div>

    {{-- PDF Preview --}}
    @elseif(isset($pdfUrl))
        <div style="width:100%; height:calc(100vh - 280px); min-height:600px; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden;">
            <iframe src="{{ $pdfUrl }}" width="100%" height="100%" frameborder="0" style="border:none;"></iframe>
        </div>

    {{-- Image Preview --}}
    @elseif(isset($imageUrl))
        <div style="width:100%; text-align:center; background:#f9fafb; border:1px solid #e5e7eb; border-radius:8px; padding:20px;">
            <img src="{{ $imageUrl }}" alt="Preview" style="max-width:100%; max-height:calc(100vh - 320px); border-radius:4px; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        </div>

    {{-- Fallback --}}
    @else
        <div style="padding:40px; text-align:center; color:#6b7280;">
            <div style="font-size:48px; margin-bottom:16px;">📄</div>
            <div style="font-size:14px; margin-bottom:16px;">Preview tidak tersedia untuk format ini.</div>
            @if($tipe === 'word')
                <a href="{{ route('admin.surat.download', [$surat, 'word']) }}" class="btn btn-primary">⬇ Download File</a>
            @else
                <a href="{{ route('admin.surat.download', [$surat, 'lampiran']) }}" class="btn btn-primary">⬇ Download Lampiran</a>
            @endif
        </div>
    @endif
</div>
@endsection
