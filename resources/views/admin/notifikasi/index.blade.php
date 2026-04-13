@extends('layouts.admin')
@section('title', 'Notifikasi')

@section('content')

<style>
    .notif-container { max-width: 900px; }
    .notif-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }
    .notif-header h5 {
        font-size: 16px;
        font-weight: 700;
        color: #1e3a5f;
        margin: 0;
    }
    .notif-actions { display: flex; gap: 6px; }
    .action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
        font-size: 14px;
        padding: 0;
        text-decoration: none;
    }
    .action-btn:hover {
        background: #f3f4f6;
        border-color: #d1d5db;
        transform: translateY(-1px);
    }
    .action-btn.read-all:hover { background: #eff6ff; border-color: #93c5fd; color: #2563eb; }
    .action-btn.delete-all:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

    .notif-filters {
        display: flex;
        gap: 6px;
        margin-bottom: 12px;
        flex-wrap: wrap;
    }
    .filter-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        border: 1.5px solid #e5e7eb;
        color: #6b7280;
        transition: all 0.2s;
        background: #fff;
    }
    .filter-pill:hover { border-color: #cbd5e1; color: #374151; text-decoration: none; transform: translateY(-1px); }
    .filter-pill.active { background: #1e3a5f; color: #fff; border-color: #1e3a5f; }
    .filter-pill.unread { background: #fffbeb; color: #92400e; border-color: #fbbf24; }
    .filter-pill.unread.active { background: #f59e0b; color: #fff; border-color: #f59e0b; }
    .filter-pill.read { background: #f0fdf4; color: #166534; border-color: #86efac; }
    .filter-pill.read.active { background: #22c55e; color: #fff; border-color: #22c55e; }

    .notif-list { display: flex; flex-direction: column; gap: 8px; }
    .notif-item {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px 16px;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    .notif-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: #e5e7eb;
        transition: all 0.2s;
    }
    .notif-item:hover {
        border-color: #d1d5db;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        transform: translateX(2px);
    }
    .notif-item.unread::before { background: #3b82f6; }
    .notif-item.unread { background: #f8fafc; }
    .notif-item.type-success::before { background: #22c55e; }
    .notif-item.type-danger::before { background: #ef4444; }
    .notif-item.type-warning::before { background: #f59e0b; }
    .notif-item.type-info::before { background: #3b82f6; }

    .notif-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .notif-icon {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        flex-shrink: 0;
    }
    .notif-icon.success { background: linear-gradient(135deg, #dcfce7, #bbf7d0); }
    .notif-icon.danger { background: linear-gradient(135deg, #fee2e2, #fecaca); }
    .notif-icon.warning { background: linear-gradient(135deg, #fef3c7, #fde68a); }
    .notif-icon.info { background: linear-gradient(135deg, #dbeafe, #bfdbfe); }

    .notif-content { flex: 1; min-width: 0; }
    .notif-message {
        font-size: 12px;
        color: #374151;
        line-height: 1.4;
        margin: 0;
    }
    .notif-time {
        font-size: 10px;
        color: #9ca3af;
        margin-top: 2px;
    }
    .notif-time i { margin-right: 2px; }

    .notif-buttons {
        display: flex;
        gap: 6px;
        flex-shrink: 0;
        align-items: center;
    }
    .notif-btn {
        width: 30px;
        height: 30px;
        border-radius: 8px;
        border: 1.5px solid #e5e7eb;
        background: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
        font-size: 13px;
        padding: 0;
        text-decoration: none;
    }
    .notif-btn:hover { transform: translateY(-2px); box-shadow: 0 2px 6px rgba(0,0,0,0.1); }
    .notif-btn.read { color: #3b82f6; border-color: #93c5fd; }
    .notif-btn.read:hover { background: #eff6ff; border-color: #3b82f6; }
    .notif-btn.view { color: #6b7280; }
    .notif-btn.view:hover { background: #f3f4f6; border-color: #9ca3af; }
    .notif-btn.delete { color: #ef4444; border-color: #fca5a5; }
    .notif-btn.delete:hover { background: #fef2f2; border-color: #ef4444; }

    .unread-badge {
        display: inline-block;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: #fff;
        font-size: 8px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 10px;
        margin-left: 6px;
        letter-spacing: 0.5px;
    }

    .empty-state {
        text-align: center;
        padding: 48px 20px;
        color: #9ca3af;
    }
    .empty-state i { font-size: 40px; opacity: 0.4; margin-bottom: 12px; display: block; }
</style>

<div class="notif-container">
    {{-- Header --}}
    <div class="notif-header">
        <h5>🔔 Notifikasi</h5>
        <div class="notif-actions">
            @if($unreadCount > 0)
            <form action="{{ route('admin.notifikasi.readAll') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="action-btn read-all" title="Tandai Semua Dibaca">
                    <i class="bi bi-check-all"></i>
                </button>
            </form>
            @endif
            <form action="{{ route('admin.notifikasi.deleteAll') }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus semua notifikasi?')">
                @csrf
                @method('DELETE')
                <input type="hidden" name="filter" value="{{ $filter }}">
                <button type="submit" class="action-btn delete-all" title="Hapus Semua">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>

    {{-- Filters --}}
    <div class="notif-filters">
        <a href="{{ route('admin.notifikasi.index', ['filter' => 'all']) }}" 
           class="filter-pill {{ $filter === 'all' ? 'active' : '' }}">
            Semua
        </a>
        <a href="{{ route('admin.notifikasi.index', ['filter' => 'unread']) }}" 
           class="filter-pill {{ $filter === 'unread' ? 'unread active' : 'unread' }}">
            <i class="bi bi-circle-fill" style="font-size:6px;"></i>
            Belum Dibaca
            @if($unreadCount > 0)
            <span class="badge bg-danger" style="font-size:9px;padding:1px 5px;">{{ $unreadCount }}</span>
            @endif
        </a>
        <a href="{{ route('admin.notifikasi.index', ['filter' => 'read']) }}" 
           class="filter-pill {{ $filter === 'read' ? 'read active' : 'read' }}">
            Sudah Dibaca
        </a>
    </div>

    {{-- List --}}
    @if($notifications->isEmpty())
    <div class="empty-state">
        <i class="bi bi-bell-slash"></i>
        <div style="font-size:13px;">Tidak ada notifikasi</div>
    </div>
    @else
    <div class="notif-list">
        @foreach($notifications as $notif)
        @php
            $isUnread = is_null($notif->read_at);
            $type = $notif->data['type'] ?? 'info';
            $icons = ['success' => '✅', 'danger' => '❌', 'warning' => '⚠️', 'info' => 'ℹ️'];
            $icon = $icons[$type] ?? 'ℹ️';
        @endphp

        <div class="notif-item {{ $isUnread ? 'unread' : '' }} type-{{ $type }}">
            <div class="notif-row">
                {{-- Icon --}}
                <div class="notif-icon {{ $type }}">{{ $icon }}</div>

                {{-- Content --}}
                <div class="notif-content">
                    <p class="notif-message">
                        {{ $notif->data['message'] ?? '' }}
                        @if($isUnread)
                        <span class="unread-badge">BARU</span>
                        @endif
                    </p>
                    <div class="notif-time">
                        <i class="bi bi-clock"></i> {{ $notif->created_at->diffForHumans() }}
                    </div>
                </div>

                {{-- Buttons - Horizontal --}}
                <div class="notif-buttons">
                    @if($isUnread)
                    <form action="{{ route('admin.notifikasi.read', $notif->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="notif-btn read" title="Tandai Dibaca">
                            <i class="bi bi-check-lg"></i>
                        </button>
                    </form>
                    @endif
                    <a href="{{ $notif->data['url'] ?? route('admin.dashboard') }}" class="notif-btn view" title="Lihat Detail">
                        <i class="bi bi-eye"></i>
                    </a>
                    <form action="{{ route('admin.notifikasi.delete', $notif->id) }}" method="POST" onsubmit="return confirm('Hapus notifikasi?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="notif-btn delete" title="Hapus">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Pagination --}}
    <div class="mt-3">
        {{ $notifications->links() }}
    </div>
    @endif
</div>

@endsection

