/**
 * Real-time Notification System
 * Polls for new notifications and displays popup toasts
 */

const NotifManager = {
    // Config
    pollInterval: 3000, // 3 detik
    pollDelay: 1000,    // delay pertama kali
    lastPollTime: null,
    isPolling: false,
    pollTimeoutId: null,
    containerSelector: '#notification-container',
    maxVisibleNotifs: 5,
    autoHideDuration: 8000, // 8 detik untuk auto-dismiss

    // Init
    init() {
        // Buat container jika belum ada
        if (!document.querySelector(this.containerSelector)) {
            const container = document.createElement('div');
            container.id = 'notification-container';
            container.setAttribute('role', 'region');
            container.setAttribute('aria-label', 'Notifikasi sistem');
            document.body.appendChild(container);
        }

        // Polling pertama kali setelah delay
        setTimeout(() => this.poll(), this.pollDelay);

        // Polling berkala
        this.pollTimeoutId = setInterval(() => this.poll(), this.pollInterval);

        // Cleanup on page unload
        window.addEventListener('beforeunload', () => this.stop());
    },

    // Stop polling
    stop() {
        if (this.pollTimeoutId) {
            clearInterval(this.pollTimeoutId);
            this.pollTimeoutId = null;
        }
        this.isPolling = false;
    },

    // Fetch notifikasi dari backend
    async poll() {
        if (this.isPolling) return; // Hindari race condition
        this.isPolling = true;

        try {
            const response = await fetch('/notif/poll?since=' + (this.lastPollTime || ''), {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                console.error('Polling failed:', response.status);
                this.isPolling = false;
                return;
            }

            const data = await response.json();
            
            if (data.notifications && data.notifications.length > 0) {
                data.notifications.forEach(notif => {
                    this.showToast(notif);
                });
            }

            // Update waktu polling terakhir
            if (data.server_time) {
                this.lastPollTime = data.server_time;
            }

            // Update badge unread count
            if (data.unread_count !== undefined) {
                this.updateUnreadBadge(data.unread_count);
            }
        } catch (error) {
            console.error('Polling error:', error);
        } finally {
            this.isPolling = false;
        }
    },

    // Tampilkan toast popup
    showToast(notif) {
        const toast = this.createToastElement(notif);
        const container = document.querySelector(this.containerSelector);
        
        if (!container) return;

        // Cek jumlah notif yang sedang ditampilkan
        const visibleCount = container.querySelectorAll('[role="alert"]').length;
        if (visibleCount >= this.maxVisibleNotifs) {
            // Hapus notif tertua
            const oldest = container.querySelector('[role="alert"]');
            if (oldest) {
                this.removeToast(oldest);
            }
        }

        container.appendChild(toast);

        // Auto-dismiss untuk notif non-critical
        if (notif.type !== 'danger') {
            setTimeout(() => {
                this.removeToast(toast);
            }, this.autoHideDuration);
        }
    },

    // Buat elemen toast
    createToastElement(notif) {
        const toast = document.createElement('div');
        toast.className = 'notification-toast';
        toast.setAttribute('role', 'alert');
        toast.setAttribute('aria-live', 'polite');
        toast.dataset.notifId = notif.id;

        // Warna berdasarkan type
        const bgColor = this.getBackgroundColor(notif.type);
        const borderColor = this.getBorderColor(notif.type);
        const icon = this.getIcon(notif.type);

        toast.innerHTML = `
            <div class="notification-toast-content" style="background-color: ${bgColor}; border-color: ${borderColor};">
                <div class="notification-toast-icon">${icon}</div>
                <div class="notification-toast-text">
                    <div class="notification-toast-title">${this.escapeHtml(notif.title)}</div>
                    <div class="notification-toast-message">${this.escapeHtml(notif.message)}</div>
                </div>
                <div class="notification-toast-actions">
                    ${notif.url ? `<a href="${notif.url}" class="notification-toast-link">Lihat</a>` : ''}
                    <button type="button" class="notification-toast-close" aria-label="Tutup notifikasi">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                            <path d="M12.207 4.793a1 1 0 010 1.414L9.414 8l2.793 2.793a1 1 0 01-1.414 1.414L8 9.414l-2.793 2.793a1 1 0 01-1.414-1.414L6.586 8 3.793 5.207a1 1 0 011.414-1.414L8 6.586l2.793-2.793a1 1 0 011.414 0z"/>
                        </svg>
                    </button>
                </div>
            </div>
        `;

        // Event listeners
        const closeBtn = toast.querySelector('.notification-toast-close');
        closeBtn.addEventListener('click', (e) => {
            e.preventDefault();
            this.dismissNotif(notif.id, toast);
        });

        // Jika ada URL, clik toast juga bisa navigate
        if (notif.url) {
            const titleEl = toast.querySelector('.notification-toast-title');
            titleEl.addEventListener('click', () => {
                window.location.href = notif.url;
            });
        }

        return toast;
    },

    // Dismiss notif (mark as read & hapus dari DOM)
    async dismissNotif(notifId, toastElement) {
        // Hapus dari DOM
        this.removeToast(toastElement);

        // Mark as read di backend
        try {
            await fetch(`/notif/delete/${notifId}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': this.getCsrfToken(),
                    'Content-Type': 'application/json',
                },
                credentials: 'same-origin',
            });
        } catch (error) {
            console.error('Failed to delete notification:', error);
        }
    },

    // Hapus toast dari DOM dengan animasi
    removeToast(element) {
        element.classList.add('notification-toast-exit');
        setTimeout(() => {
            element.remove();
        }, 200);
    },

    // Update unread badge di topbar
    updateUnreadBadge(count) {
        const badge = document.querySelector('[data-unread-count]');
        if (badge) {
            if (count > 0) {
                badge.textContent = count;
                badge.style.display = 'inline-flex';
            } else {
                badge.style.display = 'none';
            }
        }
    },

    // Helper methods
    getBackgroundColor(type) {
        const colors = {
            success: '#dcfce7', // green
            info: '#dbeafe',    // blue
            warning: '#fef3c7', // amber
            danger: '#fee2e2',  // red
        };
        return colors[type] || colors.info;
    },

    getBorderColor(type) {
        const colors = {
            success: '#86efac',
            info: '#7dd3fc',
            warning: '#fcd34d',
            danger: '#fca5a5',
        };
        return colors[type] || colors.info;
    },

    getIcon(type) {
        const icons = {
            success: '✅',
            info: 'ℹ️',
            warning: '⚠️',
            danger: '❌',
        };
        return icons[type] || icons.info;
    },

    getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },
};

// Start notification manager when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        NotifManager.init();
    });
} else {
    NotifManager.init();
}

// Export untuk global access
window.NotificationManager = NotifManager;
