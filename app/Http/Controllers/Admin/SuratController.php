<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use App\Models\SuratTahapan;
use App\Models\User;
use App\Notifications\SuratStatusNotification;
use App\Notifications\SuratDiprosesNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuratController extends Controller
{
    public function index(Request $request)
    {
        $query = Surat::with('user')->latest();

        if ($request->filled('jenis'))  $query->where('jenis', $request->jenis);
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('tahap'))  $query->where('tahap_sekarang', $request->tahap);
        if ($request->filled('search')) $query->where('judul', 'like', '%'.$request->search.'%');

        $surats = $query->paginate(15)->withQueryString();

        return view('admin.surat.index', compact('surats'));
    }

    public function show(Surat $surat)
    {
        $surat->load(['user', 'tahapans.diprosesByUser']);
        return view('admin.surat.show', compact('surat'));
    }

    public function setujui(Request $request, Surat $surat)
    {
        $request->validate([
            'catatan'     => 'nullable|string|max:500',
            'nomor_surat' => 'nullable|string|max:100',
        ]);

        // Tandai tahap sekarang selesai
        SuratTahapan::where('surat_id', $surat->id)
            ->where('tahap', $surat->tahap_sekarang)
            ->update([
                'status'        => 'selesai',
                'diproses_oleh' => Auth::id(),
                'catatan'       => $request->catatan,
                'selesai_pada'  => now(),
            ]);

        $tahapBerikutnya = $surat->tahap_sekarang + 1;

        if ($tahapBerikutnya > 10) {
            $surat->update(['status' => 'selesai', 'tahap_sekarang' => 10]);

            // Notif ke pengusul: SELESAI
            $surat->user->notify(new SuratStatusNotification(
                surat  : $surat,
                type   : 'success',
                title  : '✅ Surat selesai diproses!',
                message: "Surat \"{$surat->judul}\" telah selesai semua tahapan.",
                url    : route('user.surat.show', $surat->id),
            ));
        } else {
            $updateData = ['tahap_sekarang' => $tahapBerikutnya];

            if ($surat->tahap_sekarang === 5 && $request->filled('nomor_surat')) {
                $updateData['nomor_surat']   = $request->nomor_surat;
                $updateData['tanggal_surat'] = now()->toDateString();
            }

            $surat->update($updateData);
            $surat->refresh();

            SuratTahapan::where('surat_id', $surat->id)
                ->where('tahap', $tahapBerikutnya)
                ->update(['status' => 'proses']);

            // Notif ke pengusul: maju tahap
            $surat->user->notify(new SuratStatusNotification(
                surat  : $surat,
                type   : 'info',
                title  : "📨 Surat maju ke tahap {$tahapBerikutnya}",
                message: "\"{$surat->judul}\" sudah diverifikasi — sekarang: {$surat->nama_tahap}.",
                url    : route('user.surat.show', $surat->id),
            ));

            // Notif ke admin lain: surat diproses
            $this->notifAdminLain($surat, Auth::user(), 'disetujui');
        }

        return redirect()->route('admin.surat.show', $surat)
                         ->with('success', 'Surat berhasil disetujui dan maju ke tahap berikutnya.');
    }

    public function tolak(Request $request, Surat $surat)
    {
        $request->validate([
            'catatan' => 'required|string|max:500',
        ]);

        SuratTahapan::where('surat_id', $surat->id)
            ->where('tahap', $surat->tahap_sekarang)
            ->update([
                'status'        => 'ditolak',
                'diproses_oleh' => Auth::id(),
                'catatan'       => $request->catatan,
                'selesai_pada'  => now(),
            ]);

        $surat->update(['status' => 'ditolak']);

        // Notif ke pengusul: DITOLAK
        $surat->user->notify(new SuratStatusNotification(
            surat  : $surat,
            type   : 'danger',
            title  : '❌ Surat ditolak',
            message: "Surat \"{$surat->judul}\" ditolak. Alasan: {$request->catatan}",
            url    :route('user.surat.show', $surat->id),
        ));

        // Notif ke admin lain
        $this->notifAdminLain($surat, Auth::user(), 'ditolak');

        return redirect()->route('admin.surat.index')
                         ->with('success', 'Surat telah ditolak.');
    }

    // Kirim notif ke semua admin kecuali yang sedang login
    private function notifAdminLain(Surat $surat, $currentUser, string $aksi): void
    {
        User::where('role', 'admin')
            ->where('id', '!=', $currentUser->id)
            ->get()
            ->each(function ($admin) use ($surat, $currentUser, $aksi) {
                $admin->notify(new SuratDiprosesNotification(
                    surat         : $surat,
                    diprosesByUser: $currentUser,
                    aksi          : $aksi,
                ));
            });
    }
}