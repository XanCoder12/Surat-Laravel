# Notification System - Visual Guide & Troubleshooting

## 📺 What You'll See

### Notification Popup Layout

```
┌─────────────────────────────────────────────┐
│ ✅ Surat selesai diproses!                 │ ← Icon + Title
│                                             │
│ Surat "Nota Dinas Meeting" telah selesai   │ ← Message
│ semua tahapan.                             │
│                                             │
│ [Lihat] [X]                                │ ← Action buttons
└─────────────────────────────────────────────┘
```

### Toast Notifications Examples

```
SUCCESS (Green) - Auto-dismiss after 8s
┌─────────────────────────────────────┐
│ ✅ Surat selesai diproses!          │
│ Semua tahapan telah diselesaikan    │ [Lihat] [X] │
└─────────────────────────────────────┘

INFO (Blue) - Auto-dismiss after 8s
┌─────────────────────────────────────┐
│ ℹ️ Surat baru masuk                │
│ Pengajuan "Surat Dinas" dari Budi   │ [Lihat] [X] │
└─────────────────────────────────────┘

DANGER (Red) - NO auto-dismiss (must close manually)
┌─────────────────────────────────────┐
│ ❌ Surat ditolak                    │
│ Alasan: Dokumen tidak lengkap       │ [Lihat] [X] │
└─────────────────────────────────────┘

WARNING (Amber) - Auto-dismiss after 8s
┌─────────────────────────────────────┐
│ ⚠️ Perhatian                        │
│ Deadline SLA akan segera habis      │ [Lihat] [X] │
└─────────────────────────────────────┘
```

### Position on Screen

```
Top-Right Corner (Fixed Position)
┌─ Browser Window ─────────────────────────────┐
│                                              │
│                                              │
│                   ┌──────────────┐           │
│                   │ ℹ️ Notifikasi │           │
│                   │ Pesan notif  │           │
│                   │ [Lihat] [X]  │           │
│                   └──────────────┘           │
│                                              │
│                   ┌──────────────┐           │
│                   │ ✅ Notifikasi │           │
│                   │ Pesan notif  │           │
│                   │ [Lihat] [X]  │           │
│                   └──────────────┘           │
│                                              │
└──────────────────────────────────────────────┘
```

### Topbar Badge (Admin Dashboard)

```
Admin Dashboard Top Bar
┌─────────────────────────────────────────────────┐
│ Dashboard  📬  📊  🔔 ← Bell shows unread count │
│                    [5]                          │
└─────────────────────────────────────────────────┘

When no unread: Badge hidden
When unread > 0: Badge shows number
```

## 🔄 User Interaction Flow

### Scenario 1: User Submits Letter (Surat Masuk)

```
USER BROWSER                    ADMIN BROWSER
┌────────────────┐            ┌────────────────┐
│ Fills form     │            │ Browsing       │
│ Submits surat  │──POST──────→ Create notif   │
└────────────────┘            │ in database    │
     ↓                        └────────────────┘
  Success page                      ↓
                            ┌────────────────┐
                            │ Poll /notif/poll
                            │ (every 3 sec)  │
                            └────────────────┘
                                   ↓
                            ┌────────────────┐
                            │ ℹ️ Surat baru   │
                            │ masuk popup!    │
                            │ [Lihat] [X]    │
                            └────────────────┘
                                   ↓
                            Click [X] or wait
                            8 seconds
                                   ↓
                            Popup disappears
```

### Scenario 2: Admin Approves Letter (Status Update)

```
ADMIN BROWSER               USER BROWSER
┌──────────────┐           ┌──────────────┐
│ View surat   │           │ Browsing     │
│ Click        │           │ dashboard    │
│ "Setujui"    │──POST────→│              │
└──────────────┘ (update   └──────────────┘
     ↓          surat)          ↓
 Send notif               Poll /notif/poll
 to user                  (every 3 sec)
                               ↓
                         ┌──────────────┐
                         │ 📨 Surat      │
                         │ maju ke      │
                         │ tahap 2      │
                         │ [Lihat] [X]  │
                         └──────────────┘
                               ↓
                         Click [Lihat]
                               ↓
                         Navigate to
                         surat detail
```

### Scenario 3: Admin Rejects Letter (Critical Alert)

```
ADMIN BROWSER               USER BROWSER
┌──────────────┐           ┌──────────────┐
│ View surat   │           │ Browsing     │
│ Click        │           │ dashboard    │
│ "Tolak"      │──POST────→│              │
│ Add reason   │           └──────────────┘
└──────────────┘                ↓
     ↓                   Poll /notif/poll
 Send notif                     ↓
 to user              ┌──────────────┐
                      │ ❌ Surat      │
                      │ ditolak       │
                      │ [Lihat] [X]  │
                      └──────────────┘
                             ↓
                      NO auto-dismiss!
                      User MUST click X
                      to dismiss
```

## 🛠️ Troubleshooting Decision Tree

```
Notifications not appearing?
│
├─→ Check browser console (F12)
│   ├─→ Errors found? → Fix JavaScript/CSS errors
│   └─→ No errors? → Continue
│
├─→ Check Network tab
│   ├─→ No /notif/poll requests? → Check JS loaded
│   │   └─→ Run `npm run build`
│   │   └─→ Clear cache (Ctrl+F5)
│   │
│   └─→ /notif/poll requests exist? → Check response
│       ├─→ 404 error? → Route not found (check web.php)
│       ├─→ 403 error? → Not authenticated
│       ├─→ 401 error? → Session expired
│       └─→ 200 with empty data? → No notifications yet
│
├─→ Test API manually
│   └─→ Open browser console
│       └─→ fetch('/notif/poll').then(r => r.json()).then(console.log)
│       └─→ Check if returns data
│
└─→ Check database
    └─→ php artisan tinker
    └─→ Notification::latest()->first()
    └─→ Should see notification records
```

