<?php

namespace App\Services\Boards;

use App\Exceptions\QpickHttpException;
use App\Models\Boards\Board;
use App\Models\BoardOption;
use Cache;
use Illuminate\Support\Collection;

class BoardService
{
    private Board $board;
    private BoardOption $boardOpt;

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
            return $this->boardOpt->query()
                ->select($set['sel'] ?? '*')
                ->orderBy('sort')
                ->orderBy('id')
                ->get();
        });
    }

    /**
     * @param $type
     * @param $requestKey
     * @return BoardOption
     * @throws QpickHttpException
     */
    public function getOptionByType($type, $requestKey): BoardOption
    {
        $tags = separateTag('board.options.info');
        $ttl = config('cache.custom.expire.common');

        return Cache::tags($tags)->remember($type, $ttl, function () use ($type, $requestKey) {
            if (!$data = $this->boardOpt::getByType($type)->first()) {
                throw new QpickHttpException(422, 'board.option.disable.unknown_key', $requestKey);
            }

            return $data;
        });
    }

    /**
     * @param $boardId
     * @return Board
     * @throws QpickHttpException
     */
    public function getInfo($boardId): Board
    {
        $tags = separateTag('board.' . $boardId);
        $key = 'info';
        $ttl = config('cache.custom.expire.common');

        return Cache::tags($tags)->remember($key, $ttl, function () use ($boardId) {
            if (!$data = $this->board->with('user')->find($boardId)) {
                throw new QpickHttpException(404, 'common.not_found');
            }

            return $data;
        });
    }
}
