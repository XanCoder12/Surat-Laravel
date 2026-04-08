# Real-time Notification System - Quick Start Guide

## ✅ What's Been Implemented

Your notification system now has:

1. **Real-time Polling** - Checks for new notifications every 3 seconds
2. **Popup Toasts** - Displays notifications in the top-right corner
3. **Auto-dismiss** - Non-critical notifications disappear after 8 seconds
4. **Dismissible** - Users can close notifications with an X button
5. **Clickable** - Click notification title to navigate to letter detail
6. **Color-coded** - Different colors for success/info/warning/danger
7. **Unread Badge** - Shows unread notification count in topbar
8. **Animations** - Smooth slide-in/out animations

## 📁 Files Added/Modified

### New Files Created
```
resources/js/notifications.js         - Main notification polling logic
resources/css/notifications.css       - Toast styling and animations
NOTIFICATION_SYSTEM.md                - Full technical documentation
```

### Files Modified
```
resources/js/app.js                   - Added notifications import
resources/css/app.css                 - Added notifications.css import
resources/views/layouts/admin.blade.php - Updated topbar badge
```

## 🚀 How to Test

### Test 1: Basic Notification Flow (Surat Masuk)
```
1. Open 2 browsers: one for USER, one for ADMIN
2. Login USER in browser 1
3. Login ADMIN in browser 2
4. In USER browser: Go to "Surat" > "Ajukan Surat" > Submit form
5. In ADMIN browser: Look at top-right corner
6. ✅ Should see "Surat baru masuk" popup within 3 seconds
```

### Test 2: Status Update Notification
```
1. In ADMIN browser: Go to "Antrian Surat"
2. Click on the surat that was just submitted
3. Click "Setujui" button
4. In USER browser: Look at top-right corner
5. ✅ Should see "Surat maju ke tahap 2" (or similar) popup
```

### Test 3: Rejection Notification
```
1. In ADMIN browser: Go to "Antrian Surat"
2. Click on any surat
3. Click "Tolak" button and add catatan
4. In USER browser: Look at top-right corner
5. ✅ Should see "Surat ditolak" popup (RED color - won't auto-dismiss)
```

### Test 4: Close/Dismiss Button
```
1. Any popup appears
2. Click X button on top-right of notification
3. ✅ Notification should disappear with animation
```

### Test 5: Navigate from Notification
```
1. When notification appears, click on the title text
2. ✅ Should navigate to letter detail page
3. OR click "Lihat" button
4. ✅ Should also navigate to letter detail
```

### Test 6: Auto-dismiss
```
1. User submits a surat (info notification)
2. Watch notification in top-right
3. ✅ After 8 seconds, should disappear automatically
4. BUT: Rejection notification (danger type) should NOT auto-dismiss
```

## 🔧 Customization Options

### Change Polling Frequency (Default: 3 seconds)
Edit `resources/js/notifications.js` line 8:
```javascript
pollInterval: 5000, // Change to 5 seconds
```

### Change Auto-dismiss Time (Default: 8 seconds)
Edit `resources/js/notifications.js` line 15:
```javascript
autoHideDuration: 10000, // Change to 10 seconds
```

### Change Maximum Visible Notifications (Default: 5)
Edit `resources/js/notifications.js` line 14:
```javascript
maxVisibleNotifs: 3, // Show max 3 popups at once
```

### Change Notification Positioning
Edit `resources/css/notifications.css` lines 4-6:
```css
#notification-container {
    top: 50px;      /* Distance from top */
    right: 50px;    /* Distance from right */
    /* Or use: left, bottom instead */
}
```

## 🐛 Troubleshooting

### Q: Notifications not appearing?
**A:** 
1. Check browser Console (F12) for errors
2. Open Network tab, submit a surat, look for `/notif/poll` requests
3. Make sure you're seeing the requests every 3 seconds
4. If no requests: rebuild frontend with `npm run build`

### Q: Notifications appear but don't update?
**A:**
1. Check if `/notif/poll` is being called in Network tab
2. If not: hard refresh (Ctrl+F5) to clear cache
3. Check Console for JavaScript errors

### Q: Popup looks weird/broken?
**A:**
1. Make sure CSS was imported: Check if `notifications.css` is in `app.css`
2. Rebuild frontend: `npm run build`
3. Clear browser cache: Ctrl+F5

### Q: Want to see unread count?
**A:**
1. Look at bell icon in topbar
2. If there are unread notifications, it should show a number
3. If not showing, check that `[data-unread-count]` is in your topbar

## 📊 Backend API Reference

The system uses these endpoints:

```
GET  /notif/poll           - Get new notifications
POST /notif/delete/{id}    - Delete a notification
POST /notif/read/{id}      - Mark as read
POST /notif/read-all       - Mark all as read
POST /notif/delete-all     - Delete all notifications
```

Response format:
```json
{
    "notifications": [
        {
            "id": "uuid-string",
            "type": "success|info|warning|danger",
            "title": "Notification title",
            "message": "Full message text",
            "url": "/path/to/navigate/to",
            "time": "3 minutes ago"
        }
    ],
    "unread_count": 3,
    "server_time": "ISO timestamp"
}
```

## 🎨 Color Reference

| Type    | Background | Border  | Icon |
|---------|-----------|---------|------|
| success | #dcfce7   | #86efac | ✅   |
| info    | #dbeafe   | #7dd3fc | ℹ️   |
| warning | #fef3c7   | #fcd34d | ⚠️   |
| danger  | #fee2e2   | #fca5a5 | ❌   |

## 📝 Notes

- **Polling** (instead of WebSocket): Simpler to implement, works on all servers
- **Every 3 seconds**: Balance between responsiveness and server load
- **8 second auto-dismiss**: Standard for toast notifications
- **5 max visible**: Prevent screen clutter
- **JavaScript only**: No jQuery or heavy dependencies
- **CSRF protected**: All POST requests use Laravel CSRF tokens

## ✨ What Happens When

| Event | Who Notified | Type | Auto-dismiss |
|-------|--------------|------|--------------|
| User submits surat | All admins | info | Yes (8s) |
| Admin approves | User + other admins | info/success | Yes (8s) |
| Admin rejects | User + other admins | danger | No |
| Surat reaches final stage | User | success | Yes (8s) |

## 🔗 Related Documentation

- Full tech docs: See `NOTIFICATION_SYSTEM.md`
- Backend code: `app/Http/Controllers/NotificationApiController.php`
- Notification classes: `app/Notifications/*.php`
- Frontend code: `resources/js/notifications.js`
- Styling: `resources/css/notifications.css`

## 🎯 Next Steps

1. Test all the flows mentioned above
2. Check that notifications work on both admin and user sides
3. Customize polling interval/timing if needed
4. Adjust CSS if colors don't match your design
5. Check database: `php artisan tinker` then `Notification::latest()->limit(5)->get()`

---

**Ready to test?** Start with Test 1 above and follow the steps. You should see popups within 3 seconds! 🚀
