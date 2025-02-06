<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CancelLetter extends Model
{
    use HasFactory;

    protected $fillable = [
        'letter_id',
        'reason',
        'pic_id',
        'tanggal_surat',
        'letter_number',
        'title',
        'status',
        'file',
        'content'
    ];

    public function letter()
    {
        return $this->belongsTo(Letter::class);
    }

    public function pic()
    {
        return $this->belongsTo(Pic::class);
    }
}
