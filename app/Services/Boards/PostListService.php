<?php

namespace App\Services\Boards;

use App\Libraries\StringLibrary;
use App\Models\Post;
use App\Services\CommonListService;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;

class PostListService extends CommonListService
{
    protected array $sortableColumns = ['id', 'sort'];

    public function initQuery(): QueryBuilder
    {
        return DB::table('posts')
            ->select('posts.id')
            ->whereNull('posts.deleted_at')
            ->leftJoin('user_privacy_active', 'user_privacy_active.user_id', '=', 'posts.user_id');
    }

    public function initModel(): EloquentBuilder
    {
        return Post::query();
    }

    public function initDataStructure(): array
    {
        return [
            'onBoard' => [
                'with' => ['user', 'thumbnail.attachFiles'],
                'withCount' => ['replies'],
                'append' => []
            ],
            'total' => [
                'with' => ['user', 'board'],
                'withCount' => ['replies', 'attachFiles'],
                'append' => []
            ]
        ];
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
}
