<?php

namespace App\Services;

use Cache;
use App\Models\Board;
use App\Models\BoardOption;
use Illuminate\Support\Collection;
use App\Exceptions\QpickHttpException;

class BoardService
{
    private $board, $boardOpt;

    /**
     * BoardService constructor.
     */
    public function __construct(Board $board, BoardOption $boardOption)
    {
        $this->board = $board;
        $this->boardOpt = $boardOption;
    }


    /**
     * @param array $set
     * @return Collection
     */
    public function getOptionList(array $set = []): Collection
    {
        $tags = separateTag('board.options.list');

        $data = Cache::tags($tags)->remember(md5(json_encode($set)), config('cache.custom.expire.common'), function () use ($set) {
            $opt = $this->boardOpt;

            if (isset($set['sel'])) {
                $opt = $opt->select($set['sel']);
            }

            $opt = $opt->orderBy('sort', 'asc')->orderBy('id', 'asc')->get();

            return $opt;
        });

        return $data;
    }

    /**
     * @param $boardId
     * @return Collection
     */
    public function getInfo($boardId): Board
    {
        $tags = separateTag('board.' . $boardId);

        $data = Cache::tags($tags)->remember('info', config('cache.custom.expire.common'), function () use ($boardId) {
            $opt = $this->board->find($boardId);

            if (!$opt) {
                return false;
            }

            return $opt;
        });

        if (!$data) {
            Cache::tags($tags)->forget('info');
            throw new QpickHttpException(422, 100005);
        }

        return $data;
    }

}
