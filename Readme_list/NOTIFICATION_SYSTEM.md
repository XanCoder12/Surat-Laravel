# Notification System Documentation

## Overview
This document describes the real-time notification system for the Surat Metrologi application. The system provides popup notifications to both administrators and users with the ability to dismiss, delete, and navigate to relevant details.

## Features

### Admin Notifications
- **Surat Masuk (Letter Received)**: Notification when a user submits a new letter
- **Surat Diproses**: Notification when another admin processes/approves/rejects a letter

### User Notifications  
- **Surat Status Update**: Notifications when their letter moves to a new stage
- **Surat Selesai**: Notification when their letter is completely processed
- **Surat Ditolak**: Notification when their letter is rejected

### Popup Features
- ✅ Real-time polling (every 3 seconds)
- ✅ Auto-dismiss after 8 seconds (except for critical messages)
- ✅ Dismissible with close button
- ✅ Navigate to letter detail by clicking notification
- ✅ Delete notification from backend
- ✅ Unread count badge
- ✅ Color-coded by type (success, info, warning, danger)
- ✅ Animations (slide in/out)

## How It Works

### Backend (Laravel)

#### Notification Classes
Located in `app/Notifications/`:
- `SuratMasukNotification.php` - Triggered when user submits a letter
- `SuratStatusNotification.php` - Triggered when letter status changes
- `SuratDiprosesNotification.php` - Triggered when admin processes a letter

#### Notification API Endpoints
All endpoints are under `/notif/` route prefix:

```php
GET  /notif/poll                      // Poll for new notifications
POST /notif/read/{id}                 // Mark single as read  
POST /notif/read-all                  // Mark all as read
POST /notif/delete/{id}               // Delete single notification
POST /notif/delete-all                // Delete all notifications
```

#### NotificationApiController
Main controller at `app/Http/Controllers/NotificationApiController.php`:

- **poll()** - Returns unread notifications since last poll with server time
- **markRead()** - Marks a notification as read
- **markAllRead()** - Marks all unread notifications as read
- **destroy()** - Deletes a single notification
- **destroyAll()** - Deletes all notifications

### Frontend (JavaScript/HTML)

#### Main Files
- `resources/js/notifications.js` - Main polling and UI logic
- `resources/css/notifications.css` - Styling for notification toasts
- `resources/js/app.js` - Imports notifications module

#### How Polling Works
1. **Initialization**: When page loads, `NotifManager.init()` is called
2. **Interval Setup**: Every 3 seconds, `poll()` fetches `/notif/poll` API
3. **New Notifications**: Any unread notifications are fetched and displayed as toasts
4. **Toast Display**: Each notification shows as a popup in top-right corner
5. **Auto-dismiss**: Non-critical (non-danger) notifications auto-hide after 8 seconds
6. **User Action**: User can click "Lihat" to navigate or X button to dismiss

#### Toast Styling
Colors by type:
- **success** (green): #dcfce7 background, #86efac border
- **info** (blue): #dbeafe background, #7dd3fc border
- **warning** (amber): #fef3c7 background, #fcd34d border
- **danger** (red): #fee2e2 background, #fca5a5 border

Icons:
- success: ✅
- info: ℹ️
- warning: ⚠️
- danger: ❌

## Triggering Notifications

### When Letter is Submitted (User)
In `app/Http/Controllers/User/SuratController.php`:
```php
// Line 82
User::where('role', 'admin')->get()->each(
    fn($a) => $a->notify(new SuratMasukNotification($surat))
);
```
This sends notification to ALL admins when a user submits a letter.

### When Admin Approves Letter
In `app/Http/Controllers/Admin/SuratController.php`:
```php
// Line 59-65: Notify user about progress
$surat->user->notify(new SuratStatusNotification(...));

// Line 91: Notify other admins
$this->notifAdminLain($surat, Auth::user(), 'disetujui');
```

### When Admin Rejects Letter
Similar flow:
```php
// Line 116-122: Notify user about rejection
$surat->user->notify(new SuratStatusNotification(...));

// Line 125: Notify other admins
$this->notifAdminLain($surat, Auth::user(), 'ditolak');
```

## Testing

### Manual Testing Steps

