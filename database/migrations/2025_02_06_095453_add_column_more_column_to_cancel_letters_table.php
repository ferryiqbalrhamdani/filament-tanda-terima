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
        Schema::table('cancel_letters', function (Blueprint $table) {
            $table->foreignId('pic_id')->nullable()->constrained()->onDelete('cascade'); // Relasi ke pic
            $table->date('tanggal_surat')->nullable();
            $table->string('letter_number')->nullable(); // Nomor surat (akan di-generate)
            $table->string('title')->nullable(); // Judul surat
            $table->string('file')->nullable(); // File surat
            $table->text('content')->nullable(); // Isi surat
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cancel_letters', function (Blueprint $table) {
            $table->dropForeign(['pic_id']); // Menghapus foreign key constraint
            $table->dropColumn([
                'pic_id',
                'tanggal_surat',
                'letter_number',
                'title',
                'file',
                'content'
            ]); // Menghapus kolom-kolom yang ditambahkan
        });
    }
};
