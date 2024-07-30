<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'tb_surat_tanda_terima_id',
        'keterangan',
        'nomor_document',
        'qty',
        'satuan',
    ];

    public function suratTandaTerima()
    {
        return $this->belongsTo(SuratTandaTerima::class);
    }
}
