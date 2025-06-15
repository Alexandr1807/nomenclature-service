<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;
    use Auditable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'category_id',
        'supplier_id',
        'price',
        'file_url',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $q) {
            $q->where('is_active', true);
        });

        static::creating(function ($m) {
            if (! $m->getKey()) {
                $m->{$m->getKeyName()} = (string) Str::uuid();
            }

            if (empty($m->created_by)) {
                $m->created_by = auth()->id();
            }
        });

        static::updating(function ($m) {
            $m->updated_by = auth()->id();
        });
    }
}
