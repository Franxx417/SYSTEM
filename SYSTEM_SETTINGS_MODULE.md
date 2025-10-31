# System Settings Module Documentation

## Overview
The System Settings Module provides a comprehensive interface for administrators to configure and manage all application parameters. It features organized sections, secure storage, real-time validation, and persistent settings across sessions.

## Features Implemented

### 🗂️ **Organized Settings Categories**

#### 1. User Management
- **Default Role**: Default role assigned to new users
- **Email Verification**: Require email verification for new accounts
- **Password Requirements**: Minimum length and complexity rules
- **Login Security**: Maximum attempts and lockout duration

#### 2. Security Settings
- **Session Management**: Timeout duration and security policies
- **HTTPS Enforcement**: Force secure connections
- **Two-Factor Authentication**: Enable/disable 2FA
- **IP Restrictions**: Whitelist allowed IP addresses
- **Security Headers**: Enable security headers

#### 3. Notification Settings
- **Email Notifications**: Enable/disable email system
- **Admin Contacts**: Administrator email addresses
- **Notification Frequency**: Immediate, daily, or weekly
- **Alert Types**: Security alerts and maintenance notifications

#### 4. Performance Settings
- **Caching**: Enable/disable application caching
- **Cache Duration**: Default cache expiration time
- **File Upload Limits**: Maximum file size restrictions
- **Pagination**: Default items per page
- **Logging Level**: Application log verbosity

#### 5. Application Settings
- **App Information**: Name, version, and branding
- **Maintenance Mode**: Enable/disable maintenance mode
- **Timezone**: Application timezone settings
- **Date Formats**: Default date and time formats

### 🔒 **Security Features**

#### Encrypted Storage
- Sensitive settings are automatically encrypted
- Uses Laravel's built-in encryption
- Secure key management

#### Access Control
- Superadmin-only access
- Activity logging for all changes
- Audit trail with user tracking

#### Validation
- Real-time setting validation
- Type checking (string, integer, boolean, JSON)
- Custom validation rules per setting

### 💾 **Data Management**

#### Persistent Storage
- Database-backed settings storage
- Automatic caching for performance
- Cache invalidation on updates

#### Backup & Restore
- **Export Settings**: Download complete configuration as JSON
- **Import Settings**: Upload and restore from backup
- **Reset to Defaults**: Category-specific or full reset

#### Version Control
- Track who made changes and when
- Setting change history
- Rollback capabilities

## Database Structure

### `system_settings` Table
```sql
- id (UUID, Primary Key)
- category (String, 50 chars) - Setting category
- key (String, 100 chars) - Setting identifier
- value (Text) - Setting value (encrypted if sensitive)
- type (String, 20 chars) - Data type (string, integer, boolean, json)
- description (Text) - Human-readable description
- validation_rules (JSON) - Laravel validation rules
- is_encrypted (Boolean) - Whether value is encrypted
- is_public (Boolean) - Can be accessed by non-admin users
- sort_order (Integer) - Display order
- updated_by (UUID) - User who last updated
- created_at, updated_at (DateTime)
```

### Indexes
- Unique: `category + key`
- Index: `category`, `is_public`, `sort_order`

## API Endpoints

### Settings Management
```
GET    /api/system-settings/category/{category}  - Get settings by category
POST   /api/system-settings/batch               - Update multiple settings
POST   /api/system-settings/single              - Update single setting
POST   /api/system-settings/reset               - Reset to defaults
GET    /api/system-settings/export              - Export settings
POST   /api/system-settings/import              - Import settings
POST   /api/system-settings/test                - Test setting value
```

### Cache Management
```
POST   /api/system-settings/clear-cache         - Clear settings cache
```

## Usage Examples

### Accessing Settings in Code
```php
// Get a setting value
$sessionTimeout = SystemSetting::get('session_timeout', 120, 'security');

// Set a setting value
SystemSetting::set('app_name', 'My Procurement System', 'application');

// Get all settings for a category
$securitySettings = SystemSetting::getByCategory('security');

// Get all settings
$allSettings = SystemSetting::getAllSettings();
```

