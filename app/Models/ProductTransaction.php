<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'booking_trx_id',
        'city',
        'post_code',
        'address',
        'quantity',
        'sub_total_amount',
        'grand_total_amount',
        'discount_amount',
        'is_paid',
        'shoe_id',
        'shoe_size',
        'promo_code_id',
        'proof',
    ];

    public static function generateUniqueTrxId(): string
    {
        $prefix = 'SS-';
        do {
            $randomString = $prefix . mt_rand(1000, 9999); // prefix + 4 angka random (SS-1234)
        //cek apakah trx_id sudah ada di database
        // Jika sudah ada, ulangi proses hingga mendapatkan trx_id yang unik
        } while (self::where('booking_trx_id', $randomString)->exists()); 
    
        // Jika belum ada, kembalikan trx_id yang unik
        return $randomString; //(SS-1235)
    } 

    public function shoe(): BelongsTo
    {
        return $this->belongsTo(Shoe::class, 'shoe_id');
    }

    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class, 'promo_code_id');
    }
}
