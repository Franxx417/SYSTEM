# Branding & UI Tab - Testing Checklist

## Pre-Testing Setup
- [ ] Run `npm run build` or `npm run dev` to compile assets
- [ ] Clear application cache: `php artisan optimize:clear`
- [ ] Verify storage link exists: `php artisan storage:link`
- [ ] Login as superadmin user
- [ ] Navigate to Dashboard → Branding & UI tab

## Functional Tests

### Application Information
- [ ] Application Name input accepts text (required field)
- [ ] Tagline input accepts optional text (max 150 chars)
- [ ] Description textarea accepts text (max 255 chars)
- [ ] Character counter updates in real-time for description
- [ ] Preview updates when app name is changed
- [ ] Preview updates when tagline is changed

### Logo Upload
- [ ] Click upload area opens file dialog
- [ ] Drag and drop file into upload area works
- [ ] Only PNG, JPG, JPEG, SVG files are accepted
- [ ] File size validation works (2MB limit)
- [ ] Logo preview appears after selection
- [ ] Current logo displays if one exists
- [ ] Remove logo button works
- [ ] Preview panel shows uploaded logo in real-time

### Logo Positioning
- [ ] Position dropdown has Left, Center, Right options
- [ ] Preview updates when position is changed
- [ ] Size slider ranges from 30px to 100px
- [ ] Size value updates in real-time
- [ ] Preview logo size adjusts with slider

### Color Scheme
- [ ] Primary color picker opens and works
- [ ] Primary color hex input syncs with picker
- [ ] Secondary color picker opens and works
- [ ] Secondary color hex input syncs with picker
- [ ] Accent color picker opens and works
- [ ] Accent color hex input syncs with picker
- [ ] Invalid hex codes are rejected
- [ ] Preview buttons update with selected colors
- [ ] Preview link color updates with primary color
- [ ] Preview alert updates with accent color
- [ ] Text color auto-adjusts for contrast

### Typography
- [ ] Font family dropdown lists all available fonts
- [ ] Preview text updates when font is changed
- [ ] Google Fonts load when non-system fonts selected
- [ ] Font size slider ranges from 12px to 18px
- [ ] Font size value updates in real-time
- [ ] Preview text size adjusts with slider

### Live Preview
- [ ] Preview panel is sticky on desktop
- [ ] All preview elements update in real-time
- [ ] Preview reflects current form values
- [ ] Preview shows default values on page load

### Form State Management
- [ ] Unsaved changes badge appears when form is modified
- [ ] Browser warns before navigation with unsaved changes
- [ ] Reset button asks for confirmation if changes exist
- [ ] Reset button reloads page to discard changes

### Save Functionality
- [ ] Save button is accessible
- [ ] Loading state shows during save
- [ ] Success message appears after save
- [ ] Error messages display for validation failures
- [ ] Form validation works correctly
- [ ] Required fields are enforced
- [ ] Hex color format is validated
- [ ] File type and size are validated

## Accessibility Tests

### Keyboard Navigation
- [ ] All form inputs are keyboard accessible
- [ ] Tab order is logical
- [ ] Enter/Space activates logo upload area
- [ ] Escape closes color pickers (if applicable)
- [ ] Focus indicators are visible

### Screen Reader
- [ ] All inputs have proper labels
- [ ] ARIA attributes are present
- [ ] Form validation errors are announced
- [ ] Help text is associated with inputs
- [ ] Success/error messages are announced

### Visual Accessibility
- [ ] Sufficient color contrast (WCAG 2.1 AA)
- [ ] Focus indicators meet contrast requirements
- [ ] Error states are visible without color alone
- [ ] Text is readable at all supported sizes

## Responsive Design Tests

### Desktop (1920x1080)
- [ ] Layout displays correctly
- [ ] Preview panel is sticky
- [ ] All elements are properly spaced
- [ ] Form fields are appropriately sized

### Tablet (768x1024)
- [ ] Layout adapts appropriately
- [ ] Preview panel behavior is correct
- [ ] Touch targets are adequate size
- [ ] Text remains readable

### Mobile (375x667)
- [ ] Layout stacks vertically
- [ ] Preview panel is not sticky
- [ ] All features remain accessible
- [ ] Touch interactions work properly

## Browser Compatibility Tests

### Chrome/Edge
- [ ] All features work correctly
- [ ] Drag and drop functions properly
- [ ] Color pickers display correctly
- [ ] Performance is acceptable

### Firefox
- [ ] All features work correctly
- [ ] File upload works
- [ ] Color pickers display correctly
- [ ] CSS renders properly

### Safari
- [ ] All features work correctly
- [ ] File upload works
- [ ] Color pickers display correctly
- [ ] Webkit-specific features work

## Performance Tests
- [ ] Page loads in under 2 seconds
- [ ] Preview updates are smooth (no lag)
- [ ] Form submission completes in under 3 seconds
- [ ] No console errors or warnings
- [ ] No memory leaks during extended use

## Security Tests
- [ ] Only superadmin can access branding tab
- [ ] CSRF protection is active
- [ ] File upload security works
- [ ] SQL injection prevention works
- [ ] XSS protection is active

## Edge Cases

### Data Validation
- [ ] Empty required fields show errors
- [ ] Maximum length limits are enforced
- [ ] Invalid hex colors are rejected
- [ ] Oversized files are rejected
- [ ] Invalid file types are rejected

### State Management
- [ ] Multiple rapid changes are handled
- [ ] Form state persists during validation errors
- [ ] Preview doesn't break with invalid data
- [ ] Reset works from any form state

### Error Handling
- [ ] Network errors are caught and displayed
- [ ] Server errors show user-friendly messages
- [ ] File upload failures are handled gracefully
- [ ] Invalid data submissions show specific errors

## Integration Tests
- [ ] Saved settings persist across sessions
- [ ] Settings apply system-wide
- [ ] Logo appears in application layout
- [ ] Colors apply to UI elements
- [ ] Font changes apply globally
- [ ] View cache clears after save

## Final Verification
- [ ] All features work as documented
- [ ] No JavaScript errors in console
- [ ] No PHP errors in logs
- [ ] Database updates correctly
- [ ] Files stored in correct location
- [ ] Application remains stable

## Notes
- Record any issues found: ___________________________________
- Browser tested: ___________________________________________
- Date tested: _____________________________________________
- Tester name: _____________________________________________

## Status
- [ ] All tests passed
- [ ] Minor issues found (documented)
- [ ] Major issues found (requires fix)
- [ ] Ready for production deployment
