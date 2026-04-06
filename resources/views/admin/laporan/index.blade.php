@extends('layouts.admin')
@section('title', 'Laporan Rekap Bulanan')

@section('content')

{{-- FILTER --}}
<div class="card" style="margin-bottom:16px;">
    <form method="GET" action="{{ route('admin.laporan.index') }}"
          style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">

        <div>
            <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px;">Bulan</label>
            <select name="bulan" style="padding:7px 10px; border:1px solid #e5e7eb; border-radius:7px; font-size:13px;">
                @foreach(range(1, 12) as $m)
                    <option value="{{ $m }}" {{ $bulan == $m ? 'selected' : '' }}>
                        {{ \Carbon\Carbon::create(now()->year, $m, 1)->translatedFormat('F') }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px;">Tahun</label>
            <select name="tahun" style="padding:7px 15px; border:1px solid #e5e7eb; border-radius:7px; font-size:13px;">
                @foreach(range(now()->year, now()->year - 3) as $y)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>{{ $y }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label style="font-size:11px; color:#6b7280; display:block; margin-bottom:4px;">Jenis Surat</label>
            <select name="jenis" style="padding:7px 10px; border:1px solid #e5e7eb; border-radius:7px; font-size:13px;">
                <option value="">Semua Jenis</option>
                @foreach(\App\Models\Surat::JENIS_LABEL as $val => $label)
                    <option value="{{ $val }}" {{ $jenis === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div style="display:flex; gap:6px;">
            <button type="submit" class="btn btn-primary">🔍 Tampilkan</button>
            <a href="{{ route('admin.laporan.export', request()->query()) }}"
               class="btn" style="color:#15803d; border-color:#86efac;">
                ⬇ Export CSV
            </a>
        </div>
    </form>
</div>

{{-- RINGKASAN STAT --}}
<div class="stat-grid" style="margin-bottom:16px;">
    <div class="stat-card blue">
        <div class="stat-label">Total Surat</div>
        <div class="stat-value">{{ $ringkasan['total'] }}</div>
    </div>
    <div class="stat-card green">
        <div class="stat-label">Selesai</div>
        <div class="stat-value">{{ $ringkasan['selesai'] }}</div>
    </div>
    <div class="stat-card amber">
        <div class="stat-label">Masih Proses</div>
        <div class="stat-value">{{ $ringkasan['proses'] }}</div>
    </div>
    <div class="stat-card red">
        <div class="stat-label">Terlambat SLA</div>
        <div class="stat-value">{{ $ringkasan['sla_telat'] }}</div>
    </div>
</div>

{{-- TABEL REKAP --}}
<div class="card">
    <div class="section-header">
        <div>
            <h2>📋 Rekap Surat —
                {{ \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->translatedFormat('F Y') }}
            </h2>
            <small>{{ $ringkasan['total'] }} surat ditemukan</small>
        </div>
    </div>

    @if($surats->isEmpty())
        <div style="text-align:center; padding:40px; color:#9ca3af; font-size:13px;">
            📭 Tidak ada data surat pada periode ini
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Jenis Surat</th>
                        <th>Nama Pengusul</th>
                        <th>Judul Surat</th>
                        <th>Tujuan</th>
                        <th>Nomor Surat</th>
                        <th>Tgl Surat</th>
                        <th>Progress</th>
                        <th>Status</th>
                        <th>SLA</th>
                    </tr>
                </thead>
                <tbody>
                @foreach($surats as $i => $surat)
                    <tr>
                        <td style="color:#9ca3af; font-size:12px;">{{ $i + 1 }}</td>
                        <td><span class="badge badge-purple">{{ $surat->jenis_label }}</span></td>
                        <td style="font-size:13px; white-space:nowrap;">{{ $surat->user?->name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('admin.surat.show', $surat) }}"
                               style="font-size:13px; color:#1d4ed8; text-decoration:none; font-weight:500;">
                                {{ \Illuminate\Support\Str::limit($surat->judul, 35) }}
                            </a>
                        </td>
                        <td style="font-size:12px; color:#6b7280; max-width:150px;">
                            {{ \Illuminate\Support\Str::limit($surat->tujuan, 30) }}
                        </td>
                        <td style="font-size:12px; white-space:nowrap;">
                            {{ $surat->nomor_surat ?? '—' }}
                        </td>
                        <td style="font-size:12px; white-space:nowrap;">
                            {{ $surat->tanggal_surat ? $surat->tanggal_surat->format('d/m/Y') : '—' }}
                        </td>
                        <td>
                            <div style="font-size:11px; font-weight:500; color:#1d4ed8; white-space:nowrap;">
                                Tahap {{ $surat->tahap_sekarang }}/10
                            </div>
                            <div style="font-size:10px; color:#6b7280;">{{ $surat->nama_tahap }}</div>
                            <div class="progress-bar" style="margin-top:3px; width:80px;">
                                <div
                                    class="progress-fill"
                                    @style(['width' => min(100, max(0, (int) $surat->proses_persen)).'%'])
                                ></div>
                            </div>
                        </td>
                        <td>
                            @if($surat->status === 'selesai')
                                <span class="badge badge-green">Selesai</span>
                            @elseif($surat->status === 'ditolak')
                                <span class="badge badge-red">Ditolak</span>
                            @else
                                <span class="badge badge-amber">Proses</span>
                            @endif
                        </td>
                        <td>
                            @if($surat->status === 'selesai')
                                <span class="badge badge-green">✓ OK</span>
                            @elseif($surat->sla_status === 'terlambat')
                                <span class="badge badge-red">Terlambat</span>
                            @else
                                <span class="badge badge-blue">OK</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

@endsection