# Settings Tab Implementation Guide

## Overview
This document describes the comprehensive implementation of the Settings tab functionality in the Procurement System. All interactive elements are now fully functional with proper state management, validation, and persistent storage.

## Implemented Features

### 1. Profile Tab
**Functionality:**
- ✅ Update user profile information (name, email, position, department)
- ✅ Change password with current password verification
- ✅ Upload company logo (for non-superadmin users)
- ✅ Remove company logo
- ✅ Real-time form validation
- ✅ Session data synchronization

**Endpoints:**
- `POST /settings/profile` - Update profile
- `POST /settings/password` - Change password
- `POST /settings/logo/upload` - Upload logo
- `DELETE /settings/logo/remove` - Remove logo

**Validation:**
- Email uniqueness check
- Password minimum 8 characters
- Password confirmation matching
- Logo file format validation (JPEG, PNG, GIF, SVG, WebP)
- Logo file size limit (2MB)

### 2. Security Tab
**Functionality:**
- ✅ Change password with enhanced security
- ✅ Password visibility toggle
- ✅ Password strength requirements display
- ✅ Security tips and best practices

**Features:**
- Toggle password visibility with eye icon
- Clear password requirements (minimum 8 characters)
- Current password verification
- New password confirmation
- Security recommendations sidebar

### 3. Preferences Tab
**Functionality:**
- ✅ Language selection (English, Filipino, Chinese)
- ✅ Date format selection (MM/DD/YYYY, DD/MM/YYYY, YYYY-MM-DD)
- ✅ Time format selection (12-hour, 24-hour)
- ✅ Timezone selection
- ✅ Auto-save drafts toggle
- ✅ Compact view toggle
- ✅ Persistent storage in database
- ✅ Automatic preference loading on page load

**Endpoints:**
- `POST /settings/preferences` - Save preferences
- `GET /settings/preferences` - Load preferences

**Data Storage:**
All preferences are stored in the `settings` table with user-specific keys:
- `user.{user_id}.language`
- `user.{user_id}.date_format`
- `user.{user_id}.time_format`
- `user.{user_id}.timezone`
- `user.{user_id}.auto_save`
- `user.{user_id}.compact_view`

### 4. Appearance Tab (Non-Superadmin Only)
**Functionality:**
- ✅ Company logo upload
- ✅ Logo preview
- ✅ Logo removal
- ✅ Logo usage tips

**Note:** Superadmin users should use the dedicated Branding & UI tab instead.

### 5. Notifications Tab
**Functionality:**
- ✅ Purchase order notifications toggle
  - New PO created
  - PO approved
  - PO rejected
- ✅ System notifications toggle
  - System updates
  - Security alerts
- ✅ Email notifications toggle
  - Daily summary
  - Weekly reports
- ✅ Persistent storage in database
- ✅ Automatic settings loading on page load

**Endpoints:**
- `POST /settings/notifications` - Save notification settings
- `GET /settings/notifications` - Load notification settings

**Data Storage:**
All notification preferences are stored in the `settings` table:
- `user.{user_id}.notif_po_created`
- `user.{user_id}.notif_po_approved`
- `user.{user_id}.notif_po_rejected`
- `user.{user_id}.notif_system_updates`
- `user.{user_id}.notif_security`
- `user.{user_id}.email_daily_summary`
- `user.{user_id}.email_weekly_report`

## Technical Implementation

### Backend Components

**Controller: SettingsController.php**
- `index()` - Display settings page
- `updateProfile()` - Update user profile
- `updatePassword()` - Change password
- `uploadLogo()` - Handle logo upload
- `removeLogo()` - Remove logo
- `updatePreferences()` - Save user preferences (NEW)
- `getPreferences()` - Load user preferences (NEW)
- `updateNotifications()` - Save notification settings (NEW)
- `getNotifications()` - Load notification settings (NEW)

**Model: Setting.php**
- `get($key, $default)` - Retrieve setting value
- `set($key, $value)` - Store/update setting value
- `getCompanyLogo()` - Get company logo path

**Validation Rules:**

