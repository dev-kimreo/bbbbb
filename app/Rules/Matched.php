<?php

namespace App\Rules;

use App\Exceptions\QpickHttpException;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Matched implements Rule
{
    protected string $table;
    protected string $targetValue;
    protected string $targetColumn;
    protected string $targetColumnName;

    /**
     * Create a new rule instance.
     *
     * @return void
     * @throws QpickHttpException
     */
    public function __construct(string $table, string $targetColumn, int $targetValue, string $targetColumnName)
    {
        $this->table = $table;
        $this->targetValue = $targetValue;
        $this->targetColumn = $targetColumn;
        $this->targetColumnName = $targetColumnName;
    }

    /**
     * @param string $table
     * @param int $pkValue
     * @return Collection
     * @throws QpickHttpException
     */
    protected function getRow(string $table, int $pkValue): Collection
    {
        if (!Str::contains($table, '\\') || !class_exists($table) || !is_subclass_of($table, Model::class)) {
            throw new QpickHttpException(500, 'common.not_found_model');
        }

        $model = new $table;
        return collect($model->find($pkValue));
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value): bool
    {
        return $this->targetValue == $this->getRow($this->table, $value)->get($this->targetColumn);
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return __('exception.common.not_matched_target_column', ['targetColumnName' => $this->targetColumnName]);
    }
}
