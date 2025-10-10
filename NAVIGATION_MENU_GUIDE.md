# 📍 Navigation Menu - Complete Guide

## 🎯 **Menu Items Added**

### **For Admin Role** (`/admin/*`)
Main Navigation:
1. Admin Dashboard
2. Madrasah
3. Tingkat
4. Jenis Soal
5. Jenis Ujian
6. **📋 Berita Acara** ← NEW (View Only)
7. Log Aktifitas

**Access:**
- ✅ Can view all Berita Acara
- ✅ Can export PDF
- ✅ Can print student attendance lists
- ❌ Cannot create or edit Berita Acara

---

### **For Super Admin Role** (`/super/*`)
Main Navigation:
1. Super Dashboard
2. Data Madrasah
3. Tingkat
4. Jenis Ujian
5. **📋 Berita Acara** ← NEW (View Only)
6. Log Aktifitas

**Access:**
- ✅ Can view all Berita Acara (all schools)
- ✅ Can export PDF
- ✅ Can print student attendance lists
- ❌ Cannot create or edit Berita Acara

---

### **For Guru/Teacher Role** (`/guru/*`)
Main Navigation:
1. Guru Dashboard
2. **👥 Penempatan Siswa** ← NEW (Room Assignment)

**Access:**
- ✅ Can assign students to rooms
- ✅ Can use auto-assign feature
- ✅ Can view room summaries
- ✅ Can remove students from rooms

**Note:** Teachers help with room assignment but don't manage Berita Acara.

---

### **For Kepala Sekolah/Headmaster Role** (`/kepala/*`)
Main Navigation:
1. Kepala Dashboard
2. Data Madrasah
3. Data Siswa
4. Data Operator
5. Ujian Bersama
6. **👥 Penempatan Siswa** ← NEW (Room Assignment)
7. **📋 Berita Acara** ← NEW (Full Access)

**Access:**

**Room Assignment:**
- ✅ Can assign students to rooms
- ✅ Can use auto-assign feature
- ✅ Can view room summaries
- ✅ Can remove students from rooms

**Berita Acara:**
- ✅ Can create new Berita Acara
- ✅ Can edit (draft & finalized only)
- ✅ Can view all Berita Acara for their school
- ✅ Can approve Berita Acara
- ✅ Can finalize and archive
- ✅ Can export PDF
- ✅ Can print student attendance lists
- ✅ Can delete (draft only)

---

## 🔗 **Direct URLs**

### **Admin:**
```
/admin/berita-acara                    # Berita Acara List
/admin/berita-acara/{id}               # View Detail
/admin/berita-acara/{id}/pdf           # Export PDF
/admin/berita-acara/{id}/student-list  # Print Attendance List
```

### **Super:**
```
/super/berita-acara                    # Berita Acara List
/super/berita-acara/{id}               # View Detail
/super/berita-acara/{id}/pdf           # Export PDF
/super/berita-acara/{id}/student-list  # Print Attendance List
```

### **Guru:**
```
/guru/room-assignment                  # Room Assignment Dashboard
/guru/room-assignment/assign           # Manual Assign (POST)
/guru/room-assignment/auto-assign      # Auto Distribute (POST)
/guru/room-assignment/remove           # Remove Student (DELETE)
```

### **Kepala:**
```
# Room Assignment
/kepala/room-assignment                # Room Assignment Dashboard
/kepala/room-assignment/assign         # Manual Assign (POST)
/kepala/room-assignment/auto-assign    # Auto Distribute (POST)
/kepala/room-assignment/remove         # Remove Student (DELETE)

# Berita Acara
/kepala/berita-acara                   # Berita Acara List
/kepala/berita-acara/create            # Create Form
/kepala/berita-acara/{id}              # View Detail
/kepala/berita-acara/{id}/edit         # Edit Form
/kepala/berita-acara/{id}/pdf          # Export PDF
/kepala/berita-acara/{id}/student-list # Print Attendance List
```

---

## 📱 **Mobile Navigation**

The responsive (mobile) menu also includes:

**Admin (Mobile):**
- Dashboard
- **Berita Acara** ← NEW

**Guru (Mobile):**
- Dashboard
- **Penempatan Siswa** ← NEW

**Kepala (Mobile):**
- Dashboard
- **Penempatan Siswa** ← NEW
- **Berita Acara** ← NEW

**Super (Mobile):**
- Dashboard
- **Berita Acara** ← NEW

---

## 🎨 **Menu Highlighting**

The menu items will automatically highlight when you're on the related pages:

