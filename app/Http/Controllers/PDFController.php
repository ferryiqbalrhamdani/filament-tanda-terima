<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SuratTandaTerima;
use Barryvdh\DomPDF\Facade\Pdf;

class PDFController extends Controller
{
    public function printSuratTandaTerima(SuratTandaTerima $suratTandaTerima)
    {
        $pdf = Pdf::loadView('pdf.surat_tanda_terima', compact('suratTandaTerima'));
        return $pdf->stream('surat_tanda_terima_' . $suratTandaTerima->id . '.pdf');
    }
}
