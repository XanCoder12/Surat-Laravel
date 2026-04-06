@extends('layouts.admin')
@section('title', 'Statistik & Grafik')

@section('content')

{{-- FILTER TAHUN --}}
<div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
        <h5 class="fw-bold mb-0" style="color:#111827;">📊 Statistik & Grafik</h5>
        <small class="text-muted">Data real-time dari database</small>
    </div>
    <div class="d-flex align-items-center gap-2">
        <label style="font-size:13px;color:#6b7280;">Tahun:</label>
        <select id="filter-tahun" class="form-select form-select-sm" style="width:100px;font-size:13px;border-radius:7px;">
            @foreach(range(now()->year, now()->year - 3) as $y)
                <option value="{{ $y }}" {{ $y == now()->year ? 'selected' : '' }}>{{ $y }}</option>
            @endforeach
        </select>
        <button onclick="loadCharts()" class="btn btn-sm btn-primary"
                style="background:#1e3a5f;border-color:#1e3a5f;border-radius:7px;font-size:12px;">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
</div>

{{-- DEBUG: tampil jika ada error fetch (akan hilang sendiri kalau sukses) --}}
<div id="debug-alert" class="alert alert-warning d-none" style="font-size:12px;"></div>

{{-- ROW 1: Stat cards mini --}}
<div class="row g-3 mb-3" id="stat-cards">
    <div class="col-6 col-md-3">
        <div style="background:#1e3a5f;color:#fff;padding:16px 20px;border-radius:10px;">
            <div style="font-size:11px;opacity:.7;margin-bottom:4px;">Total Surat</div>
            <div style="font-size:26px;font-weight:700;" id="sc-total">—</div>
            <div style="font-size:11px;opacity:.6;">Tahun ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#15803d;color:#fff;padding:16px 20px;border-radius:10px;">
            <div style="font-size:11px;opacity:.7;margin-bottom:4px;">Selesai</div>
            <div style="font-size:26px;font-weight:700;" id="sc-selesai">—</div>
            <div style="font-size:11px;opacity:.6;">Tahun ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#b45309;color:#fff;padding:16px 20px;border-radius:10px;">
            <div style="font-size:11px;opacity:.7;margin-bottom:4px;">Proses</div>
            <div style="font-size:26px;font-weight:700;" id="sc-proses">—</div>
            <div style="font-size:11px;opacity:.6;">Saat ini</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div style="background:#b91c1c;color:#fff;padding:16px 20px;border-radius:10px;">
            <div style="font-size:11px;opacity:.7;margin-bottom:4px;">Ditolak</div>
            <div style="font-size:26px;font-weight:700;" id="sc-ditolak">—</div>
            <div style="font-size:11px;opacity:.6;">Tahun ini</div>
        </div>
    </div>
</div>

{{-- ROW 2: Surat per bulan (bar) + Status bulan ini (doughnut) --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-8">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h6 class="fw-bold mb-0" style="color:#1e3a5f;">📈 Surat Per Bulan</h6>
                        <small class="text-muted">Total, selesai, proses, ditolak</small>
                    </div>
                    <div class="d-flex gap-1">
                        <button onclick="toggleBulanChart('bar')"  id="btn-bar"  class="btn btn-sm chart-toggle active-toggle" style="font-size:11px;border-radius:6px;padding:3px 10px;">Bar</button>
                        <button onclick="toggleBulanChart('line')" id="btn-line" class="btn btn-sm chart-toggle"               style="font-size:11px;border-radius:6px;padding:3px 10px;">Line</button>
                    </div>
                </div>
                <div style="height:260px;">
                    <canvas id="chart-bulanan"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-4">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">🍩 Status Bulan Ini</h6>
                <small class="text-muted">Distribusi status surat</small>
                <div style="height:220px;margin-top:12px;">
                    <canvas id="chart-status"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 3: Per jenis (doughnut) + SLA (stacked bar) --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-5">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">📄 Jenis Surat</h6>
                <small class="text-muted">Distribusi per jenis surat</small>
                <div style="height:240px;margin-top:12px;">
                    <canvas id="chart-jenis"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-7">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">⏱ Pemenuhan SLA Per Bulan</h6>
                <small class="text-muted">Selesai tepat waktu vs ditolak/terlambat</small>
                <div style="height:240px;margin-top:12px;">
                    <canvas id="chart-sla"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 4: Trend harian + Tahap aktif --}}
