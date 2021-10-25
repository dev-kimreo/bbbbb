<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Inquiries\InquiryAnswer;
use Illuminate\Support\Carbon;

class InquiryAnswerObserver
{
    /**
     * Handle the InquiryAnswer "created" event.
     *
     * @param InquiryAnswer $answer
     * @return void
     */
    public function created(InquiryAnswer $answer)
    {
        DataCreated::dispatch($answer->inquiry()->getRelated(), $answer->getAttribute('inquiry_id'), '답변등록');

        $answer->inquiry->updated_at = Carbon::now();
        $answer->inquiry->save();
    }

    /**
     * Handle the InquiryAnswer "updated" event.
     *
     * @param InquiryAnswer $answer
     * @return void
     */
    public function updated(InquiryAnswer $answer)
    {
        DataUpdated::dispatch($answer->inquiry()->getRelated(), $answer->getAttribute('inquiry_id'), '답변수정');

        $answer->inquiry->updated_at = Carbon::now();
        $answer->inquiry->save();
    }

    /**
     * Handle the InquiryAnswer "deleted" event.
     *
     * @param InquiryAnswer $answer
     * @return void
     */
    public function deleted(InquiryAnswer $answer)
    {
        DataDeleted::dispatch($answer->inquiry()->getRelated(), $answer->getAttribute('inquiry_id'), '답변삭제');

        $answer->inquiry->updated_at = Carbon::now();
        $answer->inquiry->save();
    }

    /**
     * Handle the InquiryAnswer "restored" event.
     *
     * @param InquiryAnswer $answer
     * @return void
     */
    public function restored(InquiryAnswer $answer)
    {
        DataUpdated::dispatch($answer->inquiry()->getRelated(), $answer->getAttribute('inquiry_id'), '삭제된 답변 복원');
    }

    /**
     * Handle the InquiryAnswer "force deleted" event.
     *
     * @param InquiryAnswer $answer
     * @return void
     */
    public function forceDeleted(InquiryAnswer $answer)
    {
        DataDeleted::dispatch($answer->inquiry()->getRelated(), $answer->getAttribute('inquiry_id'), '답변삭제');
    }
}
