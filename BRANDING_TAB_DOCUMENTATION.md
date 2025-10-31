# Branding & UI Tab - Complete Documentation

## Overview
The enhanced Branding & UI tab provides comprehensive customization options for the Procurement System, allowing superadmins to customize the application's appearance, branding, and visual identity.

## Features

### 1. Application Information
- **Application Name** (Required): Main application title displayed in sidebar and page titles
- **Tagline**: Short slogan or tagline displayed alongside the app name
- **Description**: Brief description of the application (255 character limit with live counter)

### 2. Logo Configuration
#### Upload Features
- **Drag & Drop Support**: Drag logo files directly into the upload zone
- **Click to Upload**: Click the upload area to select files
- **Keyboard Accessible**: Fully keyboard navigable (Tab, Enter, Space)
- **File Types**: PNG, JPG, JPEG, SVG
- **File Size Limit**: 2MB maximum
- **Recommended Dimensions**: 200x50 pixels
- **Live Preview**: See logo preview immediately after selection
- **Remove Option**: Delete logo with one click

#### Positioning Controls
- **Position Options**: Left, Center, Right
- **Size Control**: Adjustable height from 30px to 100px via slider
- **Real-time Preview**: Changes reflect instantly in preview panel

### 3. Color Scheme Customization
Three primary color options with hex input support:

- **Primary Color**: Main brand color (buttons, links, primary elements)
- **Secondary Color**: Supporting color for secondary elements
- **Accent Color**: Highlight color for alerts and special elements

#### Color Picker Features
- Visual color picker with real-time preview
- Hex code input field with validation
- Bidirectional sync between picker and hex input
- WCAG 2.1 AA compliant contrast checking
- Automatic text color adjustment for readability

### 4. Typography Settings
#### Font Family Selection
Includes popular web-safe and Google Fonts:
- System UI (Default)
- Inter
- Roboto
- Open Sans
- Lato
- Montserrat
- Poppins
- Arial
- Helvetica
- Georgia
- Times New Roman

#### Font Size Control
- Range: 12px - 18px
- Recommended: 14-16px for optimal readability
- Real-time preview of text with selected font

### 5. Live Preview Panel
The sticky preview panel shows real-time changes:
- **Application Header**: Logo with name and tagline
- **Typography Sample**: Body text using selected font and size
- **Button Styles**: Primary, secondary, and accent button previews
- **Link Styles**: Hyperlink appearance with primary color
- **Alert Sample**: Alert component with accent color

### 6. Form Management
#### Save Functionality
- **Validation**: All inputs validated before submission
- **Loading State**: Visual feedback during save operation
- **Success Message**: Confirmation upon successful save
- **Error Handling**: Clear error messages with suggestions

#### Reset Functionality
- **Unsaved Changes Detection**: Warns before discarding changes
- **Confirmation Dialog**: Prevents accidental data loss
- **Complete Reset**: Returns all fields to saved values

### 7. State Management
- **Unsaved Changes Badge**: Visible indicator when changes exist
- **Browser Navigation Warning**: Prevents accidental navigation loss
- **Form Dirty Tracking**: Monitors all field modifications

## Accessibility Features (WCAG 2.1 AA Compliant)

### Keyboard Navigation
- All interactive elements are keyboard accessible
- Logical tab order throughout the form
- Visible focus indicators on all focusable elements
- Keyboard shortcuts for common actions

### Screen Reader Support
- Proper ARIA labels on all inputs
- Descriptive field help text
- Error announcements
- Status updates for dynamic content

### Visual Accessibility
- High contrast mode support
- Proper color contrast ratios
- Clear visual hierarchy
- Reduced motion support for animations

### Form Accessibility
- Required fields clearly marked
- Inline validation messages
- Associated labels for all inputs
- Descriptive error messages

## Technical Implementation

### Frontend Technologies
- **JavaScript**: Vanilla JS with ES6+ features
- **CSS**: Modern CSS3 with custom properties
- **Framework**: Bootstrap 5.3.7 for base components
- **Build Tool**: Vite 7.0.4 for asset compilation

### Backend Technologies
- **Framework**: Laravel 12
- **Database**: SQL Server (settings table)
- **File Storage**: Laravel Storage facade
- **Validation**: Laravel validation rules

### Browser Compatibility
- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+

### Performance Optimizations
- Debounced input handlers for smooth typing
- Lazy loading of preview updates
- Optimized image handling
- Minimal DOM manipulation
- CSS animations with GPU acceleration

## File Structure

```
resources/
├── views/
│   └── superadmin/
│       └── tabs/
│           └── branding.blade.php        # Main view template
├── js/
│   └── pages/
│       └── branding.js                   # JavaScript functionality
└── css/
    └── pages/
        └── branding.css                  # Styling

app/
└── Http/
    └── Controllers/
        └── SuperAdminController.php       # Backend logic

public/
└── images/
    └── default-logo.svg                   # Default logo

storage/
└── app/
    └── public/
        └── branding/                      # Uploaded logos
```

