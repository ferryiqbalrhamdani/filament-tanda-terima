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
        Schema::create('letters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade'); // Relasi ke perusahaan
            $table->foreignId('pic_id')->nullable()->constrained()->onDelete('cascade'); // Relasi ke pic
            $table->date('tanggal_surat')->nullable();
            $table->string('letter_number')->nullable(); // Nomor surat (akan di-generate)
            $table->string('title')->nullable(); // Judul surat
            $table->string('status')->nullable()->default('terpakai'); // Keterangan surat
            $table->string('file')->nullable(); // File surat
            $table->text('content')->nullable(); // Isi surat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
