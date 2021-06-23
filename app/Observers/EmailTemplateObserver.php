<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\EmailTemplate;

class EmailTemplateObserver
{
    /**
     * Handle the Tooltip "created" event.
     *
     * @param EmailTemplate $mailTemplate
     * @return void
     */
    public function created(EmailTemplate $mailTemplate)
    {
        DataCreated::dispatch($mailTemplate, $mailTemplate->getAttribute('id'), '메일 템플릿 작성');
    }

    /**
     * Handle the Tooltip "updated" event.
     *
     * @param EmailTemplate $mailTemplate
     * @return void
     */
    public function updated(EmailTemplate $mailTemplate)
    {
        DataUpdated::dispatch($mailTemplate, $mailTemplate->getAttribute('id'), '메일 템플릿 수정');
    }

    /**
     * Handle the Tooltip "deleted" event.
     *
     * @param EmailTemplate $mailTemplate
     * @return void
     */
    public function deleted(EmailTemplate $mailTemplate)
    {
        DataDeleted::dispatch($mailTemplate, $mailTemplate->getAttribute('id'), '메일 템플릿 삭제');
    }

    /**
     * Handle the Tooltip "restored" event.
     *
     * @param EmailTemplate $mailTemplate
     * @return void
     */
    public function restored(EmailTemplate $mailTemplate)
    {
        DataUpdated::dispatch($mailTemplate, $mailTemplate->getAttribute('id'), '삭제된 메일 템플릿 복구');
    }

    /**
     * Handle the Tooltip "force deleted" event.
     *
     * @param EmailTemplate $mailTemplate
     * @return void
     */
    public function forceDeleted(EmailTemplate $mailTemplate)
    {
        DataDeleted::dispatch($mailTemplate, $mailTemplate->getAttribute('id'), '메일 템플릿 영구 삭제');
    }
}
