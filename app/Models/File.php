<?php

namespace App\Models;

use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use App\Traits\HasCreatorAndUpdator;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class File extends Model
{
    use HasFactory, NodeTrait, SoftDeletes, HasCreatorAndUpdator;

    // Hooks
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->parent) {
                return;
            }

            $model->path = (!$model->parent->isRoot() ? $model->parent->path . '/' : '') . Str::slug($model->name);
        });
    }

    // Attributes
    public function owner(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value, array $attributes) {
                return $attributes['created_by'] = auth()->id() ? 'me' : $this->user->id;
            }
        );
    }

    // Methods
    public function isOwnedBy($userId): bool
    {
        return $this->created_by === $userId;
    }

    public function isRoot()
    {
        return $this->parent_id === null;
    }

    public function get_file_size()
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $power = $this->size > 0 ? floor(log($this->size, 1024)) : 0;

        return number_format($this->size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
