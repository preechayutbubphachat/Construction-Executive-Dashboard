<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use LogicException;

trait HasImmutableKey
{
    public static function bootHasImmutableKey(): void
    {
        static::updating(function (Model $model): void {
            if ($model->isDirty($model->getKeyName())) {
                throw new LogicException(sprintf('%s identifiers are immutable.', class_basename($model)));
            }
        });
    }
}
