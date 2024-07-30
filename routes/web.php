<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PDFController;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/print-surat-tanda-terima/{suratTandaTerima}', [PDFController::class, 'printSuratTandaTerima'])->name('print.surat_tanda_terima');