<div class="row g-3 mb-3">
    <div class="col-12 col-lg-7">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">📅 Trend Surat 30 Hari Terakhir</h6>
                <small class="text-muted">Jumlah pengajuan per hari</small>
                <div style="height:220px;margin-top:12px;">
                    <canvas id="chart-trend"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-5">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">🔢 Surat Aktif Per Tahap</h6>
                <small class="text-muted">Distribusi tahap surat sedang proses</small>
                <div style="height:220px;margin-top:12px;">
                    <canvas id="chart-tahap"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ROW 5: Top pengusul + Ringkasan --}}
<div class="row g-3">
    <div class="col-12 col-lg-6">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-1" style="color:#1e3a5f;">🏆 Top Pengusul Surat Bulan Ini</h6>
                <small class="text-muted">5 pegawai dengan pengajuan terbanyak</small>
                <div style="height:220px;margin-top:12px;">
                    <canvas id="chart-pengusul"></canvas>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-lg-6">
        <div class="card" style="border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div class="card-body p-4">
                <h6 class="fw-bold mb-3" style="color:#1e3a5f;">📋 Ringkasan Per Jenis</h6>
                <table class="table table-sm" style="font-size:12px;">
                    <thead>
                        <tr style="color:#6b7280;">
                            <th style="border:none;font-size:11px;">Jenis</th>
                            <th style="border:none;font-size:11px;text-align:right;">Total</th>
                        </tr>
                    </thead>
                    <tbody id="tbl-ringkasan">
                        <tr><td colspan="2" class="text-muted text-center py-3">Memuat...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js via CDN --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>

<script>
// ── Konstanta warna ───────────────────────────────────────────────────────────
const C = {
    blue:   '#1e3a5f',
    blueL:  'rgba(30,58,95,0.15)',
    green:  '#15803d',
    greenL: 'rgba(21,128,61,0.15)',
    amber:  '#b45309',
    amberL: 'rgba(180,83,9,0.15)',
    red:    '#b91c1c',
    redL:   'rgba(185,28,28,0.15)',
    purple: '#6d28d9',
    teal:   '#0f766e',
    pink:   '#be185d',
};

// ── Registry chart ─────────────────────────────────────────────────────────────
const charts = {};
function destroyChart(id) {
    if (charts[id]) { charts[id].destroy(); delete charts[id]; }
}

// ── Load semua data sekaligus ─────────────────────────────────────────────────
function loadCharts() {
    const tahun  = document.getElementById('filter-tahun').value;
    const url    = "{{ route('admin.chart.data') }}?tahun=" + tahun;
    const debug  = document.getElementById('debug-alert');

    debug.classList.add('d-none');
    debug.textContent = '';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status + ' — ' + url);
            return r.json();
        })
        .then(d => {
            updateStatCards(d);
            buildBulanChart(d.suratPerBulan);
            buildStatusChart(d.suratPerStatus);
            buildJenisChart(d.suratPerJenis);
            buildSlaChart(d.slaChart);
            buildTrendChart(d.trendHarian);
            buildTahapChart(d.suratPerTahap);
            buildPengusulChart(d.topPengusul);
            buildTableRingkasan(d.suratPerJenis);
        })
        .catch(err => {
            console.error('Chart load error:', err);
            debug.textContent = '⚠️ Gagal load data chart: ' + err.message
                + '. Cek Network tab di DevTools untuk detail.';
            debug.classList.remove('d-none');
        });
}