## 📱 Mobile/Responsive View

```
Mobile Screen (360px wide)
┌────────────────────────┐
│  Dashboard             │
│                        │
│   ┌──────────────┐    │
│   │ ✅ Notifikasi│    │
│   │              │    │
│   │ Surat        │    │
│   │ diproses     │    │
│   │              │    │
│   │[Lihat] [X]  │    │
│   └──────────────┘    │
│                        │
│   Table / Content     │
│                        │
└────────────────────────┘

- Notifications still appear top-right
- Slightly smaller on mobile
- Touch-friendly buttons
- Responsive font sizes
```

## 🎨 Color Meaning Reference

```
🟢 GREEN (Success)
   ├─ Surat selesai
   ├─ Progress update
   └─ Process success

🔵 BLUE (Info)
   ├─ Surat baru masuk
   ├─ Tahap update
   └─ General information

🟠 ORANGE (Warning)
   ├─ SLA deadline close
   ├─ Perhatian penting
   └─ Action required

🔴 RED (Danger/Critical)
   ├─ Surat ditolak
   ├─ Process failed
   └─ Requires immediate action
   └─ NOTE: Does NOT auto-dismiss!
```

## ⏱️ Timing Reference

```
Timeline of notification lifecycle:

T=0ms:   Notification created in database
T=1000ms: Polling starts (after initial delay)
T=3000ms: First poll checks for notifications
T=3100ms: Notification fetched from API
T=3150ms: Toast element created & appended to DOM
T=3200ms: Slide-in animation starts (300ms)
T=3500ms: Toast fully visible
T=11500ms: Auto-dismiss triggered (for non-critical)
T=11700ms: Slide-out animation (200ms)
T=11900ms: Element removed from DOM

Critical (Red) notifications: Stay visible until manually closed
```

## 📊 Notification States

```
Database State → API Response → UI Rendering
─────────────────────────────────────────────

1. Created
   - In notifications table
   - read_at = NULL
   - Status: Unread
   - API: Returns in /notif/poll
   - UI: Shows as popup (new)

2. Read (via markRead())
   - read_at = timestamp
   - Status: Read (but still in DB)
   - API: Not returned in /notif/poll
   - UI: Not shown in popups

3. Deleted (via destroy())
   - Record deleted from table
   - Status: Gone
   - API: Not returned in /notif/poll
   - UI: Removed from DOM

4. Dismissed (via close button)
   - Same as "deleted"
   - Calls /notif/delete/{id}
   - Immediately removed from UI
   - Then deleted from database
```

## 🔍 Debug Mode

Open browser console and try:

```javascript
// Check if NotificationManager is loaded
window.NotificationManager
// Should return: Object { init, stop, poll, showToast, ... }

// Check current config
window.NotificationManager.pollInterval
// Default: 3000 (milliseconds)

// Check last poll time
window.NotificationManager.lastPollTime
// Shows when last successful poll was

// Check if currently polling
window.NotificationManager.isPolling
// true = waiting for response, false = idle

// Manually trigger a test toast
window.NotificationManager.showToast({
    id: 'test-123',
    type: 'info',
    title: 'Test Notification',
    message: 'This is a test notification to verify the system works',
    url: '/dashboard'
})

// Test delete
window.NotificationManager.dismissNotif('test-123', document.querySelector('[data-notif-id="test-123"]'))

// Stop polling (for debugging)
window.NotificationManager.stop()

// Restart polling
window.NotificationManager.init()
```

## 📝 Common Issues & Solutions

| Issue | Cause | Solution |
|-------|-------|----------|
| No notifications appear | JS not loaded | `npm run build` + Ctrl+F5 |
| Popups appear but don't update | Polling stopped | Check console for errors |
| Popups break layout | CSS not imported | Check app.css has `@import` |
| X button doesn't work | JS error | Check console for exceptions |
| Badge doesn't show count | Badge selector missing | Check `[data-unread-count]` |
| Notifications appear for old users | localStorage issue | Clear browser cache |
| Only some users get notifs | Role/permission issue | Check user.role in database |
| Notifications slow down page | Too many in DOM | Increase `maxVisibleNotifs` |

## 🚀 Performance Optimization Tips

```
If notifications feel slow:

1. Increase poll interval
   - From 3000ms to 5000ms (more latency)
   - From 3000ms to 4000ms (balance)

2. Decrease auto-dismiss time
   - From 8000ms to 5000ms (faster)
   - Users see it longer to read: Keep at 8000ms

3. Reduce max visible
   - From 5 to 3 (less DOM)
   - But users might miss notifications

4. Clear database
   - Old notifications slow polling
   - php artisan tinker
   - Notification::delete()

5. Check server response time
   - /notif/poll should respond < 100ms
   - If > 500ms: Database issue
```

---

**Need help?** Check the detailed docs:
- `QUICK_START_NOTIFICATIONS.md` - Quick testing guide
- `NOTIFICATION_SYSTEM.md` - Technical documentation  
- `IMPLEMENTATION_SUMMARY.md` - Full overview
