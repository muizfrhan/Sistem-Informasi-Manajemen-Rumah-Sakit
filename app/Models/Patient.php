<?php

namespace App\Models;

use App\Casts\EncryptedNik;
use App\Enums\Gender;
use App\Enums\InsuranceType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    /** @use HasFactory<\Database\Factories\PatientFactory> */
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'mr_number',
        'name',
        'nik',
        'gender',
        'birth_date',
        'birth_place',
        'blood_type',
        'marital_status',
        'phone',
        'address',
        'village',
        'district',
        'city',
        'province',
        'postal_code',
        'insurance_type',
        'insurance_number',
        'insurance_name',
        'emergency_contact_name',
        'emergency_contact_phone',
        'is_active',
        'created_by',
    ];

    protected $hidden = ['nik_hash'];

    protected function casts(): array
    {
        return [
            'nik' => EncryptedNik::class,
            'gender' => Gender::class,
            'insurance_type' => InsuranceType::class,
            'birth_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        $term = trim($term);

        if (self::isNik($term)) {
            $hash = EncryptedNik::blindIndex($term);

            return $query->where(fn (Builder $q) => $q->where('nik_hash', $hash)->orWhere('name', 'ILIKE', '%'.$term.'%'));
        }

        $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $term).'%';

        return $query->where(fn (Builder $q) => $q
            ->where('mr_number', 'ILIKE', $like)
            ->orWhere('name', 'ILIKE', $like)
            ->orWhere('insurance_number', 'ILIKE', $like));
    }

    public static function isNik(string $value): bool
    {
        return (bool) preg_match('/^\d{16}$/', trim($value));
    }

    public function getAgeAttribute(): int
    {
        return (int) \Carbon\Carbon::parse($this->birth_date)->age;
    }

    public function getAgeYearsAttribute(): string
    {
        $diff = $this->birth_date->diff(now());

        return "{$diff->y} th {$diff->m} bl {$diff->d} hr";
    }

    public function getMaskedNikAttribute(): string
    {
        if (! $this->nik) {
            return '-';
        }

        $nik = $this->nik;

        return mb_substr($nik, 0, 6).'******'.mb_substr($nik, -4);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
