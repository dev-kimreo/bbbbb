<?php

namespace App\Observers\Exhibitions;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Exhibitions\Banner;

class BannerObserver
{
    /**
     * Handle the  "created" event.
     *
     * @param Banner $banner
     * @return void
     */
    public function created(Banner $banner)
    {
        DataCreated::dispatch($banner, $banner->getAttribute('id'), '배너 등록');
    }

    /**
     * Handle the InquiryAnswer "updated" event.
     *
     * @param Banner $banner
     * @return void
     */
    public function updated(Banner $banner)
    {
        DataUpdated::dispatch($banner, $banner->getAttribute('id'), '배너 수정');
    }

    /**
     * Handle the Banner "deleted" event.
     *
     * @param Banner $banner
     * @return void
     */
    public function deleted(Banner $banner)
    {
        DataDeleted::dispatch($banner, $banner->getAttribute('id'), '배너 삭제');
    }

    /**
     * Handle the Banner "restored" event.
     *
     * @param Banner $banner
     * @return void
     */
    public function restored(Banner $banner)
    {
        DataUpdated::dispatch($banner, $banner->getAttribute('id'), '배너 복원');
    }

    /**
     * Handle the Banner "force deleted" event.
     *
     * @param Banner $banner
     * @return void
     */
    public function forceDeleted(Banner $banner)
    {
        DataDeleted::dispatch($banner, $banner->getAttribute('id'), '배너 영구 삭제');
    }
}
