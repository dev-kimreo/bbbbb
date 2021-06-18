<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\TermsOfUse;

class TermsOfUSeObserver
{
    /**
     * Handle the Tooltip "created" event.
     *
     * @param TermsOfUse $termsOfUse
     * @return void
     */
    public function created(TermsOfUse $termsOfUse)
    {
        DataCreated::dispatch($termsOfUse, $termsOfUse->getAttribute('id'), $termsOfUse->getAttribute('type') . ' 작성');
    }

    /**
     * Handle the Tooltip "updated" event.
     *
     * @param TermsOfUse $termsOfUse
     * @return void
     */
    public function updated(TermsOfUse $termsOfUse)
    {
        DataUpdated::dispatch($termsOfUse, $termsOfUse->getAttribute('id'), $termsOfUse->getAttribute('type') . ' 수정');
    }

    /**
     * Handle the Tooltip "deleted" event.
     *
     * @param TermsOfUse $termsOfUse
     * @return void
     */
    public function deleted(TermsOfUse $termsOfUse)
    {
        DataDeleted::dispatch($termsOfUse, $termsOfUse->getAttribute('id'), $termsOfUse->getAttribute('type') . ' 삭제');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param TermsOfUse $termsOfUse
     * @return void
     */
    public function restored(TermsOfUse $termsOfUse)
    {
        DataUpdated::dispatch($termsOfUse, $termsOfUse->getAttribute('id'), '삭제된 ' . $termsOfUse->getAttribute('type') . ' 복구');
    }

    /**
     * Handle the Tooltip "force deleted" event.
     *
     * @param TermsOfUse $termsOfUse
     * @return void
     */
    public function forceDeleted(TermsOfUse $termsOfUse)
    {
        DataDeleted::dispatch($termsOfUse, $termsOfUse->getAttribute('id'), $termsOfUse->getAttribute('type') . ' 삭제');
    }
}