## Database Schema

Settings stored in the `settings` table:

| Key | Type | Description |
|-----|------|-------------|
| `app.name` | string | Application name |
| `app.tagline` | string | Application tagline |
| `app.description` | string | Application description |
| `branding.logo_path` | string | Logo file path |
| `branding.logo_position` | string | Logo position (left/center/right) |
| `branding.logo_size` | integer | Logo height in pixels |
| `branding.primary_color` | string | Primary color hex code |
| `branding.secondary_color` | string | Secondary color hex code |
| `branding.accent_color` | string | Accent color hex code |
| `branding.font_family` | string | Selected font family |
| `branding.font_size` | number | Base font size in pixels |

## API Endpoints

### Update Branding
**POST** `/superadmin/branding`

**Parameters:**
- `app_name` (required, string, max:100)
- `app_tagline` (optional, string, max:150)
- `app_description` (optional, string, max:255)
- `logo` (optional, file, max:2MB, types:png,jpg,jpeg,svg)
- `logo_position` (optional, enum:left,center,right)
- `logo_size` (optional, integer, range:30-100)
- `primary_color` (optional, hex color)
- `secondary_color` (optional, hex color)
- `accent_color` (optional, hex color)
- `font_family` (optional, string, max:50)
- `font_size` (optional, number, range:12-18)

**Response:**
```json
{
    "status": "success",
    "message": "Branding settings updated successfully!"
}
```

## Usage Guide

### For Superadmins

1. **Navigate to Branding Tab**
   - Go to Dashboard → Branding & UI tab

2. **Update Application Info**
   - Enter application name (required)
   - Add optional tagline and description
   - Watch preview update in real-time

3. **Upload Logo**
   - Drag logo file to upload area OR click to browse
   - Adjust position and size using controls
   - Remove logo if needed

4. **Customize Colors**
   - Click color pickers to select colors
   - Enter hex codes manually for precision
   - Preview appears instantly

5. **Adjust Typography**
   - Select font family from dropdown
   - Adjust base font size with slider
   - View text sample in preview

6. **Save Changes**
   - Click "Save Branding" button
   - Wait for confirmation message
   - Changes apply system-wide

### Best Practices

1. **Logo Design**
   - Use transparent PNG or SVG for best results
   - Recommended aspect ratio: 4:1 (width:height)
   - Optimize file size before upload
   - Test logo on both light and dark backgrounds

2. **Color Selection**
   - Ensure sufficient contrast for readability
   - Test colors with color blindness simulators
   - Maintain brand consistency
   - Consider cultural color meanings

3. **Typography**
   - Choose legible fonts
   - Maintain comfortable reading size (14-16px)
   - Avoid decorative fonts for body text
   - Test across different devices

4. **Testing**
   - Preview changes before saving
   - Test on different screen sizes
   - Verify accessibility compliance
   - Check cross-browser compatibility

## Troubleshooting

### Logo Not Uploading
- Check file size (must be under 2MB)
- Verify file format (PNG, JPG, JPEG, SVG only)
- Ensure storage directory has write permissions
- Clear browser cache and try again

### Colors Not Updating
- Verify hex code format (#RRGGBB)
- Check browser console for errors
- Ensure JavaScript is enabled
- Try refreshing the page

### Preview Not Showing Changes
- Check browser console for errors
- Verify JavaScript is loading
- Clear browser cache
- Disable browser extensions

### Form Not Saving
- Ensure all required fields are filled
- Check network connection
- Verify CSRF token is valid
- Check server logs for errors

## Security Considerations

- Only superadmin users can access branding settings
- File upload validation prevents malicious files
- CSRF protection on all form submissions
- SQL injection prevention via Laravel ORM
- XSS protection with Blade templating
- File storage outside public root

## Future Enhancements

- [ ] Dark mode theme toggle
- [ ] Multiple color theme presets
- [ ] Custom CSS injection
- [ ] Favicon upload
- [ ] Email template branding
- [ ] PDF report branding
- [ ] Brand style guide export
- [ ] A/B testing for branding
- [ ] Branding version history
- [ ] Multi-language support

## Support

For issues or questions:
1. Check this documentation
2. Review browser console for errors
3. Check Laravel logs: `storage/logs/laravel.log`
4. Verify file permissions
5. Contact system administrator

## Changelog

### Version 1.0.0 (2025-10-26)
- Initial release
- Complete branding customization
- Real-time preview functionality
- Accessibility compliance (WCAG 2.1 AA)
- Cross-browser compatibility
- Comprehensive validation
- State management
- Performance optimizations
