<?php
/**
 * Data Pegawai Setup Script
 * This script creates the required directories and moves the view files
 * 
 * Run this from project root: php setup_data_pegawai.php
 */

$baseDir = __DIR__;
$viewDir = $baseDir . '/resources/views/admin/user';
$tmpIndexFile = $baseDir . '/tmp_user_index.blade.php';
$tmpShowFile = $baseDir . '/tmp_user_show.blade.php';
$destIndexFile = $viewDir . '/index.blade.php';
$destShowFile = $viewDir . '/show.blade.php';

echo "🔧 Data Pegawai Setup Script\n";
echo "═" . str_repeat("═", 38) . "\n\n";

// Step 1: Create directory
echo "Step 1: Creating directory...\n";
if (!is_dir($viewDir)) {
    if (mkdir($viewDir, 0755, true)) {
        echo "✅ Directory created: resources/views/admin/user/\n";
    } else {
        echo "❌ Failed to create directory\n";
        exit(1);
    }
} else {
    echo "✅ Directory already exists\n";
}

// Step 2: Copy index file
echo "\nStep 2: Moving index file...\n";
if (file_exists($tmpIndexFile)) {
    if (copy($tmpIndexFile, $destIndexFile)) {
        echo "✅ Copied: tmp_user_index.blade.php → resources/views/admin/user/index.blade.php\n";
        unlink($tmpIndexFile);
        echo "   (Temporary file deleted)\n";
    } else {
        echo "❌ Failed to copy index file\n";
        exit(1);
    }
} else {
    echo "⚠️  tmp_user_index.blade.php not found\n";
}

// Step 3: Copy show file
echo "\nStep 3: Moving show file...\n";
if (file_exists($tmpShowFile)) {
    if (copy($tmpShowFile, $destShowFile)) {
        echo "✅ Copied: tmp_user_show.blade.php → resources/views/admin/user/show.blade.php\n";
        unlink($tmpShowFile);
        echo "   (Temporary file deleted)\n";
    } else {
        echo "❌ Failed to copy show file\n";
        exit(1);
    }
} else {
    echo "⚠️  tmp_user_show.blade.php not found\n";
}

// Step 4: Verify
echo "\nStep 4: Verifying setup...\n";
if (file_exists($destIndexFile) && file_exists($destShowFile)) {
    echo "✅ All files in place!\n";
    echo "\n" . str_repeat("═", 40) . "\n";
    echo "✨ Setup Complete!\n";
    echo "═" . str_repeat("═", 39) . "\n";
    echo "\nYou can now access: http://localhost:8000/admin/Users\n";
    echo "The 'Data Pegawai' menu item in the admin sidebar is ready to use!\n";
} else {
    echo "❌ Verification failed - files not in correct location\n";
    exit(1);
}
?>
