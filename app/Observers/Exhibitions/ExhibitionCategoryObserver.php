<?php

namespace App\Observers\Exhibitions;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Exhibitions\ExhibitionCategory;

class ExhibitionCategoryObserver
{
    /**
     * Handle the InquiryAnswer "created" event.
     *
     * @param ExhibitionCategory $category
     * @return void
     */
    public function created(ExhibitionCategory $category)
    {
        DataCreated::dispatch($category, $category->getAttribute('id'), '전시관리 카테고리 등록');
    }

    /**
     * Handle the InquiryAnswer "updated" event.
     *
     * @param ExhibitionCategory $category
     * @return void
     */
    public function updated(ExhibitionCategory $category)
    {
        DataUpdated::dispatch($category, $category->getAttribute('id'), '전시관리 카테고리 수정');
    }

    /**
     * Handle the ExhibitionCategory "deleted" event.
     *
     * @param ExhibitionCategory $category
     * @return void
     */
    public function deleted(ExhibitionCategory $category)
    {
        DataDeleted::dispatch($category, $category->getAttribute('id'), '전시관리 카테고리 삭제');
    }

    /**
     * Handle the ExhibitionCategory "restored" event.
     *
     * @param ExhibitionCategory $category
     * @return void
     */
    public function restored(ExhibitionCategory $category)
    {
        DataUpdated::dispatch($category, $category->getAttribute('id'), '삭제된 전시관리 카테고리 복원');
    }

    /**
     * Handle the ExhibitionCategory "force deleted" event.
     *
     * @param ExhibitionCategory $category
     * @return void
     */
    public function forceDeleted(ExhibitionCategory $category)
    {
        DataDeleted::dispatch($category, $category->getAttribute('id'), '전시관리 카테고리 영구 삭제');
    }
}
