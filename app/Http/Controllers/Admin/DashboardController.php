<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $bulanIni = now()->month;
        $tahunIni = now()->year;

        // Statistik
        $totalBulanIni = Surat::whereMonth('created_at', $bulanIni)
                              ->whereYear('created_at', $tahunIni)
                              ->count();

        $totalSelesai  = Surat::whereMonth('created_at', $bulanIni)
                              ->whereYear('created_at', $tahunIni)
                              ->where('status', 'selesai')->count();

        $totalProses   = Surat::whereMonth('created_at', $bulanIni)
                              ->whereYear('created_at', $tahunIni)
                              ->where('status', 'proses')->count();

        $totalTerlambat = Surat::where('status', 'proses')
                               ->whereNotNull('deadline_sla')
                               ->where('deadline_sla', '<', now())
                               ->count();

        // Antrian: surat yang butuh aksi berdasarkan role user ini
        // Sesuaikan kondisi tahap dengan role masing-masing:
        // Arsiparis    → tahap 2
        // Kasubbag TU  → tahap 3
        // Kepala Balai → tahap 4
        // Persuratan   → tahap 5,6,7,8,9,10
        // Admin lama   → semua
        $admin = Auth::user();

        $antrianQuery = Surat::where('status', 'proses')
            ->with('user')
            ->orderByRaw("CASE WHEN deadline_sla < NOW() THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->limit(10);

        // Filter berdasarkan role
        if ($admin->role === 'admin_aspirasi') {
            $antrianQuery->where(function($q) {
                $q->where('tahap_sekarang', 2)
                  ->orWhere('tahap_sekarang', '>=', 5);
            });
        } elseif ($admin->role === 'admin_kasubbag_tu') {
            $antrianQuery->where('tahap_sekarang', 3);
        } elseif ($admin->role === 'admin_kepala_balai') {
            $antrianQuery->where('tahap_sekarang', 4);
        }
        // admin lama (role='admin') tetap bisa lihat semua

        $antrian = $antrianQuery->get();

        // Rekap per jenis surat bulan ini
        $rekapJenis = Surat::whereMonth('created_at', $bulanIni)
                           ->whereYear('created_at', $tahunIni)
                           ->selectRaw('jenis, COUNT(*) as jumlah')
                           ->groupBy('jenis')
                           ->pluck('jumlah', 'jenis');

        // Surat terbaru
        $suratTerbaru = Surat::with('user')
                             ->latest()
                             ->limit(5)
                             ->get();

        // Data surat dengan siapa saja yang telah memproses (bulan ini)
        $suratDenganPengolah = Surat::whereMonth('created_at', $bulanIni)
                                     ->whereYear('created_at', $tahunIni)
                                     ->with([
                                         'user',
                                         'tahapans' => function ($query) {
                                             $query->where('status', 'selesai')
                                                   ->whereNotNull('diproses_oleh')
                                                   ->with('diprosesByUser')
                                                   ->orderBy('tahap');
                                         }
                                     ])
                                     ->orderByDesc('created_at')
                                     ->limit(8)
                                     ->get();

        // Jumlah antrian untuk badge sidebar (share ke layout)
        $antrianCount = $antrian->count();

        return view('admin.dashboard', compact(
            'totalBulanIni', 'totalSelesai', 'totalProses', 'totalTerlambat',
            'antrian', 'rekapJenis', 'suratTerbaru', 'suratDenganPengolah', 'antrianCount'
        ));
    }
}