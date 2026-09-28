<?php

namespace App\Observers;

use App\Services\AuditLogger;
use Illuminate\Database\Eloquent\Model;

class AuditableObserver
{
    public function __construct(private readonly AuditLogger $audit) {}

    public function created(Model $model): void
    {
        $this->audit->created($model);
    }

    public function updated(Model $model): void
    {
        $this->audit->updated($model, $model->getOriginal());
    }

    public function deleted(Model $model): void
    {
        $this->audit->deleted($model);
    }

    public function restored(Model $model): void
    {
        $this->audit->write('restored', $model, [], $model->attributesToArray());
    }
}