1. **Test Surat Masuk Notification**
   - Login as user
   - Create a new surat (submit)
   - Login as different admin in another browser
   - Should see "Surat baru masuk" popup immediately (within 3 seconds)

2. **Test Status Update Notification**
   - Login as admin
   - Go to surat antrian
   - Click on a surat and approve/reject it
   - Switch to user browser
   - Should see status update notification

3. **Test Dismissal**
   - Click X button on any popup
   - Should disappear with animation

4. **Test Navigation**
   - Click "Lihat" button or notification title
   - Should navigate to letter detail page

5. **Test Auto-dismiss**
   - Submit a surat (generates info notification)
   - Wait 8 seconds
   - Popup should disappear automatically
   - Try with rejection (danger type - should NOT auto-dismiss)

### Browser Console
Open browser DevTools Console to see:
```javascript
// Check if NotificationManager is loaded
console.log(window.NotificationManager);

// Manually trigger a test notification (if needed)
NotificationManager.showToast({
    id: 'test-1',
    type: 'info',
    title: 'Test Notification',
    message: 'This is a test notification',
    url: '/dashboard'
});
```

## Customization

### Change Poll Interval
In `resources/js/notifications.js`:
```javascript
pollInterval: 3000, // Change to desired milliseconds
```

### Change Auto-dismiss Duration
```javascript
autoHideDuration: 8000, // Change to desired milliseconds
```

### Change Max Visible Notifications
```javascript
maxVisibleNotifs: 5, // Max popups shown simultaneously
```

### Add New Notification Type
1. Create notification class in `app/Notifications/`
2. Set `'type'` in `toArray()` to one of: 'success', 'info', 'warning', 'danger'
3. Send notification: `$user->notify(new YourNotification(...))`

## Troubleshooting

### Notifications Not Showing
1. Check browser console for JavaScript errors
2. Verify `/notif/poll` endpoint returns data: Open DevTools Network tab, look for `/notif/poll` requests
3. Check database: `SELECT * FROM notifications WHERE user_id = X;`
4. Clear browser cache: Ctrl+F5

### Notifications Showing but Not Updating
1. Check Network tab - is `/notif/poll` being called every 3 seconds?
2. If not, check if `NotificationManager.init()` was called
3. Check for JavaScript errors in Console tab

### Slow Performance
- Increase `pollInterval` to reduce frequency (e.g., 5000 for 5 seconds)
- Limit notifications in database: `/notif/poll` only fetches 10 most recent
- Check for other heavy scripts interfering

## API Response Format

### Success Response
```json
{
    "notifications": [
        {
            "id": "uuid",
            "type": "success|info|warning|danger",
            "title": "Notification Title",
            "message": "Notification message body",
            "url": "/path/to/detail",
            "read": false,
            "time": "3 minutes ago",
            "created": "2026-04-07T05:32:27.842Z"
        }
    ],
    "unread_count": 5,
    "server_time": "2026-04-07T05:32:27.842Z"
}
```

## Files Modified/Created

### Created
- `resources/js/notifications.js` - Main notification logic
- `resources/css/notifications.css` - Toast styling
- `NOTIFICATION_SYSTEM.md` - This documentation

### Modified
- `resources/js/app.js` - Added notifications import
- `resources/css/app.css` - Added notifications.css import
- `resources/views/layouts/admin.blade.php` - Added data-unread-count attribute to badge

## Database Schema

The `notifications` table (created by Laravel migration):
```
- id (UUID, primary key)
- type (string) - 'App\\Notifications\\ClassName'
- notifiable_type (string) - 'App\\Models\\User'
- notifiable_id (bigint) - User ID
- data (text) - JSON with type, title, message, url, etc.
- read_at (timestamp, nullable) - When marked as read
- created_at (timestamp)
- updated_at (timestamp)
```

## Performance Notes

- Polling every 3 seconds keeps notifications fresh without overloading server
- Each poll request is lightweight (only fetches unread since last poll)
- Notifications are deleted (soft delete) when user dismisses them
- Max 10 notifications per poll prevents large payloads
- Toast animations use CSS for smooth performance

## Security

- All API endpoints protected by `auth` middleware
- CSRF token required for POST requests
- Notifications scoped to authenticated user only
- Cannot view other users' notifications
- Cannot delete other users' notifications
