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
}
