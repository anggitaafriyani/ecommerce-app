<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderIssue extends Model
{
    use HasFactory;

    public const CATEGORY_LABELS = [
        'belum_diterima' => 'Barang belum diterima',
        'rusak' => 'Barang rusak / cacat saat diterima',
        'tidak_sesuai' => 'Barang tidak sesuai pesanan',
        'lainnya' => 'Kendala pengiriman lainnya',
    ];

    public const STATUS_LABELS = [
        'open' => 'Menunggu tanggapan',
        'in_review' => 'Sedang diperiksa',
        'resolved' => 'Selesai ditangani',
    ];

    protected $guarded = ['id'];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
