@extends('layouts.admin')
@section('title', 'Detail Surat')

@section('content')

<div style="display:grid; grid-template-columns:1fr 340px; gap:16px; align-items:start;">

    {{-- KOLOM KIRI: INFO SURAT + TRACKING --}}
    <div style="display:flex; flex-direction:column; gap:16px;">

        {{-- INFO UTAMA --}}
        <div class="card">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
                <div>
                    <h2 style="font-size:18px; font-weight:700; color:#111827; margin-bottom:6px;">
                        {{ $surat->judul }}
                    </h2>
                    <div style="display:flex; gap:8px; flex-wrap:wrap;">
                        <span class="badge badge-purple">{{ $surat->jenis_label }}</span>
                        @if($surat->sifat === 'segera')
                            <span class="badge badge-red">Segera</span>
                        @elseif($surat->sifat === 'rahasia')
                            <span class="badge badge-amber">Rahasia</span>
                        @else
                            <span class="badge badge-gray">Biasa</span>
                        @endif
                        @if($surat->status === 'selesai')
                            <span class="badge badge-green">✓ Selesai</span>
                        @elseif($surat->status === 'ditolak')
                            <span class="badge badge-red">✗ Ditolak</span>
                        @else
                            <span class="badge badge-amber">● Proses</span>
                        @endif
                    </div>
                </div>
                <a href="{{ route('admin.surat.index') }}" class="btn btn-sm">← Kembali</a>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:13px;">
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Pengusul</div>
                    <div style="font-weight:500;">{{ $surat->user?->name ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Tujuan Surat</div>
                    <div>{{ $surat->tujuan }}</div>
                </div>
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Tanggal Pengajuan</div>
                    <div>{{ $surat->created_at?->format('d M Y, H:i') ?? '—' }}</div>
                </div>
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Nomor Surat</div>
                    <div>{{ $surat->nomor_surat ?? '— (belum dinomori)' }}</div>
                </div>
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Deadline SLA</div>
                    <div @style([
                        'color' => $surat->sla_status === 'terlambat' ? '#b91c1c' : '#374151',
                        'font-weight' => '500',
                    ])>
                        {{ $surat->deadline_sla ? $surat->deadline_sla->format('d M Y, H:i') : '—' }}
                        @if($surat->sla_status === 'terlambat') ⚠ Terlambat @endif
                    </div>
                </div>
                <div>
                    <div style="color:#6b7280; font-size:11px; margin-bottom:2px;">Progress</div>
                    <div style="display:flex; align-items:center; gap:8px;">
                        <div class="progress-bar" style="flex:1;">
                            <div
                                class="progress-fill"
                                @style(['width' => min(100, max(0, (int) $surat->proses_persen)).'%'])
                            ></div>
                        </div>
                        <span style="font-size:12px; font-weight:600; color:#1d4ed8;">{{ $surat->proses_persen }}%</span>
                    </div>
                </div>
            </div>

            {{-- FILE --}}
            <div style="margin-top:16px; padding-top:16px; border-top:1px solid #f3f4f6;">
                <div style="font-size:12px; color:#6b7280; margin-bottom:8px; font-weight:600;">LAMPIRAN</div>
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <a href="{{ Storage::url($surat->file_word) }}" target="_blank"
                       class="btn btn-sm">
                        📄 Unduh File Surat (.docx)
                    </a>
                    @if($surat->file_lampiran)
                        <a href="{{ Storage::url($surat->file_lampiran) }}" target="_blank"
                           class="btn btn-sm">
                            📎 Unduh Lampiran
                        </a>
                    @endif
                </div>
            </div>
        </div>

        {{-- TRACKING TAHAPAN --}}
        <div class="card">
            <h2 style="font-size:15px; font-weight:600; margin-bottom:16px;">📍 Riwayat Tahapan</h2>
            <div style="position:relative;">
                @foreach($surat->tahapans as $tahapan)
                    @php
                        $tahapanTitleColor = match ($tahapan->status) {
                            'proses' => '#1d4ed8',
                            'menunggu' => '#9ca3af',
                            default => '#111827',
                        };
                        $tahapanCircleColors = match ($tahapan->status) {
                            'selesai' => ['background' => '#dcfce7', 'color' => '#15803d'],
                            'proses' => ['background' => '#dbeafe', 'color' => '#1d4ed8'],
                            'ditolak' => ['background' => '#fee2e2', 'color' => '#b91c1c'],
                            default => ['background' => '#f3f4f6', 'color' => '#9ca3af'],
                        };
                    @endphp
                    <div style="display:flex; gap:14px; margin-bottom:0;">
                        {{-- Garis vertikal --}}
                        <div style="display:flex; flex-direction:column; align-items:center; width:28px; flex-shrink:0;">
                            {{-- Lingkaran status --}}
                            <div @style(array_merge([
                                'width' => '28px',
                                'height' => '28px',
                                'border-radius' => '50%',
                                'display' => 'flex',
                                'align-items' => 'center',
                                'justify-content' => 'center',
                                'font-size' => '12px',
                                'font-weight' => '700',
                                'flex-shrink' => '0',
                            ], $tahapanCircleColors))>
                                @if($tahapan->status === 'selesai') ✓
                                @elseif($tahapan->status === 'proses') →
                                @elseif($tahapan->status === 'ditolak') ✗
                                @else {{ $tahapan->tahap }}
                                @endif
                            </div>
                            {{-- Garis --}}
                            @if(!$loop->last)
                                <div @style([
                                    'width' => '2px',
                                    'flex' => '1',
                                    'min-height' => '24px',
                                    'background' => $tahapan->status === 'selesai' ? '#86efac' : '#e5e7eb',
                                    'margin' => '4px 0',
                                ])></div>
                            @endif
                        </div>

                        {{-- Konten tahapan --}}
                        <div @style(['padding-bottom' => $loop->last ? '0' : '20px', 'flex' => '1'])>
                            <div style="display:flex; align-items:center; justify-content:space-between;">
                                <div @style(['font-size' => '13px', 'font-weight' => '600', 'color' => $tahapanTitleColor])>
                                    {{ $tahapan->nama_tahap }}
                                </div>
                                @if($tahapan->selesai_pada)
                                    <div style="font-size:11px; color:#9ca3af;">
                                        {{ $tahapan->selesai_pada->format('d M Y, H:i') }}
                                    </div>
                                @endif
                            </div>
                            @if($tahapan->diprosesByUser)
                                <div style="font-size:11px; color:#6b7280; margin-top:2px;">
                                    oleh {{ $tahapan->diprosesByUser->name }}
                                </div>
                            @endif
                            @if($tahapan->catatan)
                                <div style="font-size:12px; color:#374151; margin-top:4px;
                                            background:#f9fafb; padding:6px 10px; border-radius:6px;
                                            border-left:3px solid #e5e7eb;">
                                    {{ $tahapan->catatan }}
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- KOLOM KANAN: AKSI --}}
    <div style="display:flex; flex-direction:column; gap:12px;">

        @if($surat->status === 'proses')

            {{-- SETUJUI --}}
            <div class="card">
                <h2 style="font-size:14px; font-weight:600; margin-bottom:12px; color:#15803d;">
                    ✅ Setujui & Teruskan
                </h2>
                <form action="{{ route('admin.surat.setujui', $surat) }}" method="POST">
                    @csrf

                    {{-- Input nomor surat hanya muncul di tahap 5 --}}
                    @if($surat->tahap_sekarang === 5)
                        <div style="margin-bottom:10px;">
                            <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">
                                Nomor Surat <span style="color:#b91c1c;">*</span>
                            </label>
                            <input type="text" name="nomor_surat" required
                                   placeholder="Contoh: 024/KU.01/IV/2025"
                                   style="width:100%; padding:7px 10px; border:1px solid #e5e7eb;
                                          border-radius:7px; font-size:13px; box-sizing:border-box;">
                        </div>
                    @endif

                    <div style="margin-bottom:10px;">
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">
                            Catatan (opsional)
                        </label>
                        <textarea name="catatan" rows="3" placeholder="Tambahkan catatan..."
                                  style="width:100%; padding:7px 10px; border:1px solid #e5e7eb;
                                         border-radius:7px; font-size:13px; resize:vertical; box-sizing:border-box;">
                        </textarea>
                    </div>

                    <div style="font-size:12px; color:#6b7280; margin-bottom:10px; padding:8px 10px;
                                background:#f0fdf4; border-radius:6px; border:1px solid #bbf7d0;">
                        Tahap berikutnya:
                        <strong style="color:#15803d;">
                            {{ \App\Models\Surat::NAMA_TAHAP[$surat->tahap_sekarang + 1] ?? 'Selesai' }}
                        </strong>
                    </div>

                    <button type="submit" class="btn btn-success" style="width:100%;">
                        ✓ Setujui & Teruskan
                    </button>
                </form>
            </div>

            {{-- TOLAK --}}
            <div class="card">
                <h2 style="font-size:14px; font-weight:600; margin-bottom:12px; color:#b91c1c;">
                    ✗ Tolak Surat
                </h2>
                <form action="{{ route('admin.surat.tolak', $surat) }}" method="POST">
                    @csrf
                    <div style="margin-bottom:10px;">
                        <label style="font-size:12px; color:#6b7280; display:block; margin-bottom:4px;">
                            Alasan Penolakan <span style="color:#b91c1c;">*</span>
                        </label>
                        <textarea name="catatan" rows="3" required
                                  placeholder="Tuliskan alasan penolakan..."
                                  style="width:100%; padding:7px 10px; border:1px solid #e5e7eb;
                                         border-radius:7px; font-size:13px; resize:vertical; box-sizing:border-box;">
                        </textarea>
                        @error('catatan')
                            <div style="color:#b91c1c; font-size:12px; margin-top:4px;">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-danger" style="width:100%;"
                            onclick="return confirm('Yakin ingin menolak surat ini?')">
                        ✗ Tolak Surat
                    </button>
                </form>
            </div>

        @elseif($surat->status === 'selesai')
            <div class="card" style="text-align:center; padding:24px;">
                <div style="font-size:32px; margin-bottom:8px;">✅</div>
                <div style="font-size:14px; font-weight:600; color:#15803d;">Surat Selesai</div>
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Semua tahapan telah selesai</div>
            </div>
        @else
            <div class="card" style="text-align:center; padding:24px;">
                <div style="font-size:32px; margin-bottom:8px;">❌</div>
                <div style="font-size:14px; font-weight:600; color:#b91c1c;">Surat Ditolak</div>
                <div style="font-size:12px; color:#6b7280; margin-top:4px;">Pengusul perlu merevisi dan mengajukan ulang</div>
            </div>
        @endif

        {{-- INFO TAHAP SEKARANG --}}
        <div class="card" style="background:#f8fafc;">
            <div style="font-size:11px; color:#6b7280; margin-bottom:6px; font-weight:600;">POSISI SEKARANG</div>
            <div style="font-size:22px; font-weight:700; color:#1e3a5f;">
                Tahap {{ $surat->tahap_sekarang }}/10
            </div>
            <div style="font-size:13px; color:#374151; margin-top:2px;">{{ $surat->nama_tahap }}</div>
        </div>

    </div>
</div>

@endsection