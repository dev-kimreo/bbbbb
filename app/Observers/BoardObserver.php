<?php

namespace App\Observers;

use App\Events\Backoffice\DataCreated;
use App\Events\Backoffice\DataDeleted;
use App\Events\Backoffice\DataUpdated;
use App\Models\Boards\Board;

class BoardObserver
{
    /**
     * Handle the Board "created" event.
     *
     * @param Board $board
     * @return void
     */
    public function created(Board $board)
    {
        DataCreated::dispatch($board, $board->getAttribute('id'), '게시판 생성');
    }

    /**
     * Handle the Board "updated" event.
     *
     * @param Board $board
     * @return void
     */
    public function updated(Board $board)
    {
        DataUpdated::dispatch($board, $board->getAttribute('id'), '게시판 정보수정');
    }

    /**
     * Handle the Board "deleted" event.
     *
     * @param Board $board
     * @return void
     */
    public function deleted(Board $board)
    {
        DataDeleted::dispatch($board, $board->getAttribute('id'), '게시판 삭제');
    }

    /**
     * Handle the Board "restored" event.
     *
     * @param Board $board
     * @return void
     */
    public function restored(Board $board)
    {
        DataUpdated::dispatch($board, $board->getAttribute('id'), '삭제된 게시판 복구');
    }

    /**
     * Handle the Board "force deleted" event.
     *
     * @param Board $board
     * @return void
     */
    public function forceDeleted(Board $board)
    {
        DataDeleted::dispatch($board, $board->getAttribute('id'), '게시판 삭제');
    }
}
