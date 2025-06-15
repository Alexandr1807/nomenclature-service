<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Http\Request;

class Category extends Model
{
    use Auditable;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'parent_id',
        'created_by',
        'updated_by',
    ];

    /**
     * Boot logic: UUID generation and author stamps.
     */
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

    /**
     * Родительская категория.
     */
    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Дочерние категории.
     */
    public function children()
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function childrenRecursive()
    {
        return $this->children()->with('childrenRecursive');
    }
}
