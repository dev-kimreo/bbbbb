<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Models\Boards\Board;
use App\Models\BoardOption;
use Cache;
use Illuminate\Support\Collection;

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
        $key = md5(json_encode($set));
        $ttl = config('cache.custom.expire.common');

        return Cache::tags($tags)->remember($key, $ttl, function () use ($set) {
            return $this->boardOpt
                ->select($set['sel'] ?? '*')
                ->orderBy('sort', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        });
    }

    /**
     * @param $type
     * @return BoardOption
     */
    public function getOptiontByType($type, $requestKey): BoardOption
    {
        $tags = separateTag('board.options.info');
        $ttl = config('cache.custom.expire.common');

        return Cache::tags($tags)->remember($type, $ttl, function () use ($type, $requestKey) {
            if(!$data = $this->boardOpt::getByType($type)->first()) {
                throw new QpickHttpException(422, 'board.option.disable.unknown_key', $requestKey);
            }

            return $data;
        });
    }

    /**
     * @param $boardId
     * @return Collection
     */
    public function getInfo($boardId): Board
    {
        $tags = separateTag('board.' . $boardId);
        $key = 'info';
        $ttl = config('cache.custom.expire.common');

        return Cache::tags($tags)->remember($key, $ttl, function () use ($boardId) {
            if(!$data = $this->board->with('user')->find($boardId)) {
                throw new QpickHttpException(404, 'common.not_found');
            }

            return $data;
        });
    }
}
