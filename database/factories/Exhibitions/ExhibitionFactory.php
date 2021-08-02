<?php

namespace Database\Factories\Exhibitions;

use App\Models\Exhibitions\Exhibition;
use App\Models\Users\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExhibitionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Exhibition::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'target_opt' => Exhibition::$targetOpt[array_rand(Exhibition::$targetOpt)],
            'target_grade' => function (array $attributes) {
                if ($attributes['target_opt'] == 'grade') {
                    return [User::$userGrade[array_rand(User::$userGrade)]];
                }

                return null;
            },
            'started_at' => Carbon::now(),
            'ended_at' => Carbon::now()->addDays(15)
        ];
    }

    /**
     * @param $targetOpt
     * @return ExhibitionFactory
     */
    public function setTargetOpt($targetOpt)
    {
        return $this->state(function (array $attributes) use ($targetOpt) {
            if(in_array($targetOpt, Exhibition::$targetOpt)) {
                return [
                    'target_opt' => $targetOpt,
                    'target_grade' => $targetOpt == 'grade'? [User::$userGrade[array_rand(User::$userGrade)]]: null
                ];
            } else {
                return [];
            }
        });
    }
}
