<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Supplier extends Model
{
    use Auditable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'phone',
        'contact_name',
        'website',
        'description',
        'created_by',
        'updated_by',
    ];

    protected static function booted(): void
    {
        static::creating(function ($m) {
            if (!$m->getKey()) {
                $m->{$m->getKeyName()} = (string) Str::uuid();
            }

            if (empty($m->created_by)) {
                $m->created_by = auth()->id();
            }
        });

        static::updating(function ($m) {
            if (empty($m->updated_by)) {
                $m->updated_by = auth()->id();
            }
        });
    }
}
