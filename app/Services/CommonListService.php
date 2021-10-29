<?php

namespace App\Services;

use App\Exceptions\QpickHttpException;
use App\Libraries\CollectionLibrary;
use DB;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

/**
 * @method skip(mixed $skip)
 * @method take(mixed $take)
 * @method groupBy(string $string)
 */
abstract class CommonListService
{
    protected string $tableName;
    protected QueryBuilder $query;
    protected EloquentBuilder $model;
    protected array $dataStructure = [];
    protected array $sortableColumns = [];

    abstract public function initQuery(): QueryBuilder;
    abstract public function initModel(): EloquentBuilder;
    abstract public function initDataStructure(): array;

    public function __construct()
    {
        $this->query = $this->initQuery();
        $this->model = $this->initModel();
        $this->dataStructure = $this->initDataStructure();
    }

    public static function query(): CommonListService
    {
        return new static();
    }

    /**
     * @param Collection|string $sort
     * @return $this
     * @throws QpickHttpException
     */
    public function sort($sort): CommonListService
    {
        $this->procSortData($sort)->each(function ($v) {
            $this->query->orderBy($v['key'], $v['value']);
        });

        return $this;
    }

    /**
     * @param Collection|string $sort
     * @return Collection
     * @throws QpickHttpException
     */
    protected function procSortData($sort): Collection
    {
        if (is_string($sort)) {
            $res = CollectionLibrary::getBySort($sort, $this->sortableColumns);
        } elseif (is_array($sort)) {
            $res = collect($sort)->intersectByKeys(array_flip($this->sortableColumns));
        } else {
            $res = collect([]);
        }

        return $res;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return $this->query->count();
    }

    public function select(string ...$field): CommonListService
    {
        $field = $this->appendTableNameToFieldName($field);
        $this->query->select($field);

        return $this;
    }

    /**
     * @param string ...$field
     * @return Collection
     */
    public function groupCount(string ...$field): Collection
    {
        $groupBy = $this->appendTableNameToFieldName($field);
        $select = array_merge($groupBy, [DB::raw('count(*) as groupCount')]);

        return $this->query->select($select)->groupBy($groupBy)->get();
    }

    /**
     * @param array $arr
     * @return array
     */
    protected function appendTableNameToFieldName(array $arr): array
    {
        foreach ($arr as &$v) {
            if (strpos($v, '.') === false) {
                $v = $this->tableName . '.' . $v;
            }
        }

        return $arr;
    }

    protected function getQuery(string $structureKey, $keys): EloquentBuilder
    {
        $with = $this->dataStructure[$structureKey]['with'] ?? [];
        $withCount = $this->dataStructure[$structureKey]['withCount'] ?? [];

        return $this->model
            ->with($with)
            ->withCount($withCount)
            ->whereIn('id', $keys);
    }

    /**
     * @param string $structureKey
     * @return Collection
     */
    public function get(string $structureKey): Collection
    {
        $keys = $this->query->get()->pluck('id');
        $append = $this->dataStructure[$structureKey]['append'] ?? [];
        $hidden = $this->dataStructure[$structureKey]['hidden'] ?? [];

        return $this->getQuery($structureKey, $keys)
            ->get()
            ->append($append)
            ->makeHidden($hidden)
            ->sortBy(function ($v) use ($keys) {
                return $keys->search($v->id);
            })
            ->values();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return $this
     */
    public function __call(string $name, array $arguments)
    {
        $this->query->$name(...$arguments);
        return $this;
    }
}