// ── Stat cards ────────────────────────────────────────────────────────────────
function updateStatCards(d) {
    const bln  = d.suratPerBulan;
    const tot  = bln.total.reduce((a,b) => a+b, 0);
    const sel  = bln.selesai.reduce((a,b) => a+b, 0);
    const tol  = bln.ditolak.reduce((a,b) => a+b, 0);
    document.getElementById('sc-total').textContent   = tot;
    document.getElementById('sc-selesai').textContent = sel;
    document.getElementById('sc-proses').textContent  = d.suratPerStatus.proses;
    document.getElementById('sc-ditolak').textContent = tol;
}

// ── Chart: Surat per bulan (bar / line toggle) ────────────────────────────────
let bulanType = 'bar';
let bulanData = null;

function buildBulanChart(data) {
    bulanData = data;
    destroyChart('bulanan');
    const ctx    = document.getElementById('chart-bulanan').getContext('2d');
    const isLine = bulanType === 'line';

    const ds = (label, values, border, bgBar, bgLine) => ({
        label,
        data: values,
        borderColor: border,
        backgroundColor: isLine ? bgLine : bgBar,
        borderWidth: 2,
        fill: isLine,
        tension: isLine ? 0.4 : 0,
        pointRadius: isLine ? 3 : 0,
        pointHoverRadius: 5,
        ...(isLine ? {} : { borderRadius: 4 }),
    });

    charts['bulanan'] = new Chart(ctx, {
        type: bulanType,
        data: {
            labels: data.labels,
            datasets: [
                ds('Total',   data.total,   C.blue,  C.blueL,  'rgba(30,58,95,0.12)'),
                ds('Selesai', data.selesai, C.green, C.greenL, 'rgba(21,128,61,0.12)'),
                ds('Proses',  data.proses,  C.amber, C.amberL, 'rgba(180,83,9,0.12)'),
                ds('Ditolak', data.ditolak, C.red,   C.redL,   'rgba(185,28,28,0.12)'),
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: '#f3f4f6' } }
            }
        }
    });
}

function toggleBulanChart(type) {
    bulanType = type;
    document.querySelectorAll('.chart-toggle').forEach(b => b.classList.remove('active-toggle'));
    document.getElementById('btn-' + type).classList.add('active-toggle');
    if (bulanData) buildBulanChart(bulanData);
}

// ── Chart: Status bulan ini (doughnut) ───────────────────────────────────────
function buildStatusChart(data) {
    destroyChart('status');
    const ctx = document.getElementById('chart-status').getContext('2d');
    const total = data.proses + data.selesai + data.ditolak;

    // Kalau semua 0, tampilkan placeholder
    const values = total > 0
        ? [data.proses, data.selesai, data.ditolak]
        : [1, 0, 0];

    charts['status'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Proses', 'Selesai', 'Ditolak'],
            datasets: [{
                data: values,
                backgroundColor: total > 0
                    ? [C.amber, C.green, C.red]
                    : ['#e5e7eb', '#e5e7eb', '#e5e7eb'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '68%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 16 } },
                tooltip: {
                    callbacks: {
                        label: ctx => total > 0
                            ? ` ${ctx.label}: ${ctx.raw}`
                            : ' Belum ada data bulan ini'
                    }
                }
            }
        }
    });
}

// ── Chart: Per jenis (doughnut) ───────────────────────────────────────────────
function buildJenisChart(data) {
    destroyChart('jenis');
    const ctx     = document.getElementById('chart-jenis').getContext('2d');
    const palette = [C.blue, C.green, C.amber, C.purple, C.teal, C.pink];
    const hasData = data.labels.length > 0;

    charts['jenis'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: hasData ? data.labels : ['Belum ada data'],
            datasets: [{
                data: hasData ? data.data : [1],
                backgroundColor: hasData ? palette.slice(0, data.labels.length) : ['#e5e7eb'],
                borderWidth: 0,
                hoverOffset: 6
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12, padding: 12 } }
            }
        }
    });
}