*Profile Update:*
```php
'name' => 'required|string|max:255'
'email' => 'required|email|max:255|unique:users,email,{user_id}'
'position' => 'nullable|string|max:255'
'department' => 'nullable|string|max:255'
```

*Password Update:*
```php
'current_password' => 'required'
'new_password' => 'required|min:8|confirmed'
```

*Logo Upload:*
```php
'logo' => 'required|image|mimes:jpeg,jpg,png,gif,svg,webp|max:2048'
```

*Preferences:*
```php
'language' => 'required|in:en,fil,zh'
'date_format' => 'required|in:MM/DD/YYYY,DD/MM/YYYY,YYYY-MM-DD'
'time_format' => 'required|in:12,24'
'timezone' => 'required|string|max:100'
'auto_save' => 'boolean'
'compact_view' => 'boolean'
```

*Notifications:*
```php
'notif_po_created' => 'boolean'
'notif_po_approved' => 'boolean'
'notif_po_rejected' => 'boolean'
'notif_system_updates' => 'boolean'
'notif_security' => 'boolean'
'email_daily_summary' => 'boolean'
'email_weekly_report' => 'boolean'
```

### Frontend Components

**JavaScript: resources/js/pages/settings.js**

**Functions:**
- `initializePreferencesForm()` - Setup preferences form handling
- `initializeNotificationsForm()` - Setup notifications form handling
- `loadUserPreferences()` - Load and populate user preferences
- `loadNotificationSettings()` - Load and populate notification settings
- `showToast()` - Display toast notifications
- `togglePassword()` - Toggle password visibility

**Features:**
- AJAX form submissions
- Real-time validation
- Loading states on buttons
- Success/error toast notifications
- Automatic preference loading
- Form state persistence
- CSRF token handling

**View: resources/views/settings/index.blade.php**

**Tabs:**
1. Profile - User information and logo management
2. Security - Password change
3. Preferences - System preferences
4. Appearance - Logo management (non-superadmin)
5. Notifications - Notification preferences

## User Interface Features

### Form Validation
- ✅ Client-side validation before submission
- ✅ Server-side validation with detailed error messages
- ✅ Real-time feedback via toast notifications
- ✅ Disabled state during form submission
- ✅ Automatic re-enabling after submission

### State Management
- ✅ Form data persisted to database
- ✅ Settings loaded on page load
- ✅ Session data updated after profile changes
- ✅ Checkbox states preserved
- ✅ Select dropdown values restored

### Error Handling
- ✅ Network error handling
- ✅ Validation error display
- ✅ Success confirmation messages
- ✅ Graceful degradation
- ✅ User-friendly error messages

### UI Responsiveness
- ✅ Mobile-responsive layout
- ✅ Bootstrap 5 grid system
- ✅ Responsive tabs
- ✅ Touch-friendly controls
- ✅ Proper spacing and alignment

## Testing Checklist

### Profile Tab Tests
- [ ] Update name successfully
- [ ] Update email with validation
- [ ] Update position and department
- [ ] Change password with correct current password
- [ ] Fail password change with incorrect current password
- [ ] Password confirmation validation
- [ ] Upload logo (valid formats)
- [ ] Reject invalid logo formats
- [ ] Reject oversized logos (>2MB)
- [ ] Remove logo successfully
- [ ] Session data updates after profile save

### Preferences Tab Tests
- [ ] Save language preference
- [ ] Save date format preference
- [ ] Save time format preference
- [ ] Save timezone preference
- [ ] Toggle auto-save checkbox
- [ ] Toggle compact view checkbox
- [ ] Load saved preferences on page refresh
- [ ] All dropdowns populated correctly
- [ ] Checkboxes reflect saved state

### Notifications Tab Tests
- [ ] Toggle PO created notification
- [ ] Toggle PO approved notification
- [ ] Toggle PO rejected notification
- [ ] Toggle system updates notification
- [ ] Toggle security alerts notification
- [ ] Toggle daily email summary
- [ ] Toggle weekly report
- [ ] Load saved notification settings
- [ ] All toggles work independently
- [ ] Settings persist after page refresh

