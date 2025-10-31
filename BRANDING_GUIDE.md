# Branding System Guide

## Overview

The comprehensive branding system allows superadmins to customize the application's appearance including logos, colors, typography, and company information. All branding changes are automatically applied system-wide including web views, mobile interfaces, print views, and exports.

## Features

### Application Information
- **Application Name**: Customizable name displayed in sidebar, page titles, and login
- **Tagline**: Short slogan/description displayed under the app name
- **Description**: Brief description of the application

### Visual Branding
- **Logo Upload**: Support for PNG, JPG, SVG (max 2MB)
- **Logo Positioning**: Left, center, or right alignment
- **Logo Size**: Adjustable height (30-100px)
- **Primary Color**: Main brand color for buttons, links, and active elements
- **Secondary Color**: Supporting color for secondary UI elements
- **Accent Color**: Highlight color for success states and important elements

### Typography
- **Font Family**: Choose from 11 font options including system fonts and web fonts
- **Font Size**: Adjustable base font size (12-18px)

## How to Configure Branding

### For Superadmins

1. **Access Branding Settings**
   - Login as superadmin
   - Navigate to Dashboard
   - Click on the "Branding & UI" tab

2. **Upload Logo**
   - Click the upload area or drag and drop
   - Supported formats: PNG, JPG, SVG
   - Maximum file size: 2MB
   - Recommended dimensions: 200x50px

3. **Configure Colors**
   - Use color pickers or enter hex values
   - Changes preview in real-time
   - Primary color affects buttons and links
   - Accent color affects success states

4. **Set Typography**
   - Select font family from dropdown
   - Adjust base font size with slider
   - Preview updates immediately

5. **Save Changes**
   - Click "Save Branding" button
   - Changes apply system-wide immediately
   - All caches are automatically cleared

## Branding Application Scope

### Web Application
- Sidebar logo and app name
- Navigation links and active states
- Buttons (primary, secondary, accent)
- Form controls and focus states
- Alerts and notifications
- Status badges
- Tables and data displays
- Modals and popups
- Dropdown menus

### Login Page
- Company logo display
- App name and tagline
- Branded color scheme
- Sign-in button styling

### Print Views
- Purchase order PDFs
- Report headers
- Company logo on documents
- Branded typography

### Mobile Interface
- Responsive logo sizing
- Touch-friendly button colors
- Mobile navigation branding

## Developer Guide

### Using BrandingService

```php
// Get branding service instance
$branding = app(\App\Services\BrandingService::class);

// Get all branding settings
$settings = $branding->getAll();

// Get specific values
$logo = $branding->getLogoPath();
$appName = $branding->getAppName();
$primaryColor = $branding->getPrimaryColor();

// Get print data
$printData = $branding->getPrintData();
```

### Helper Functions

```php
// Get branding service or specific value
$service = branding();
$logo = branding('branding.logo_path');

// Quick access helpers
$name = app_name();
$logo = app_logo();
$color = brand_color('primary'); // or 'secondary', 'accent'
```

### Blade Directives

```blade
{{-- Display brand elements --}}
@brandLogo
@brandName
@brandColor
```

### In Controllers

```php
use App\Services\BrandingService;

public function index(BrandingService $branding)
{
    $data = [
        'logo' => $branding->getLogoPath(),
        'appName' => $branding->getAppName(),
        'colors' => $branding->getColors(),
    ];
    
    return view('dashboard', $data);
}
```

### In Blade Views

```blade
{{-- Branding is automatically available via view composer --}}
<img src="{{ $brandingService->getLogoPath() }}" alt="Logo">
<h1>{{ $brandingService->getAppName() }}</h1>

{{-- Or use settings array --}}
<div style="color: {{ $brandSettings['branding.primary_color'] }}">
    Branded content
</div>
```

### Dynamic CSS

The system automatically generates a dynamic CSS file at `/branding/dynamic.css` that applies branding colors and typography throughout the application. This is automatically included in the main layout.

Key CSS variables:
- `--brand-primary`: Primary color
- `--brand-primary-dark`: Darker shade of primary
- `--brand-primary-light`: Lighter shade of primary
- `--brand-secondary`: Secondary color
- `--brand-accent`: Accent color
- `--brand-font-family`: Font family
- `--brand-font-size`: Base font size

## Caching

Branding settings are cached for 1 hour to improve performance. The cache is automatically cleared when:
- Branding settings are updated via superadmin panel
- `php artisan cache:clear` is run
- Manual cache clear: `app(BrandingService::class)->clearCache()`

## Default Values

If no branding is configured, the system uses these defaults:
- **App Name**: Procurement System
- **Tagline**: Management System
- **Primary Color**: #0d6efd (Bootstrap blue)
- **Secondary Color**: #6c757d (Bootstrap gray)
- **Accent Color**: #198754 (Bootstrap green)
- **Font Family**: system-ui
- **Font Size**: 14px

## Troubleshooting

### Logo not appearing
- Check file format (PNG, JPG, SVG only)
- Verify file size is under 2MB
- Ensure storage directory has write permissions
- Check if logo path is set in settings

### Colors not applying
- Clear browser cache (Ctrl+F5)
- Verify hex color format (#RRGGBB)
- Check if dynamic CSS route is accessible
- Clear Laravel caches: `php artisan optimize:clear`

### Font not changing
- Verify font family is in the supported list
- Check if web fonts are loading correctly
- Clear compiled views: `php artisan view:clear`
- Check browser font rendering

## Best Practices

1. **Logo Design**
   - Use transparent backgrounds (PNG with alpha channel)
   - Keep dimensions around 200x50px for optimal display
   - Ensure logo is readable at small sizes
   - Use SVG for scalability when possible

2. **Color Selection**
   - Maintain sufficient contrast ratios (WCAG 2.1 AA)
   - Test colors in both light and dark contexts
   - Consider color-blind accessibility
   - Use accent color sparingly for emphasis

3. **Typography**
   - Choose readable fonts (14-16px recommended)
   - Avoid decorative fonts for body text
   - Ensure font loads quickly
   - Test on multiple devices

4. **Testing**
   - Test on multiple browsers
   - Verify mobile responsiveness
   - Check print view appearance
   - Test with different user roles

## Security Notes

- Only superadmins can modify branding settings
- Logo uploads are validated for file type and size
- Uploaded files are stored in isolated directory
- Old logos are automatically deleted on update
- Settings are protected by CSRF tokens

## Performance Considerations

- Branding settings are cached for 1 hour
- Dynamic CSS is cached by browser
- Logo files should be optimized before upload
- SVG logos are recommended for better performance
- Font files use system fonts when possible

## Support

For branding issues or questions:
1. Check this guide first
2. Verify settings in superadmin panel
3. Clear all caches
4. Check Laravel logs for errors
5. Contact system administrator