### Frontend JavaScript
```javascript
// Load category settings
loadCategorySettings('security');

// Save settings
saveSettings('security', formElement);

// Reset category to defaults
resetCategory('security');

// Export/Import
exportSettings();
importSettings();
```

## Installation & Setup

### 1. Run Migrations
```bash
php artisan migrate
```

### 2. Seed Default Settings
```bash
php artisan settings:seed
```

### 3. Access Settings Interface
Navigate to `/settings/system` (superadmin only)

## Default Settings

### User Management
- `default_role`: 'requestor'
- `require_email_verification`: true
- `password_min_length`: 8
- `password_require_special`: true
- `max_login_attempts`: 5
- `lockout_duration`: 15 (minutes)

### Security
- `session_timeout`: 120 (minutes)
- `force_https`: false
- `two_factor_enabled`: false
- `ip_whitelist`: '' (empty)
- `security_headers`: true

### Notifications
- `email_enabled`: true
- `admin_email`: 'admin@example.com'
- `notification_frequency`: 'immediate'
- `security_alerts`: true
- `system_maintenance`: true

### Performance
- `cache_enabled`: true
- `cache_duration`: 3600 (seconds)
- `max_file_size`: 10 (MB)
- `pagination_limit`: 50
- `log_level`: 'info'

### Application
- `app_name`: 'Procurement System'
- `app_version`: '1.0.0'
- `maintenance_mode`: false
- `timezone`: 'UTC'
- `date_format`: 'Y-m-d'

## Security Considerations

### Encryption
- Passwords and API keys are automatically encrypted
- Encryption keys are managed by Laravel
- No sensitive data stored in plain text

### Access Control
- Only superadmin users can access settings
- All changes are logged with user attribution
- IP address tracking for security audits

### Validation
- Input validation prevents malicious data
- Type checking ensures data integrity
- Custom validation rules per setting type

## Performance Optimization

### Caching Strategy
- Settings cached for 1 hour by default
- Cache invalidation on updates
- Efficient cache key management

### Database Optimization
- Indexed columns for fast queries
- Minimal database calls with caching
- Batch operations for multiple updates

## Troubleshooting

### Common Issues

**Settings not loading**
- Check database connection
- Verify migrations are run
- Ensure cache is not corrupted

**Permission denied**
- Verify user has superadmin role
- Check authentication middleware
- Review access control logs

**Cache issues**
- Clear application cache: `php artisan cache:clear`
- Clear settings cache via admin interface
- Check cache driver configuration

### Debugging
- Enable debug mode in `.env`
- Check Laravel logs in `storage/logs/`
- Use browser developer tools for frontend issues

## Future Enhancements

### Planned Features
1. **Setting Templates**: Predefined configuration sets
2. **Environment Sync**: Sync settings across environments
3. **API Keys Management**: Secure API key storage and rotation
4. **Setting Dependencies**: Conditional settings based on other values
5. **Bulk Operations**: Mass update capabilities
6. **Setting History**: Complete change tracking and rollback
7. **Validation Presets**: Common validation rule templates
8. **Setting Groups**: Logical grouping within categories
9. **Real-time Sync**: Live updates across admin sessions
10. **Mobile Interface**: Responsive mobile administration

### Integration Opportunities
- **Monitoring**: Integration with system monitoring tools
- **Backup Systems**: Automated backup to external storage
- **Configuration Management**: Integration with DevOps tools
- **Compliance**: Audit trail for regulatory compliance

## Conclusion

The System Settings Module provides a robust, secure, and user-friendly interface for managing all application parameters. With its organized structure, comprehensive security features, and powerful management capabilities, it ensures administrators can efficiently configure and maintain the procurement system while maintaining security and data integrity.
