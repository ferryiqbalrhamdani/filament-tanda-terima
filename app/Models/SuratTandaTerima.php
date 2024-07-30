<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratTandaTerima extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'tb_surat_tanda_terima';

    protected $fillable = [
        'company_id',
        'kepada',
        'nomor_document',
        'tanggal',
        'total',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function items()
    {
        return $this->hasMany(Item::class, 'tb_surat_tanda_terima_id');
    }

    public static function generateNomorDocument($companyId, $tanggal)
    {
        $company = Company::find($companyId);
        $companySlug = $company->slug;
        $month = date('m', strtotime($tanggal));
        $year = date('Y', strtotime($tanggal));
        $latestDocument = self::where('company_id', $companyId)
            ->whereYear('created_at', $year)
            ->orderBy('nomor_document', 'desc')
            ->first();

        $nextNumber = 1;

        if ($latestDocument) {
            $lastNumber = intval(substr($latestDocument->nomor_document, 0, 3));
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('%03d/%s/%02d-%d', $nextNumber, $companySlug, $month, $year);
    }
}
