<?php

namespace App\Observers\Exhibitions;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Exhibitions\Popup;

class PopupObserver
{
    /**
     * Handle the  "created" event.
     *
     * @param Popup $popup
     * @return void
     */
    public function created(Popup $popup)
    {
        DataCreated::dispatch($popup, $popup->getAttribute('id'), '팝업 등록');
    }

    /**
     * Handle the InquiryAnswer "updated" event.
     *
     * @param Popup $popup
     * @return void
     */
    public function updated(Popup $popup)
    {
        DataUpdated::dispatch($popup, $popup->getAttribute('id'), '팝업 수정');
    }

    /**
     * Handle the Popup "deleted" event.
     *
     * @param Popup $popup
     * @return void
     */
    public function deleted(Popup $popup)
    {
        DataDeleted::dispatch($popup, $popup->getAttribute('id'), '팝업 삭제');
    }

    /**
     * Handle the Popup "restored" event.
     *
     * @param Popup $popup
     * @return void
     */
    public function restored(Popup $popup)
    {
        DataUpdated::dispatch($popup, $popup->getAttribute('id'), '팝업 복원');
    }

    /**
     * Handle the Popup "force deleted" event.
     *
     * @param Popup $popup
     * @return void
     */
    public function forceDeleted(Popup $popup)
    {
        DataDeleted::dispatch($popup, $popup->getAttribute('id'), '팝업 영구 삭제');
    }
}
