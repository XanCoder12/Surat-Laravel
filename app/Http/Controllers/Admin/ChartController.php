<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartController extends Controller
{
    public function index()
    {
        return view('admin.chart.index', [
            'title' => 'Statistik & Grafik',
        ]);
    }

    // ── API endpoint: semua data chart sekaligus ──────────────────────────────
    public function data(Request $request)
    {
        $tahun = (int) $request->get('tahun', now()->year);

        return response()->json([
            'suratPerBulan'  => $this->suratPerBulan($tahun),
            'suratPerJenis'  => $this->suratPerJenis($tahun),
            'suratPerStatus' => $this->suratPerStatus($tahun),
            'slaChart'       => $this->slaChart($tahun),
            'suratPerTahap'  => $this->suratPerTahap(),
            'trendHarian'    => $this->trendHarian(),
            'topPengusul'    => $this->topPengusul($tahun),
        ]);
    }

    // ── Surat per bulan (line / bar) ──────────────────────────────────────────
    private function suratPerBulan(int $tahun): array
    {
        $data = DB::table('surats')
            ->whereYear('created_at', $tahun)
            ->selectRaw('
                MONTH(created_at) as bulan,
                COUNT(*) as total,
                SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as selesai,
                SUM(CASE WHEN status = "proses"  THEN 1 ELSE 0 END) as proses,
                SUM(CASE WHEN status = "ditolak" THEN 1 ELSE 0 END) as ditolak
            ')
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get()
            ->keyBy('bulan');

        $labels  = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $total   = [];
        $selesai = [];
        $proses  = [];
        $ditolak = [];

        for ($m = 1; $m <= 12; $m++) {
            $row      = $data->get($m);
            $total[]  = (int) ($row?->total   ?? 0);
            $selesai[]= (int) ($row?->selesai ?? 0);
            $proses[] = (int) ($row?->proses  ?? 0);
            $ditolak[]= (int) ($row?->ditolak ?? 0);
        }

        return compact('labels', 'total', 'selesai', 'proses', 'ditolak');
    }

    // ── Surat per jenis (doughnut) ────────────────────────────────────────────
    // Kolom 'jenis' di tabel surats berisi nilai enum seperti 'dinas', 'keluar', dll.
    // Sesuaikan JENIS_LABEL di sini sesuai dengan nilai aktual di DB kamu.
    private function suratPerJenis(int $tahun): array
    {
        $jenisLabel = [
            'dinas'       => 'Surat Dinas',
            'keluar'      => 'Surat Keluar',
            'masuk'       => 'Surat Masuk',
            'tugas'       => 'Surat Tugas',
            'keterangan'  => 'Surat Keterangan',
        ];

        $rows = DB::table('surats')
            ->whereYear('created_at', $tahun)
            ->selectRaw('jenis, COUNT(*) as total')
            ->groupBy('jenis')
            ->orderByDesc('total')
            ->get();

        $labels = $rows->map(fn($r) => $jenisLabel[$r->jenis] ?? ucfirst($r->jenis))->values()->toArray();
        $data   = $rows->pluck('total')->map(fn($v) => (int)$v)->toArray();

        return compact('labels', 'data');
    }

    // ── Status surat bulan ini (pie/doughnut) ─────────────────────────────────
    private function suratPerStatus(int $tahun): array
    {
        $bulan = now()->month;

        $rows = DB::table('surats')
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->get()
            ->keyBy('status');

        return [
            'proses'  => (int) ($rows->get('proses')?->total  ?? 0),
            'selesai' => (int) ($rows->get('selesai')?->total ?? 0),
            'ditolak' => (int) ($rows->get('ditolak')?->total ?? 0),
        ];
    }

    // ── SLA: tepat waktu vs terlambat per bulan (stacked bar) ────────────────
    // Menggunakan surat_tahapans untuk menghitung keterlambatan per tahap.
    // Jika kolom deadline_sla tidak ada di surats, fallback ke perbandingan tahap.
    private function slaChart(int $tahun): array
    {
        $labels    = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
        $tepat     = array_fill(0, 12, 0);
        $terlambat = array_fill(0, 12, 0);

        // Cek apakah kolom deadline_sla ada
        $hasDeadline = \Schema::hasColumn('surats', 'deadline_sla');

        if ($hasDeadline) {
            $rows = DB::table('surats')
                ->whereYear('created_at', $tahun)
                ->whereNotNull('deadline_sla')
                ->selectRaw('
                    MONTH(created_at) as bulan,
                    SUM(CASE WHEN status = "selesai" AND deadline_sla >= updated_at THEN 1 ELSE 0 END) as tepat,
                    SUM(CASE WHEN deadline_sla < NOW() AND status != "selesai" THEN 1 ELSE 0 END) as terlambat
                ')
                ->groupBy('bulan')
                ->orderBy('bulan')
                ->get()
                ->keyBy('bulan');

            for ($m = 1; $m <= 12; $m++) {
                $row            = $rows->get($m);
                $tepat[$m-1]    = (int) ($row?->tepat     ?? 0);
                $terlambat[$m-1]= (int) ($row?->terlambat ?? 0);
            }
        } else {
            // Fallback: selesai = tepat, ditolak = terlambat (per bulan)
            $rows = DB::table('surats')
                ->whereYear('created_at', $tahun)
                ->selectRaw('
                    MONTH(created_at) as bulan,
                    SUM(CASE WHEN status = "selesai" THEN 1 ELSE 0 END) as tepat,
                    SUM(CASE WHEN status = "ditolak" THEN 1 ELSE 0 END) as terlambat
                ')
                ->groupBy('bulan')
                ->get()
                ->keyBy('bulan');

            for ($m = 1; $m <= 12; $m++) {
                $row            = $rows->get($m);
                $tepat[$m-1]    = (int) ($row?->tepat     ?? 0);
                $terlambat[$m-1]= (int) ($row?->terlambat ?? 0);
            }
        }

        return compact('labels', 'tepat', 'terlambat');
    }

    // ── Distribusi surat berdasarkan tahap aktif (horizontal bar) ────────────
    // Mengambil dari tabel surat_tahapans, bukan kolom tahap_sekarang di surats
    private function suratPerTahap(): array
    {
        // Ambil surat yang masih proses, lalu cari tahap terakhir aktif / menunggu
        $rows = DB::table('surat_tahapans')
            ->join('surats', 'surat_tahapans.surat_id', '=', 'surats.id')
            ->where('surats.status', 'proses')
            ->where('surat_tahapans.status', 'menunggu')
            ->selectRaw('surat_tahapans.nama_tahap as tahap, COUNT(*) as total')
            ->groupBy('surat_tahapans.nama_tahap')
            ->orderByDesc('total')
            ->get();

        // Jika nama_tahap tidak ada, fallback ke nomor tahap
        if ($rows->isEmpty()) {
            $rows = DB::table('surat_tahapans')
                ->join('surats', 'surat_tahapans.surat_id', '=', 'surats.id')
                ->where('surats.status', 'proses')
                ->selectRaw('CONCAT("Tahap ", surat_tahapans.tahap) as tahap, COUNT(*) as total')
                ->groupBy('surat_tahapans.tahap')
                ->orderBy('surat_tahapans.tahap')
                ->get();
        }

        return [
            'labels' => $rows->pluck('tahap')->toArray(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }

    // ── Trend surat 30 hari terakhir (line area) ──────────────────────────────
    private function trendHarian(): array
    {
        $rows = DB::table('surats')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as tgl, COUNT(*) as total')
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get()
            ->keyBy('tgl');

        $labels = [];
        $data   = [];

        for ($d = 29; $d >= 0; $d--) {
            $tgl      = now()->subDays($d)->toDateString();
            $labels[] = now()->subDays($d)->format('d/m');
            $data[]   = (int) ($rows->get($tgl)?->total ?? 0);
        }

        return compact('labels', 'data');
    }

    // ── Top 5 pengusul terbanyak bulan ini (horizontal bar) ──────────────────
    private function topPengusul(int $tahun): array
    {
        $rows = DB::table('surats')
            ->join('users', 'surats.user_id', '=', 'users.id')
            ->whereYear('surats.created_at', $tahun)
            ->whereMonth('surats.created_at', now()->month)
            ->selectRaw('users.name, COUNT(*) as total')
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return [
            'labels' => $rows->pluck('name')->toArray(),
            'data'   => $rows->pluck('total')->map(fn($v) => (int)$v)->toArray(),
        ];
    }
}