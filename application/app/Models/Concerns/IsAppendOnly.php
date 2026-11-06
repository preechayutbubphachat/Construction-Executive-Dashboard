<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use LogicException;

trait IsAppendOnly
{
    public static function bootIsAppendOnly(): void
    {
        static::updating(fn (Model $model) => throw new LogicException(sprintf('%s records are append-only.', class_basename($model))));
        static::deleting(fn (Model $model) => throw new LogicException(sprintf('%s records are append-only.', class_basename($model))));
    }
}
