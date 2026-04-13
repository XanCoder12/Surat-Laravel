<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminRoleSelectionController extends Controller
{
    /**
     * Tampilkan halaman pemilihan role admin
     */
    public function show()
    {
        $user = Auth::user();

        // Jika sudah pilih role, redirect ke dashboard
        if ($user->hasSelectedRole()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.role.select');
    }

    /**
     * Simpan pilihan role admin
     */
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|in:admin_aspirasi,admin_kasubbag_tu,admin_kepala_balai',
        ]);

        $user = Auth::user();

        // Pastikan user adalah admin
        if (!$user->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        // Simpan role dan tandai sudah dipilih
        $user->update([
            'role' => $request->role,
            'role_selected' => true,
        ]);

        return redirect()->route('admin.dashboard')
            ->with('success', 'Role berhasil dipilih. Anda sekarang login sebagai ' . $user->getRoleLabel() . '.');
    }
}
