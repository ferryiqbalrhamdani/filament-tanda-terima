<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Letter extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'pic_id',
        'tanggal_surat',
        'letter_number',
        'title',
        'file',
        'content',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }

    public function cancelLetters()
    {
        return $this->hasMany(CancelLetter::class, 'letter_id');
    }

    public static function getRomanMonth(int $month): string
    {
        $romanMonths = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ];

        return $romanMonths[$month] ?? '';
    }
}
