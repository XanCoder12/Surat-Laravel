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
        Schema::table('surats', function (Blueprint $table) {
            $table->timestamp('disetujui_pada')->nullable()->after('deadline_sla');
            $table->timestamp('file_dihapus_pada')->nullable()->after('disetujui_pada');
            $table->timestamp('file_expires_at')->nullable()->after('file_dihapus_pada');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('surats', function (Blueprint $table) {
            $table->dropColumn(['disetujui_pada', 'file_dihapus_pada', 'file_expires_at']);
        });
    }
};
