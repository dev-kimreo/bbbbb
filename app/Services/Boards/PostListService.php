<?php

namespace App\Services\Boards;

use App\Exceptions\QpickHttpException;
use App\Libraries\CollectionLibrary;
use App\Libraries\StringLibrary;
use App\Models\Post;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;

class PostListService
{
    protected Builder $query;
    protected array $sortableColumns = ['id', 'sort'];

    public function __construct()
    {
        $this->query = DB::table('posts')
            ->select('posts.id')
            ->whereNull('posts.deleted_at')
            ->leftJoin('user_privacy_active', 'user_privacy_active.user_id', '=', 'posts.user_id');
    }

    /*
    public function __call(string $name, array $arguments)
    {
        $this->query->$name(...$arguments);
    }
    */

    public static function query(): PostListService
    {
        return new static();
    }

    /**
     * @param array|string $column
     * @param $value
     * @return PostListService
     */
    public function where($column, $value = null): PostListService
    {
        collect(is_array($column) ? $column : [$column => $value])
            ->each(function ($v, $k) {
                if (!$v) {
                    return;
                }

                switch ($k) {
                    case 'board_id':
                        $this->query->where('posts.board_id', $v);
                        break;

                    case 'hidden':
                        if (!is_array($v)) {
                            $this->query->where('posts.hidden', $v);
                        } elseif (!in_array(null, $v)) {
                            $this->query->whereIn('posts.hidden', $v);
                        }
                        break;

                    case 'email':
                        $v = StringLibrary::escapeSql($v);
                        $this->query->where('user_privacy_active.email', 'like', '%' . $v . '%');
                        break;

                    case 'post_id':
                        $this->query->where('posts.id', $v);
                        break;

                    case 'name':
                        $this->query->where('user_privacy_active.name', $v);
                        break;

                    case 'title':
                        $v = StringLibrary::escapeSql($v);
                        $this->query->where('posts.title', 'like', '%' . $v . '%');
                        break;

                    case 'start_created_date':
                        $this->query->where('posts.created_at', '>=', Carbon::parse($v));
                        break;

                    case 'end_created_date':
                        $v = Carbon::parse($v)->setTime(23, 59, 59);
                        $this->query->where('posts.created_at', '<=', $v);
                        break;

                    case 'multi_search':
                        $this->query->where(function ($q) use ($v) {
                            $q->orWhere('user_privacy_active.name', $v);

                            if (is_numeric($v)) {
                                $q->orWhere('posts.id', $v);
                            }
                        });
                        break;
                }
            });

        return $this;
    }

    /**
     * @param Collection|string $sort
     * @return $this
     * @throws QpickHttpException
     */
    public function sort($sort): PostListService
    {
        if (is_string($sort)) {
            $sort = CollectionLibrary::getBySort($sort, $this->sortableColumns);
        } elseif (is_array($sort)) {
            $sort = collect(array_intersect_key($sort, array_flip($this->sortableColumns)));
        } else {
            $sort = collect([]);
        }

        if ($sort->isNotEmpty()) {
            $sort->each(function ($v) {
                $this->query->orderBy($v['key'], $v['value']);
            });
        } else {
            $this->query->orderByDesc('id');
        }

        return $this;
    }

    /**
     * @param int $length
     * @return PostListService
     */
    public function skip(int $length): PostListService
    {
        $this->query->skip($length);
        return $this;
    }

    /**
     * @param int $length
     * @return PostListService
     */
    public function take(int $length): PostListService
    {
        $this->query->take($length);
        return $this;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->query->count();
    }

    /**
     * @param string $mode
     * @return Collection
     */
    public function get(string $mode): Collection
    {
        switch ($mode) {
            default:
            case 'onBoard':
                $with = ['user', 'thumbnail.attachFiles'];
                $withCount = ['replies'];
                $append = [];
                break;

            case 'total':
                $with = ['user', 'board'];
                $withCount = ['replies', 'attachFiles'];
                $append = [];
                break;
        }

        $keys = $this->query->get()->pluck('id');

        return Post::query()
            ->with($with)
            ->withCount($withCount)
            ->whereIn('id', $keys)
            ->get()
            ->append($append)
            ->sortBy(function ($v) use ($keys) {
                return $keys->search($v->id);
            })
            ->values();
    }
}
