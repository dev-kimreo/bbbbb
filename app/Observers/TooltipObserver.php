<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Tooltip;

class TooltipObserver
{
    /**
     * Handle the Tooltip "created" event.
     *
     * @param Tooltip $tooltip
     * @return void
     */
    public function created(Tooltip $tooltip)
    {
        DataCreated::dispatch($tooltip, $tooltip->getAttribute('id'), '툴팁 작성');
    }

    /**
     * Handle the Tooltip "updated" event.
     *
     * @param Tooltip $tooltip
     * @return void
     */
    public function updated(Tooltip $tooltip)
    {
        DataUpdated::dispatch($tooltip, $tooltip->getAttribute('id'), '툴팁 수정');
    }

    /**
     * Handle the Tooltip "deleted" event.
     *
     * @param Tooltip $tooltip
     * @return void
     */
    public function deleted(Tooltip $tooltip)
    {
        DataDeleted::dispatch($tooltip, $tooltip->getAttribute('id'), '툴팁 삭제');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param Tooltip $tooltip
     * @return void
     */
    public function restored(Tooltip $tooltip)
    {
        DataUpdated::dispatch($tooltip, $tooltip->getAttribute('id'), '삭제된 툴팁 복구');
    }

    /**
     * Handle the Tooltip "force deleted" event.
     *
     * @param Tooltip $tooltip
     * @return void
     */
    public function forceDeleted(Tooltip $tooltip)
    {
        DataDeleted::dispatch($tooltip, $tooltip->getAttribute('id'), '툴팁 삭제');
    }
}
