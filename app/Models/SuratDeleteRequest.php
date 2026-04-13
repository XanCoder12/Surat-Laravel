<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuratDeleteRequest extends Model
{
    protected $fillable = [
        'surat_id',
        'user_id',
        'admin_id',
        'alasan',
        'status',
        'admin_catatan',
        'admin_approved_at',
    ];

    protected $casts = [
        'admin_approved_at' => 'datetime',
    ];

    public function surat()
    {
        return $this->belongsTo(Surat::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'disetujui';
    }

    public function isRejected()
    {
        return $this->status === 'ditolak';
    }
}