// ── Chart: SLA stacked bar ────────────────────────────────────────────────────
function buildSlaChart(data) {
    destroyChart('sla');
    const ctx = document.getElementById('chart-sla').getContext('2d');
    charts['sla'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                { label: 'Tepat Waktu / Selesai', data: data.tepat,     backgroundColor: C.green, borderRadius: 4, stack: 'sla' },
                { label: 'Terlambat / Ditolak',   data: data.terlambat, backgroundColor: C.red,   borderRadius: 4, stack: 'sla' },
            ]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'top', labels: { font: { size: 11 }, boxWidth: 12 } } },
            scales: {
                x: { stacked: true, grid: { display: false }, ticks: { font: { size: 11 } } },
                y: { stacked: true, beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: '#f3f4f6' } }
            }
        }
    });
}

// ── Chart: Trend harian (line area) ──────────────────────────────────────────
function buildTrendChart(data) {
    destroyChart('trend');
    const ctx = document.getElementById('chart-trend').getContext('2d');
    charts['trend'] = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Pengajuan',
                data: data.data,
                borderColor: C.blue,
                backgroundColor: C.blueL,
                fill: true,
                tension: 0.4,
                pointRadius: 2,
                pointHoverRadius: 5,
                borderWidth: 2
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxTicksLimit: 10 } },
                y: { beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: '#f3f4f6' } }
            }
        }
    });
}

// ── Chart: Surat aktif per tahap (horizontal bar) ─────────────────────────────
function buildTahapChart(data) {
    destroyChart('tahap');
    const ctx = document.getElementById('chart-tahap').getContext('2d');
    const shortLabels = (data.labels || []).map(l => l.length > 22 ? l.substring(0, 20) + '…' : l);
    const hasData = shortLabels.length > 0;

    charts['tahap'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hasData ? shortLabels : ['Tidak ada surat proses'],
            datasets: [{
                label: 'Surat',
                data: hasData ? data.data : [0],
                backgroundColor: C.blue,
                borderRadius: 4,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { font: { size: 10 }, precision: 0 }, grid: { color: '#f3f4f6' } },
                y: { ticks: { font: { size: 10 } }, grid: { display: false } }
            }
        }
    });
}

// ── Chart: Top pengusul (horizontal bar) ──────────────────────────────────────
function buildPengusulChart(data) {
    destroyChart('pengusul');
    const ctx     = document.getElementById('chart-pengusul').getContext('2d');
    const palette = [C.blue, C.green, C.amber, C.purple, C.teal];
    const hasData = data.labels.length > 0;

    charts['pengusul'] = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: hasData ? data.labels : ['Belum ada data'],
            datasets: [{
                label: 'Surat Diajukan',
                data: hasData ? data.data : [0],
                backgroundColor: hasData ? palette.slice(0, data.labels.length) : ['#e5e7eb'],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                x: { beginAtZero: true, ticks: { font: { size: 11 }, precision: 0 }, grid: { color: '#f3f4f6' } },
                y: { ticks: { font: { size: 11 } }, grid: { display: false } }
            }
        }
    });
}

// ── Tabel ringkasan per jenis ──────────────────────────────────────────────────
function buildTableRingkasan(data) {
    const tbody = document.getElementById('tbl-ringkasan');
    if (!data.labels || !data.labels.length) {
        tbody.innerHTML = '<tr><td colspan="2" class="text-muted text-center py-3">Belum ada data tahun ini</td></tr>';
        return;
    }
    tbody.innerHTML = data.labels.map((label, i) => `
        <tr>
            <td>${label}</td>
            <td style="text-align:right"><strong>${data.data[i]}</strong></td>
        </tr>
    `).join('');
}

// ── Style toggle button ────────────────────────────────────────────────────────
const style = document.createElement('style');
style.textContent = `
    .chart-toggle { border:1px solid #e5e7eb; background:transparent; color:#6b7280; }
    .chart-toggle:hover { background:#f9fafb; }
    .active-toggle { background:#1e3a5f !important; color:#fff !important; border-color:#1e3a5f !important; }
`;
document.head.appendChild(style);

// ── Load saat halaman ready ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', loadCharts);
</script>

@endsection