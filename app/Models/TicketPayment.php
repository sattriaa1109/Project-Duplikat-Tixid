<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TicketPayment extends Model
{
    // Pastikan 'HasFactory' ada jika Anda menggunakannya
    use HasFactory, SoftDeletes; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ticket_id',
        'qrcode',
        'status',
        'book_date',  // <-- INI DIA KUNCI MASALAHNYA
        'paid_date'
    ];

    /**
     * Relasi: Satu pembayaran ini MILIK SATU tiket.
     */
    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }
}