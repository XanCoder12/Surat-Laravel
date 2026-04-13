<?php

namespace App\Notifications;

use App\Models\Surat;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuratDeletedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Surat $surat,
        public string $alasan
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'surat_id' => $this->surat->id,
            'type'     => 'info',
            'title'    => '🗑 Surat Dihapus oleh User',
            'message'  => "User {$this->surat->user->name} menghapus surat \"{$this->surat->judul}\". Alasan: {$this->alasan}",
            'url'      => route('admin.surat.index'),
            'alasan'   => $this->alasan,
        ];
    }
}
