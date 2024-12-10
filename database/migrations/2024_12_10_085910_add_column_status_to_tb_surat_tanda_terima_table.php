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
        Schema::table('tb_surat_tanda_terima', function (Blueprint $table) {
            $table->boolean('status')->default(true);
            $table->string('penanggung_jawab', 100)->nullable();
            $table->text('keterangan')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tb_surat_tanda_terima', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
