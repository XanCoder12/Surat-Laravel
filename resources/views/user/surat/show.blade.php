@extends('layouts.user')
@section('title', 'Detail Surat')

@section('content')

<div class="d-flex align-items-center gap-2 mb-4">
    <a href="{{ route('user.surat.index') }}" class="btn btn-sm btn-light" style="border-radius:8px;">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <h5 class="fw-bold mb-0" style="color:#1e3a5f;">Detail & Tracking Surat</h5>
        <small class="text-muted">Pantau progress pengajuan surat kamu</small>
    </div>
</div>

<div class="row g-3">

    {{-- KOLOM KIRI --}}
    <div class="col-12 col-lg-8">

        {{-- INFO SURAT --}}
        <div class="card card-custom mb-3">
            <div class="card-body p-4">
                <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
                    <div>
                        <h5 class="fw-bold mb-2" style="color:#111827;">{{ $surat->judul }}</h5>
                        <div class="d-flex gap-2 flex-wrap">
                            <span class="badge rounded-pill" style="background:#ede9fe;color:#6d28d9;font-size:11px;">
                                {{ $surat->jenis_label }}
                            </span>
                            <span class="badge rounded-pill badge-{{ $surat->sifat }}" style="font-size:11px;">
                                {{ ucfirst($surat->sifat) }}
                            </span>
                            @if($surat->status === 'selesai')
                                <span class="badge rounded-pill" style="background:#dcfce7;color:#15803d;font-size:11px;">✓ Selesai</span>
                            @elseif($surat->status === 'ditolak')
                                <span class="badge rounded-pill" style="background:#fee2e2;color:#b91c1c;font-size:11px;">✗ Ditolak</span>
                            @else
                                <span class="badge rounded-pill" style="background:#dbeafe;color:#1d4ed8;font-size:11px;">⏱ Diproses</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="row g-3" style="font-size:13px;">
                    <div class="col-sm-6">
                        <div style="color:#6b7280;font-size:11px;font-weight:600;margin-bottom:3px;">TUJUAN SURAT</div>
                        <div style="color:#111827;">{{ $surat->tujuan }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div style="color:#6b7280;font-size:11px;font-weight:600;margin-bottom:3px;">TANGGAL PENGAJUAN</div>
                        <div>{{ $surat->created_at->Format('d F Y, H:i') }}</div>
                    </div>
                    @if($surat->nomor_surat)
                    <div class="col-sm-6">
                        <div style="color:#6b7280;font-size:11px;font-weight:600;margin-bottom:3px;">NOMOR SURAT</div>
                        <div class="fw-semibold" style="color:#1e3a5f;">{{ $surat->nomor_surat }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div style="color:#6b7280;font-size:11px;font-weight:600;margin-bottom:3px;">TANGGAL SURAT</div>
                        <div>{{ $surat->tanggal_surat?->format('d M Y') ?? '—' }}</div>
                    </div>
                    @endif
                    <div class="col-sm-6">
                        <div style="color:#6b7280;font-size:11px;font-weight:600;margin-bottom:3px;">DEADLINE SLA</div>
                        <div style="color:{{ $surat->sla_status === 'terlambat' ? '#b91c1c' : '#374151' }};font-weight:500;">
                            {{ $surat->deadline_sla?->format('d M Y, H:i') ?? '—' }}
                            @if($surat->sla_status === 'terlambat')
                                <span class="badge bg-danger ms-1" style="font-size:10px;">Terlambat</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Progress overall --}}
                <div class="mt-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:12px;font-weight:600;color:#374151;">Progress Keseluruhan</span>
                        <span style="font-size:12px;font-weight:700;color:#1e3a5f;">{{ $surat->proses_persen }}%</span>
                    </div>
                    <div class="progress" style="height:8px;border-radius:99px;">
                        <div class="progress-bar" role="progressbar"
                             style="width:{{ $surat->proses_persen }}%;background:#1e3a5f;border-radius:99px;">
                        </div>
                    </div>
                    <div style="font-size:11px;color:#6b7280;margin-top:4px;">
                        Tahap {{ $surat->tahap_sekarang }} dari 10 · {{ $surat->nama_tahap }}
                    </div>
                </div>

                {{-- Lampiran --}}
                <div class="mt-4 pt-3 border-top d-flex gap-2 flex-wrap">
                    <a href="{{ Storage::url($surat->file_word) }}" target="_blank"
                       class="btn btn-sm d-flex align-items-center gap-2"
                       style="font-size:12px;border:1px solid #e5e7eb;border-radius:8px;color:#1e3a5f;">
                        <i class="bi bi-file-earmark-word" style="color:#2563eb;"></i> Unduh File Surat
                    </a>
                    @if($surat->file_lampiran)
                        <a href="{{ Storage::url($surat->file_lampiran) }}" target="_blank"
                           class="btn btn-sm d-flex align-items-center gap-2"
                           style="font-size:12px;border:1px solid #e5e7eb;border-radius:8px;color:#1e3a5f;">
                            <i class="bi bi-paperclip"></i> Unduh Lampiran
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- TRACKING TIMELINE --}}
        <div class="card card-custom">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-4" style="color:#1e3a5f;">
                    <i class="bi bi-map me-2"></i>Timeline Proses Surat
                </h6>

                <div class="tracking-steps">
                @foreach($surat->tahapans as $tahapan)
                    <div class="step-item {{ $tahapan->status === 'selesai' ? 'done' : '' }}">
                        <div style="position:relative;">
                            <div class="step-circle {{ $tahapan->status }}" style="
                                width:36px;height:36px;font-size:14px;
                                {{ $tahapan->status === 'proses' ? 'box-shadow:0 0 0 4px #dbeafe;' : '' }}
                            ">
                                @if($tahapan->status === 'selesai')
                                    <i class="bi bi-check-lg"></i>
                                @elseif($tahapan->status === 'proses')
                                    <i class="bi bi-hourglass-split"></i>
                                @elseif($tahapan->status === 'ditolak')
                                    <i class="bi bi-x-lg"></i>
                                @else
                                    {{ $tahapan->tahap }}
                                @endif
                            </div>
                            @if(!$loop->last)
                                <div class="step-line" style="left:17px;"></div>
                            @endif
                        </div>

                        <div class="step-content" style="padding-bottom:{{ $loop->last ? '0' : '24px' }};">
                            <div class="d-flex align-items-start justify-content-between gap-2">
                                <div class="step-title {{ $tahapan->status }}" style="font-size:14px;">
                                    {{ $tahapan->nama_tahap }}
                                    @if($tahapan->status === 'proses')
                                        <span class="badge rounded-pill ms-1" style="background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:500;">Sedang diproses</span>
                                    @endif
                                </div>
                                @if($tahapan->selesai_pada)
                                    <span style="font-size:11px;color:#9ca3af;white-space:nowrap;flex-shrink:0;">
                                        {{ $tahapan->selesai_pada->format('d M Y, H:i') }}
                                    </span>
                                @endif
                            </div>

                            @if($tahapan->diprosesByUser)
                                <div class="step-meta">
                                    <i class="bi bi-person-check me-1"></i>
                                    Diproses oleh <strong>{{ $tahapan->diprosesByUser->name }}</strong>
                                </div>
                            @endif

                            @if($tahapan->catatan)
                                <div class="step-note mt-2" style="
                                    {{ $tahapan->status === 'ditolak' ? 'border-left-color:#ef4444;background:#fef2f2;' : '' }}
                                    {{ $tahapan->status === 'selesai' ? 'border-left-color:#22c55e;background:#f0fdf4;' : '' }}
                                ">
                                    <i class="bi bi-chat-left-text me-1"></i>
                                    <em>{{ $tahapan->catatan }}</em>
                                </div>
                            @endif

                            {{-- Pesan status khusus --}}
                            @if($tahapan->status === 'ditolak' && $loop->index === $surat->tahap_sekarang - 1)
                                <div class="alert alert-danger py-2 px-3 mt-2 mb-0" style="font-size:12px;border-radius:8px;border:none;">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                    Surat ditolak pada tahap ini. Silakan perbaiki dan ajukan ulang.
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
                </div>
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN --}}
    <div class="col-12 col-lg-4">

        {{-- STATUS CARD --}}
        <div class="card card-custom mb-3" style="
            background:{{ $surat->status === 'selesai' ? 'linear-gradient(135deg,#15803d,#22c55e)' : ($surat->status === 'ditolak' ? 'linear-gradient(135deg,#b91c1c,#ef4444)' : 'linear-gradient(135deg,#1e3a5f,#2563eb)') }};
            color:#fff;">
            <div class="card-body p-4 text-center">
                <div style="font-size:42px;margin-bottom:8px;">
                    {{ $surat->status === 'selesai' ? '✅' : ($surat->status === 'ditolak' ? '❌' : '⏳') }}
                </div>
                <div style="font-size:16px;font-weight:700;">
                    @if($surat->status === 'selesai') Surat Selesai
                    @elseif($surat->status === 'ditolak') Surat Ditolak
                    @else Tahap {{ $surat->tahap_sekarang }}/10
                    @endif
                </div>
                <div style="font-size:12px;opacity:0.8;margin-top:4px;">
                    @if($surat->status === 'proses') {{ $surat->nama_tahap }}
                    @elseif($surat->status === 'selesai') Semua tahapan selesai
                    @else Perlu perbaikan
                    @endif
                </div>
            </div>
        </div>

        {{-- RINGKASAN TAHAPAN --}}
        <div class="card card-custom mb-3">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1e3a5f;font-size:13px;">📋 Ringkasan Tahapan</h6>
                @foreach($surat->tahapans as $tahapan)
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div style="
                            width:20px;height:20px;border-radius:50%;flex-shrink:0;
                            display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;
                            background:{{ $tahapan->status === 'selesai' ? '#dcfce7' : ($tahapan->status === 'proses' ? '#dbeafe' : ($tahapan->status === 'ditolak' ? '#fee2e2' : '#f3f4f6')) }};
                            color:{{ $tahapan->status === 'selesai' ? '#15803d' : ($tahapan->status === 'proses' ? '#1d4ed8' : ($tahapan->status === 'ditolak' ? '#b91c1c' : '#9ca3af')) }};
                        ">
                            @if($tahapan->status === 'selesai') ✓
                            @elseif($tahapan->status === 'proses') →
                            @elseif($tahapan->status === 'ditolak') ✗
                            @else {{ $tahapan->tahap }}
                            @endif
                        </div>
                        <div style="font-size:11px;
                            color:{{ $tahapan->status === 'menunggu' ? '#9ca3af' : '#374151' }};
                            font-weight:{{ $tahapan->status === 'proses' ? '600' : '400' }};">
                            {{ $tahapan->nama_tahap }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- SLA INFO --}}
        @if($surat->status === 'proses')
        <div class="card card-custom" style="
            background:{{ $surat->sla_status==='terlambat' ? '#fef2f2' : '#eff6ff' }};
            border:1px solid {{ $surat->sla_status === 'terlambat' ? '#fca5a5' : '#bfdbfe' }} !important;">
            <div class="card-body px-4 py-3">
                <div style="font-size:12px;font-weight:600;
                    color:{{ $surat->sla_status==='terlambat' ? '#b91c1c' : '#1d4ed8' }};margin-bottom:4px;">
                    @if($surat->sla_status==='terlambat')
                        ⚠ SLA Terlampaui!
                    @else
                        ⏱ SLA 1 Hari Kerja
                    @endif
                </div>
                <div style="font-size:12px;color:#374151;">
                    Deadline: <strong>{{ $surat->deadline_sla?->Format('d M Y, H:i') ?? '—' }}</strong>
                </div>
                @if($surat->sla_status !== 'terlambat' && $surat->deadline_sla)
                    <div style="font-size:11px;color:#6b7280;margin-top:2px;">
                        Sisa: <strong>{{ $surat->sisa_jam }}</strong>
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>
</div>

@endsection