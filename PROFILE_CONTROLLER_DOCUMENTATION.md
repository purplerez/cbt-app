# ProfileController Documentation

## Overview
ProfileController mengelola semua operasi terkait profil pengguna, termasuk viewing, updating profil, mengubah password, dan menghapus akun.

---

## Methods

### 1. `show(Request $request): View`
**Purpose:** Menampilkan halaman detail profil pengguna (read-only view)

**Route:** 
- Verb: `GET`
- Path: `/profile/show`
- Name: `profile.show`

**Parameters:**
- `$request` - HTTP Request object dengan user yang authenticated

**Returns:**
- View: `profile.show` dengan data user

**Usage:**
```blade
<a href="{{ route('profile.show') }}">Lihat Profil</a>
```

**Features:**
- Menampilkan informasi personal (nama, email, phone, address)
- Menampilkan informasi akun (ID, created_at, updated_at, last_login_at)
- Menampilkan roles/permissions pengguna
- Quick actions untuk edit profile dan change password

---

### 2. `edit(Request $request): View`
**Purpose:** Menampilkan form untuk mengedit profil pengguna

**Route:**
- Verb: `GET`
- Path: `/profile`
- Name: `profile.edit`

**Parameters:**
- `$request` - HTTP Request object dengan user yang authenticated

**Returns:**
- View: `profile.edit` dengan form untuk:
  - Update profile information (name, email)
  - Update password
  - Delete account

**Usage:**
```blade
<a href="{{ route('profile.edit') }}">Edit Profil</a>
```

---

### 3. `update(ProfileUpdateRequest $request): RedirectResponse`
**Purpose:** Memperbarui informasi profil pengguna (nama dan email)

**Route:**
- Verb: `PATCH`
- Path: `/profile`
- Name: `profile.update`

**Request Validation:**
- `name` - required, string, max:255
- `email` - required, email, unique (ignore current user), lowercase

**Parameters:**
- `$request` - ProfileUpdateRequest dengan data yang sudah validated

**Returns:**
- RedirectResponse dengan flash message `status: 'profile-updated'`
- Jika ada error, redirect dengan flash message `error`

**Features:**
- Validasi input dengan ProfileUpdateRequest
- Mendeteksi perubahan email dan reset email verification
- Log activity ke ActivityLog
- Error handling dengan try-catch

**Form Action:**
```blade
<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')
    
    <input type="text" name="name" value="{{ old('name', $user->name) }}" required>
    <input type="email" name="email" value="{{ old('email', $user->email) }}" required>
    
    <button type="submit">Save</button>
</form>
```

---

### 4. `updatePassword(Request $request): RedirectResponse`
**Purpose:** Memperbarui password pengguna

**Route:**
- Verb: `PUT`
- Path: `/profile/password`
- Name: `password.update`

**Request Validation:**
- `current_password` - required, must be current password
- `password` - required, must meet password rules, confirmed
- `password_confirmation` - required, must match password

**Parameters:**
- `$request` - HTTP Request dengan data password yang sudah validated

**Returns:**
- RedirectResponse dengan flash message `status: 'password-updated'`
- Jika ada error, redirect dengan flash message `error`

**Features:**
- Validasi password current password dengan 'current_password' rule
- Hash password baru menggunakan bcrypt
- Log activity ke ActivityLog
- Error handling dengan try-catch

**Form Action:**
```blade
<form method="post" action="{{ route('password.update') }}">
    @csrf
    @method('put')
    
    <input type="password" name="current_password" required>
    <input type="password" name="password" required>
    <input type="password" name="password_confirmation" required>
    
    <button type="submit">Update Password</button>
</form>
```

---

### 5. `destroy(Request $request): RedirectResponse`
**Purpose:** Menghapus akun pengguna secara permanen

**Route:**
- Verb: `DELETE`
- Path: `/profile`
- Name: `profile.destroy`

**Request Validation:**
- `password` - required, must be current password

**Parameters:**
- `$request` - HTTP Request dengan password confirmation

**Returns:**
- RedirectResponse ke home page (`/`) dengan flash message `status: 'account-deleted'`
- Logout user dan invalidate session

**Features:**
- Require password confirmation untuk security
- Log activity ke ActivityLog sebelum deletion
- Logout pengguna
- Delete akun dari database
- Invalidate dan regenerate session token
- Error handling dengan try-catch

**Form Action:**
```blade
<form method="post" action="{{ route('profile.destroy') }}">
    @csrf
    @method('delete')
    
    <input type="password" name="password" placeholder="Current Password" required>
    
    <button type="submit">Delete Account</button>
</form>
```

---

## Activity Logging

Setiap action (update profile, change password, delete account) akan dicatat di ActivityLog dengan format:

```
"{username} (ID: {id}) Berhasil Memperbaharui Profil"
"{username} (ID: {id}) Berhasil Mengubah Password"
"{username} (ID: {id}) Berhasil Menghapus Profil"
```

---

## Error Handling

Semua methods menggunakan try-catch untuk error handling:
- Jika ada exception, akan log ke `storage/logs/laravel.log`
- Return redirect dengan flash message error
- User akan diberi feedback yang jelas

---

## Flash Messages

| Action | Success Message | Error Message |
|--------|-----------------|---------------|
| Update Profile | `status: 'profile-updated'` | `error: 'Gagal memperbarui profil...'` |
| Update Password | `status: 'password-updated'` | `error: 'Gagal mengubah password...'` |
| Delete Account | `status: 'account-deleted'` | `error: 'Gagal menghapus akun...'` |

---

## Related Files

- **Controller:** `app/Http/Controllers/ProfileController.php`
- **Request:** `app/Http/Requests/ProfileUpdateRequest.php`
- **Routes:** `routes/web.php` (lines 90-95)
- **Views:**
  - `resources/views/profile/edit.blade.php` - Edit form
  - `resources/views/profile/show.blade.php` - Profile detail view
  - `resources/views/profile/partials/update-profile-information-form.blade.php`
  - `resources/views/profile/partials/update-password-form.blade.php`
  - `resources/views/profile/partials/delete-user-form.blade.php`

---

## Security Considerations

1. ✅ All routes protected with `auth` middleware
2. ✅ Password validation menggunakan `current_password` rule
3. ✅ Email change will reset email verification
4. ✅ Delete account requires password confirmation
5. ✅ All actions are logged to ActivityLog
6. ✅ Exception handling dan logging untuk debugging
