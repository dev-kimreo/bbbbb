<?php

namespace App\Observers\Exhibitions;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use Illuminate\Database\Eloquent\Model;

class ExhibitionObserver
{
    /**
     * Handle the  "created" event.
     *
     * @param Model $model
     * @return void
     */
    public function created(Model $model)
    {
        DataCreated::dispatch($model, $model->getAttribute('id'), '신규등록');
    }

    /**
     * Handle the InquiryAnswer "updated" event.
     *
     * @param Model $model
     * @return void
     */
    public function updated(Model $model)
    {
        DataUpdated::dispatch($model, $model->getAttribute('id'), '정보수정');
    }

    /**
     * Handle the Model "deleted" event.
     *
     * @param Model $model
     * @return void
     */
    public function deleted(Model $model)
    {
        DataDeleted::dispatch($model, $model->getAttribute('id'), '삭제');
    }

    /**
     * Handle the Model "restored" event.
     *
     * @param Model $model
     * @return void
     */
    public function restored(Model $model)
    {
        DataUpdated::dispatch($model, $model->getAttribute('id'), '삭제복원');
    }

    /**
     * Handle the Model "force deleted" event.
     *
     * @param Model $model
     * @return void
     */
    public function forceDeleted(Model $model)
    {
        DataDeleted::dispatch($model, $model->getAttribute('id'), '삭제');
    }
}
