<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support modifying ENUM columns easily, so we use raw SQL
        \DB::statement("ALTER TABLE surats MODIFY COLUMN jenis ENUM('nota_dinas', 'surat_dinas', 'surat_keputusan', 'surat_pernyataan', 'surat_keterangan', 'surat_undangan', 'surat_lainnya')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("ALTER TABLE surats MODIFY COLUMN jenis ENUM('nota_dinas', 'surat_dinas', 'surat_keputusan', 'surat_pernyataan', 'surat_keterangan')");
    }
};
