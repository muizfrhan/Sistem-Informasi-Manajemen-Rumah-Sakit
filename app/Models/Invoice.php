<?php

namespace App\Models;

use App\Enums\InvoiceItemType;
use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use Database\Factories\InvoiceFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    /** @use HasFactory<InvoiceFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_number',
        'patient_id',
        'registration_id',
        'created_by',
        'status',
        'subtotal',
        'discount',
        'total',
        'paid_total',
        'insurance_type',
        'insurance_holder',
        'issued_at',
        'due_at',
        'paid_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatus::class,
            'issued_at' => 'date',
            'due_at' => 'date',
            'paid_at' => 'datetime',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'total' => 'integer',
            'paid_total' => 'integer',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->whereIn('status', [
            InvoiceStatus::DRAFT,
            InvoiceStatus::UNPAID,
            InvoiceStatus::PARTIAL,
        ]);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', InvoiceStatus::PAID);
    }

    public function scopeForMonth(Builder $query, \DateTimeInterface $date): Builder
    {
        return $query->whereBetween('issued_at', [
            $date->format('Y-m-01'),
            $date->format('Y-m-t'),
        ]);
    }

    /** Sisa tagihan yang harus dibayar. */
    public function getOutstandingAttribute(): int
    {
        return max(0, (int) $this->total - (int) $this->paid_total);
    }

    public function getProgressAttribute(): int
    {
        return $this->total > 0 ? (int) round($this->paid_total / $this->total * 100) : 0;
    }

    /** Ringkasan komposisi biaya per jenis item. */
    public function breakdown(): array
    {
        return $this->items
            ->groupBy(fn (InvoiceItem $item) => $item->item_type)
            ->map(fn ($group, $type) => [
                'type' => $type,
                'label' => $type instanceof InvoiceItemType ? $type->label() : (string) $type,
                'total' => (int) $group->sum('subtotal'),
            ])
            ->sortByDesc('total')
            ->values()
            ->all();
    }

    public function preferredPaymentMethods(): array
    {
        if ($this->insurance_type === 'BPJS') {
            return [PaymentMethod::BPJS, PaymentMethod::CASH];
        }

        if ($this->insurance_type && $this->insurance_type !== 'NONE') {
            return [PaymentMethod::INSURANCE, PaymentMethod::CASH];
        }

        return [
            PaymentMethod::CASH,
            PaymentMethod::QRIS,
            PaymentMethod::CARD,
            PaymentMethod::TRANSFER,
        ];
    }
}