### Cross-Browser Tests
- [ ] Chrome/Edge (Chromium)
- [ ] Firefox
- [ ] Safari
- [ ] Mobile browsers (iOS/Android)

### Role-Based Tests
- [ ] Requestor can access all tabs
- [ ] Requestor can see Appearance tab
- [ ] Superadmin cannot see Appearance tab
- [ ] All settings work for both roles

## Usage Instructions

### For End Users

**Updating Profile:**
1. Navigate to Settings
2. Click on Profile tab
3. Update desired fields
4. Click "Update Profile"
5. Wait for success confirmation

**Changing Password:**
1. Go to Profile or Security tab
2. Enter current password
3. Enter new password (min 8 characters)
4. Confirm new password
5. Click "Change Password"

**Setting Preferences:**
1. Click Preferences tab
2. Select desired language, formats, timezone
3. Toggle switches as needed
4. Click "Save Preferences"
5. Settings apply immediately

**Managing Notifications:**
1. Click Notifications tab
2. Toggle desired notifications on/off
3. Click "Save Notification Settings"
4. Changes take effect immediately

### For Developers

**Adding New Preference:**
1. Add setting to database via migration/seeder
2. Add validation rule in `updatePreferences()`
3. Add form field in `preferences` tab
4. Add handling in `settings.js`
5. Update default value in `getPreferences()`

**Adding New Notification Type:**
1. Add checkbox in notifications tab
2. Add ID to JavaScript handler
3. Add validation rule
4. Add storage key pattern
5. Update load/save functions

## Database Schema

### Settings Table
```sql
CREATE TABLE settings (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT,
    created_at DATETIME,
    updated_at DATETIME
);
```

**Example Records:**
```
user.{uuid}.language = 'en'
user.{uuid}.date_format = 'DD/MM/YYYY'
user.{uuid}.time_format = '12'
user.{uuid}.timezone = 'Asia/Manila'
user.{uuid}.auto_save = 1
user.{uuid}.compact_view = 0
user.{uuid}.notif_po_created = 1
...
```

## Security Considerations

1. **Authentication:** All endpoints require active session
2. **Authorization:** Users can only modify their own settings
3. **CSRF Protection:** All POST requests include CSRF token
4. **Password Hashing:** Passwords hashed with bcrypt
5. **File Validation:** Strict file type and size validation
6. **Input Sanitization:** All inputs validated and sanitized
7. **SQL Injection Prevention:** Eloquent ORM used throughout
8. **XSS Protection:** Blade escaping enabled

## Performance Optimization

1. **Caching:** Settings cached in browser session
2. **AJAX Requests:** Minimal data transfer
3. **Lazy Loading:** Settings loaded only when tab accessed
4. **Debouncing:** Form submissions debounced (3 seconds)
5. **Optimized Queries:** Single query per setting type
6. **Asset Bundling:** JavaScript bundled via Vite

## Troubleshooting

**Settings Not Saving:**
- Check browser console for errors
- Verify CSRF token is present
- Check network tab for failed requests
- Ensure database connection is active

**Preferences Not Loading:**
- Clear browser cache
- Check console for JavaScript errors
- Verify API endpoints are accessible
- Check database for user settings

**Logo Upload Failing:**
- Verify file size (<2MB)
- Check file format (JPEG, PNG, GIF, SVG, WebP)
- Ensure storage directory has write permissions
- Check PHP upload_max_filesize setting

**Toast Notifications Not Showing:**
- Check Bootstrap JavaScript is loaded
- Verify toast container exists
- Check browser console for errors
- Ensure settings.js is loaded

## Future Enhancements

Potential improvements for future versions:
- Multi-language support (i18n)
- Dark mode toggle
- Custom date/time format builder
- Advanced notification rules
- Email notification preview
- Two-factor authentication
- Activity log export
- Profile picture upload
- Keyboard shortcuts
- Accessibility improvements

## Support

For issues or questions:
1. Check this documentation
2. Review browser console for errors
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify route configuration
5. Test with different user roles
6. Contact system administrator
