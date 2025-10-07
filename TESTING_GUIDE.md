# Quick Testing Guide - PO Form Updates

## 🚀 Quick Start

1. **Clear browser cache**: `Ctrl + Shift + Delete`
2. **Hard refresh page**: `Ctrl + F5`
3. **Navigate to**: Create PO page

## ✅ What to Test

### Test 1: Number Validation (2 minutes)

**Shipping Field**:
1. Type letters "abc" → Should not accept ❌
2. Type "50" and blur → Should format to "50.00" ✅
3. Type "-25" → Should not accept negative ❌
4. Leave empty → Should show "0.00" ✅

**Discount Field**:
1. Same tests as Shipping
2. Try pasting "abc123" → Should filter to "123.00" ✅

**Result**: Both fields only accept positive numbers with 2 decimals ✅

---

### Test 2: Date Pickers (2 minutes)

**Date Requested**:
1. Click the field → Calendar popup appears ✅
2. Select any date → Date fills in (YYYY-MM-DD) ✅
3. Use month/year dropdowns → Works smoothly ✅

**Delivery Date**:
1. Select date before Date Requested → Should be blocked ❌
2. Select date after Date Requested → Works ✅
3. Check bottom → Should show "Delivery period: X days" ✅

**Result**: Datepickers work perfectly with validation ✅

---

### Test 3: Data Persistence (3 minutes)

**Fresh Form Test**:
1. Click "New PO" or "Create Purchase Order"
2. Form should be completely empty ✅
3. Fill in some fields
4. Click "New PO" again
5. Form should be empty again ✅

**Refresh Test**:
1. Click "New PO"
2. Fill in:
   - Supplier
   - Purpose
   - Dates
   - Shipping: "25.50"
   - Discount: "10.00"
   - Add 2-3 items
3. Press `F5` to refresh
4. **ALL DATA SHOULD BE RESTORED** ✅

**Result**: Data persists on refresh, clears on new creation ✅

---

### Test 4: Profile Photo (1 minute)

**Desktop**:
1. Look at top-right corner
2. Should see circular photo (or initial badge) ✅
3. Photo should be beside name and department ✅

**Mobile** (resize browser or use mobile device):
1. Look at top-right corner
2. Click profile icon/photo
3. Dropdown should appear ✅

**Note**: If you don't have a profile photo set up yet, you'll see a circular badge with your initial - this is normal! ✅

---

## 🐛 Expected Console Messages

Press `F12` and check Console tab:

### Fresh Form:
```
PO Create script loaded
Form element found: [object HTMLFormElement]
jQuery and jQuery UI ready - initializing datepickers
Initializing datepickers for date-from and date-to
Fresh form creation - starting with empty form
```

### Page Refresh:
```
PO Create script loaded
Form element found: [object HTMLFormElement]
jQuery and jQuery UI ready - initializing datepickers
Initializing datepickers for date-from and date-to
Page refresh detected - restoring form data
```

### ❌ NO ERRORS should appear!

---

## 🎯 Quick Feature Summary

| Feature | Status | What to Expect |
|---------|--------|----------------|
| Shipping field | ✅ | Numbers only, 2 decimals |
| Discount field | ✅ | Numbers only, 2 decimals |
| Vatable Sales | ✅ | Read-only, calculated |
| 12% VAT | ✅ | Read-only, calculated |
| Date Requested | ✅ | Datepicker works |
| Delivery Date | ✅ | Datepicker works |
| Date validation | ✅ | Delivery ≥ Request |
| Delivery period | ✅ | Shows day count |
| Fresh form | ✅ | Empty on "New PO" |
| Data persistence | ✅ | Saved on refresh |
| Profile photo | ✅ | Shows in header |

---

## 🔧 If Something Doesn't Work

### Numbers not validating?
```bash
# Run this and refresh browser
npm run build
```

### Datepickers not appearing?
1. Check console for errors (F12)
2. Look for "jQuery and jQuery UI ready" message
3. Hard refresh: `Ctrl + Shift + R`

### Data not persisting?
1. Open Console (F12)
2. Go to Application tab → Local Storage
3. Look for `po_create_draft_v1`
4. If not there, check console for errors

### Profile photo not showing?
1. This is normal if profile photo isn't set up
2. You should see a circular badge with your initial
3. To add photo: Set `$auth['profile_photo']` in your session

---

## 📝 5-Minute Full Test Script

```
1. Clear cache (Ctrl+Shift+Delete) ✅
2. Go to Create PO page ✅
3. Try typing letters in Shipping → Blocked ✅
4. Type "50" in Shipping → Becomes "50.00" ✅
5. Click Date Requested → Datepicker appears ✅
6. Select a date → Date fills in ✅
7. Click Delivery Date → Datepicker appears ✅
8. Select date after Request Date → Works ✅
9. See "Delivery period: X days" ✅
10. Fill in all fields including items ✅
11. Press F5 to refresh ✅
12. All data restored ✅
13. Click "New PO" button ✅
14. Form is empty ✅
15. Look at header → See profile photo/initial ✅

ALL TESTS PASSED! 🎉
```

---

## 📱 Mobile Testing

1. Resize browser to mobile size
2. Click hamburger menu → Sidebar appears ✅
3. Click profile icon → Dropdown appears ✅
4. Test form fields → All work ✅
5. Datepickers adapt to mobile ✅

---

## ✨ What's New

**Before This Update**:
- ❌ Could type letters in number fields
- ❌ Could enter negative numbers
- ❌ Datepickers not working
- ❌ Data lost on refresh
- ❌ No profile photo display

**After This Update**:
- ✅ Number fields validated properly
- ✅ Only positive numbers allowed
- ✅ Datepickers working perfectly
- ✅ Data persists on refresh
- ✅ Profile photo in header

---

## 🎉 You're All Set!

The form is now production-ready with:
- Robust number validation
- Working datepickers
- Smart data persistence
- Professional UI with profile photos

**Go ahead and test it out!** 🚀