**Berita Acara menu highlights when on:**
- `/kepala/berita-acara` (list)
- `/kepala/berita-acara/create` (create form)
- `/kepala/berita-acara/{id}` (detail view)
- `/kepala/berita-acara/{id}/edit` (edit form)

**Penempatan Siswa menu highlights when on:**
- `/kepala/room-assignment` (room assignment page)
- `/guru/room-assignment` (room assignment page)

This is achieved using:
```php
:active="request()->routeIs('kepala.berita-acara.*')"
:active="request()->routeIs('kepala.room-assignment.*')"
```

---

## 🔍 **Menu Item Icons (Emojis)**

For quick visual recognition:
- 📋 **Berita Acara** - Official document/report
- 👥 **Penempatan Siswa** - Student assignment to rooms

---

## 🚦 **Workflow Navigation Path**

### **Typical User Journey for Kepala Sekolah:**

**Before Exam (Setup):**
1. Click **"Penempatan Siswa"** menu
2. Assign students to rooms
3. Click **"Berita Acara"** menu
4. Print student attendance lists

**After Exam (Documentation):**
1. Click **"Berita Acara"** menu
2. Click "Buat Berita Acara Baru"
3. Fill in exam details
4. Save → Finalize → Approve
5. Export PDF for archive

### **Typical User Journey for Guru:**

**Before Exam:**
1. Click **"Penempatan Siswa"** menu
2. Help assign students to rooms
3. Done

### **Typical User Journey for Admin/Super:**

**After Exam:**
1. Click **"Berita Acara"** menu
2. View all school reports
3. Export PDFs if needed
4. Monitor compliance across schools

---

## ⚙️ **Technical Implementation**

### **Navigation Component:**
File: `resources/views/layouts/navigation.blade.php`

**Main Navigation (Desktop):**
```php
<x-nav-link :href="route('kepala.berita-acara.index')" 
            :active="request()->routeIs('kepala.berita-acara.*')">
    {{ __('Berita Acara') }}
</x-nav-link>
```

**Responsive Navigation (Mobile):**
```php
<x-responsive-nav-link :href="route('kepala.berita-acara.index')" 
                       :active="request()->routeIs('kepala.berita-acara.*')">
    {{ __('Berita Acara') }}
</x-responsive-nav-link>
```

### **Active State Detection:**
Uses Laravel's `request()->routeIs()` helper with wildcard:
- `'kepala.berita-acara.*'` matches ALL routes starting with `kepala.berita-acara`
- Including: index, create, show, edit, etc.

---

## 📖 **Localization**

All menu labels use Laravel's `__()` helper for translation support:

```php
{{ __('Berita Acara') }}        # Can be translated
{{ __('Penempatan Siswa') }}    # Can be translated
```

To add translations, create:
```
resources/lang/id/messages.php
resources/lang/en/messages.php
```

---

## ✅ **Verification Checklist**

After implementation, verify:

**For Kepala:**
- [ ] Can see "Penempatan Siswa" in top menu
- [ ] Can see "Berita Acara" in top menu
- [ ] Both menus highlight when active
- [ ] Mobile menu shows both items
- [ ] Clicking menu navigates correctly

**For Guru:**
- [ ] Can see "Penempatan Siswa" in top menu
- [ ] Cannot see "Berita Acara" in menu
- [ ] Mobile menu shows "Penempatan Siswa"

**For Admin:**
- [ ] Can see "Berita Acara" in top menu (after Jenis Ujian)
- [ ] Cannot see "Penempatan Siswa" in menu
- [ ] Mobile menu shows "Berita Acara"

**For Super:**
- [ ] Can see "Berita Acara" in top menu
- [ ] Cannot see "Penempatan Siswa" in menu
- [ ] Mobile menu shows "Berita Acara"

---

## 🎯 **Summary Table**

| Role | Penempatan Siswa | Berita Acara | Access Level |
|------|------------------|--------------|--------------|
| **Admin** | ❌ No | ✅ Yes | View Only |
| **Super** | ❌ No | ✅ Yes | View Only |
| **Kepala** | ✅ Yes | ✅ Yes | Full Access |
| **Guru** | ✅ Yes | ❌ No | Assign Only |
| **Siswa** | ❌ No | ❌ No | No Access |

---

## 📝 **Notes**

1. **Menu Position:** Berita Acara appears after primary data management menus
2. **Consistent Naming:** Uses Indonesian terms matching existing menu items
3. **Mobile Friendly:** All new menus work on mobile devices
4. **Active States:** Menus highlight appropriately when on related pages
5. **Role-based:** Only shows to authorized roles

---

**Updated:** October 10, 2025  
**File Modified:** `resources/views/layouts/navigation.blade.php`  
**Status:** ✅ Complete
