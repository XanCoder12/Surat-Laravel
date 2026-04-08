# Google Cloud Storage Integration Guide

## Overview

This guide shows how to integrate **Google Cloud Storage** with your Laravel app so documents (surat files) are automatically saved to Google Cloud instead of your server.

---

## 🎯 Benefits

✅ **Automatic backup** - Files safely stored in Google Cloud
✅ **Scalable** - No storage limits on your server
✅ **Secure** - Google handles encryption and security
✅ **Access anywhere** - Retrieve files from anywhere
✅ **Cost-effective** - Pay only for what you use
✅ **CDN integration** - Serve files via fast CDN

---

## 📋 Setup Steps

### Step 1: Create Google Cloud Project

1. Go to [Google Cloud Console](https://console.cloud.google.com)
2. **Create New Project**:
   - Project name: `Surat-Metrologi`
   - Click "Create"
3. **Wait for project to be created** (1-2 minutes)

### Step 2: Enable Cloud Storage API

1. In Google Cloud Console, search for **"Cloud Storage"**
2. Click on **"Cloud Storage"**
3. Click **"Enable"** (blue button at top)
4. Wait for API to be enabled

### Step 3: Create Storage Bucket

1. Go to **Cloud Storage > Buckets**
2. Click **"CREATE BUCKET"**
3. **Configure bucket:**
   - **Name**: `surat-metrologi-storage` (must be globally unique)
   - **Location type**: Multi-region
   - **Location**: `us` (or your region)
   - **Default storage class**: Standard
   - **Access control**: Uniform
   - **Data protection**: Optional (skip for now)
4. Click **"CREATE"**

### Step 4: Create Service Account

1. Go to **IAM & Admin > Service Accounts**
2. Click **"CREATE SERVICE ACCOUNT"**
3. **Fill in details:**
   - **Service account name**: `surat-storage-account`
   - **Service account ID**: Auto-filled
   - **Description**: For storing surat documents
4. Click **"CREATE AND CONTINUE"**

### Step 5: Grant Permissions

1. **Grant role to service account:**
   - Select role: **"Storage Admin"** or **"Storage Object Admin"**
   - This allows reading/writing to buckets
2. Click **"CONTINUE"**
3. Click **"DONE"**

### Step 6: Create & Download Key

1. Go back to **IAM & Admin > Service Accounts**
2. Click on the service account you created
3. Go to **"Keys"** tab
4. Click **"ADD KEY > Create new key"**
5. Select **JSON** format
6. Click **"CREATE"**
7. **Download** the JSON file (save somewhere safe!)

---

## 💻 Install Laravel Package

### Step 1: Install Google Cloud Storage Package

```bash
composer require google/cloud-storage
```

### Step 2: Copy Key File

1. Copy the downloaded JSON key file to your project
2. Create directory: `config/` (if not exists)
3. Move JSON file: `config/google-cloud-key.json`
4. **Important**: Add to `.gitignore`:
   ```
   config/google-cloud-key.json
   ```

### Step 3: Update `.env` File

Add these lines to `.env`:

```env
GOOGLE_CLOUD_PROJECT_ID=your-project-id
GOOGLE_CLOUD_KEY_FILE=config/google-cloud-key.json
GOOGLE_CLOUD_BUCKET=surat-metrologi-storage
GOOGLE_CLOUD_ENABLED=true
```

Replace `your-project-id` with your actual project ID (from JSON file).

### Step 4: Configure Laravel Filesystem

Edit `config/filesystems.php`:

```php
'disks' => [
    // ... existing disks ...
    
    'gcs' => [
        'driver' => 'gcs',
        'project_id' => env('GOOGLE_CLOUD_PROJECT_ID'),
        'key_file' => storage_path(env('GOOGLE_CLOUD_KEY_FILE')),
        'bucket' => env('GOOGLE_CLOUD_BUCKET'),
    ],
],
```

---

## 🔧 Update Your Code

### Option 1: Use Google Cloud in Surat Controller

Edit `app/Http/Controllers/User/SuratController.php`:

**Current code (line 59-62):**
```php
// Upload file to local storage
$fileWord = $request->file('file_word')->store('surat/word', 'public');
$fileLamp = $request->file('file_lampiran')
          ? $request->file('file_lampiran')->store('surat/lampiran', 'public')
          : null;
```

**Change to:**
```php
// Upload to Google Cloud Storage (if enabled)
$disk = env('GOOGLE_CLOUD_ENABLED') ? 'gcs' : 'public';

$fileWord = $request->file('file_word')->store('surat/word', $disk);
$fileLamp = $request->file('file_lampiran')
          ? $request->file('file_lampiran')->store('surat/lampiran', $disk)
          : null;
```

### Option 2: Create Helper Function

Create `app/Helpers/FileHelper.php`:

```php
<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;

class FileHelper
{
    public static function getStorageDisk(): string
    {
        return env('GOOGLE_CLOUD_ENABLED', false) ? 'gcs' : 'public';
    }
    
    public static function storeFile($file, $path): string
    {
        return $file->store($path, self::getStorageDisk());
    }
    
    public static function getFileUrl($filePath): string
    {
        $disk = self::getStorageDisk();
        
        if ($disk === 'gcs') {
            return Storage::disk('gcs')->url($filePath);
        }
        
        return Storage::disk('public')->url($filePath);
    }
    
    public static function deleteFile($filePath): bool
    {
        $disk = self::getStorageDisk();
        return Storage::disk($disk)->delete($filePath);
    }
}
```

### Option 3: Use Storage Facade Directly

```php
use Illuminate\Support\Facades\Storage;

// Store file
$path = Storage::disk('gcs')->put('surat/word', $request->file('file_word'));

// Get URL
$url = Storage::disk('gcs')->url($path);

// Delete file
Storage::disk('gcs')->delete($path);

// Download file
return Storage::disk('gcs')->download($path);
```

---

## 📝 Example Implementation

Here's how to update the Surat Controller:

```php
<?php

namespace App\Http\Controllers\User;

use Illuminate\Support\Facades\Storage;

class SuratController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'judul'          => 'required|string|max:255',
            'jenis'          => 'required|in:nota_dinas,surat_dinas,surat_keputusan,surat_pernyataan,surat_keterangan',
            'sifat'          => 'required|in:biasa,segera,rahasia',
            'tujuan'         => 'required|string|max:500',
            'file_word'      => 'required|file|mimes:docx,doc|max:10240',
            'file_lampiran'  => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:20480',
        ]);

        // Choose storage disk
        $disk = env('GOOGLE_CLOUD_ENABLED', false) ? 'gcs' : 'public';

        // Upload files
        $fileWord = $request->file('file_word')->store('surat/word', $disk);
        $fileLamp = $request->file('file_lampiran')
                  ? $request->file('file_lampiran')->store('surat/lampiran', $disk)
                  : null;

        // Calculate SLA
        $deadline = $this->hitungSLA(now());

        // Create surat
        $surat = Surat::create([
            'user_id'       => Auth::id(),
            'judul'         => $request->judul,
            'jenis'         => $request->jenis,
            'sifat'         => $request->sifat,
            'tujuan'        => $request->tujuan,
            'file_word'     => $fileWord,      // GCS path
            'file_lampiran' => $fileLamp,      // GCS path
            'tahap_sekarang'=> 1,
            'status'        => 'proses',
            'deadline_sla'  => $deadline,
        ]);

        // Rest of the code...
        
        return redirect()->route('user.surat.show', $surat)
                         ->with('success', 'Surat berhasil diajukan!');
    }
    
    public function download(Surat $surat)
    {
        $disk = env('GOOGLE_CLOUD_ENABLED', false) ? 'gcs' : 'public';
        
        return Storage::disk($disk)->download(
            $surat->file_word,
            "Surat_{$surat->id}.docx"
        );
    }
}
```

---

## 🌐 Serve Files via URL

### If files need to be publicly accessible:

1. **Set bucket permissions** (Google Cloud Console):
   - Go to **Cloud Storage > Buckets**
   - Click your bucket
   - Go to **Permissions**
   - Add member: `allUsers`
   - Role: `Storage Object Viewer`

2. **Get public URL:**
```php
$url = Storage::disk('gcs')->url($surat->file_word);
// Output: https://storage.googleapis.com/surat-metrologi-storage/surat/word/filename.docx
```

### If files should be private (protected download):

1. **Keep bucket private** (don't add allUsers)
2. **Generate signed URLs** (valid for time period):

```php
use Google\Cloud\Storage\StorageClient;

$storage = new StorageClient([
    'projectId' => env('GOOGLE_CLOUD_PROJECT_ID'),
    'keyFilePath' => storage_path(env('GOOGLE_CLOUD_KEY_FILE')),
]);

$bucket = $storage->bucket(env('GOOGLE_CLOUD_BUCKET'));
$object = $bucket->object($surat->file_word);

$url = $object->signedUrl(new \DateTime('+ 20 minutes'));
// URL expires in 20 minutes
```

---

## 🔒 Security Best Practices

1. **Never commit key file:**
   ```
   # .gitignore
   config/google-cloud-key.json
   ```

2. **Restrict bucket access:**
   - Keep bucket private
   - Only service account has access
   - Use signed URLs for downloads

3. **Limit key permissions:**
   - Use "Storage Object Admin" (not Owner)
   - This role can't delete buckets, only manage objects

4. **Rotate keys regularly:**
   - Delete old keys after creating new ones
   - Google Cloud Console > Service Accounts > Keys

5. **Monitor access:**
   - Enable Google Cloud Audit Logs
   - Monitor unauthorized access attempts

---

## 📊 Cost Estimation

**Google Cloud Storage Pricing (as of 2026):**
- **Storage**: $0.020 per GB/month
- **Network (download)**: $0.123 per GB
- **Requests**: $0.0004 per 10k requests

**Example for small business:**
- 100 GB storage: $2/month
- 50 GB downloads/month: $6/month
- 100k requests/month: $4/month
- **Total**: ~$12-15/month

---

## 🧪 Test It

### Test Google Cloud Upload

```bash
php artisan tinker

# Store a test file
$path = Storage::disk('gcs')->put('test', 'Hello World');

# Check if file exists
Storage::disk('gcs')->exists($path);  // true

# Get file
Storage::disk('gcs')->get($path);  // "Hello World"

# Delete file
Storage::disk('gcs')->delete($path);
```

### Check Google Cloud Console

1. Go to **Cloud Storage > Buckets**
2. Click your bucket name
3. You should see `test` file in the browser
4. Click it to view details

---

## 🚀 Deployment

### On Production Server

1. **Copy key file securely:**
   ```bash
   # Don't commit to git!
   # Instead, upload separately:
   scp config/google-cloud-key.json user@server:/path/to/app/config/
   chmod 600 config/google-cloud-key.json
   ```

2. **Or use Google Cloud Secret Manager:**
   - Store key in Secret Manager
   - Retrieve at runtime
   - Never save to disk

3. **Set environment variables:**
   ```bash
   export GOOGLE_CLOUD_PROJECT_ID=your-project-id
   export GOOGLE_CLOUD_BUCKET=surat-metrologi-storage
   export GOOGLE_CLOUD_ENABLED=true
   ```

---

## 🐛 Troubleshooting

### "Permission denied" error?
- Check service account has "Storage Admin" role
- Check bucket exists and is accessible
- Verify JSON key file path is correct

### "Bucket not found" error?
- Check bucket name in `.env`
- Verify bucket exists in Google Cloud Console
- Check project ID matches

### "Project not found" error?
- Verify `GOOGLE_CLOUD_PROJECT_ID` matches
- Found in JSON key file: `"project_id"`

### Files not uploading?
- Check file size limits (10MB for docs, 20MB for attachments)
- Verify disk quota not exceeded
- Check network connection

### URLs not working?
- If private bucket: use signed URLs
- If public bucket: check permissions are set
- Verify bucket URL format is correct

---

## 📚 References

- [Google Cloud Storage Docs](https://cloud.google.com/storage/docs)
- [Laravel Cloud Storage Package](https://packagist.org/packages/google/cloud-storage)
- [Laravel Filesystem Configuration](https://laravel.com/docs/12.x/filesystem)
- [Google Cloud Pricing](https://cloud.google.com/storage/pricing)

---

## ✅ Checklist

- [ ] Create Google Cloud Project
- [ ] Enable Cloud Storage API
- [ ] Create Storage Bucket
- [ ] Create Service Account
- [ ] Grant Storage Admin role
- [ ] Create and download JSON key
- [ ] Add key to `config/google-cloud-key.json`
- [ ] Update `.env` with bucket details
- [ ] Update `config/filesystems.php`
- [ ] Install `google/cloud-storage` package
- [ ] Update Surat Controller
- [ ] Test uploading file
- [ ] Verify file in Google Cloud Console
- [ ] Set bucket permissions (if public access needed)
- [ ] Add key file to `.gitignore`

---

## 🎯 Next Steps

1. **Quick Setup** (20 minutes):
   - Follow steps 1-6 above
   - Install package
   - Test with `php artisan tinker`

2. **Integration** (30 minutes):
   - Update Surat Controller
   - Test with actual file upload
   - Verify in Google Cloud Console

3. **Production** (1 hour):
   - Set up secure key management
   - Configure CDN if needed
   - Set up monitoring and logging

---

**That's it!** Your documents are now safely stored in Google Cloud! ☁️
