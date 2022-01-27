<?php

namespace Tests\Feature\Users;

use App\Models\Solution;
use App\Models\Users\User;
use App\Models\Users\UserPrivacyActive;
use App\Models\Users\UserSite;
use App\Models\Users\UserSolution;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class UserSiteTest extends TestCase
{
    use QpickTestBase, WithFaker, DatabaseTransactions;

    protected array $structureShow = [
        'id',
        'userId',
        'userSolutionId',
        'name',
        'url',
        'bizType',
        'createdAt',
        'updatedAt',
        'userSolution' => [
            'id',
            'userId',
            'solutionId',
            'solutionUserId',
            'apikey',
            'createdAt',
            'updatedAt',
            'solutionName',
            'solution' => [
                'id',
                'name',
                'createdAt',
                'updatedAt',
            ]
        ]
    ];

    protected array $structureList = [
        'header' => [
            'page',
            'perPage',
            'skip',
            'block',
            'perBlock',
            'totalCount',
            'totalPage',
            'totalBlock',
            'startPage',
            'endPage'
        ],
        'list' => [
            [
                'id',
                'userId',
                'userSolutionId',
                'name',
                'url',
                'bizType',
                'createdAt',
                'updatedAt',
                'userSolution' => [
                    'id',
                    'userId',
                    'solutionId',
                    'solutionUserId',
                    'apikey',
                    'createdAt',
                    'updatedAt',
                    'solutionName',
                    'solution' => [
                        'id',
                        'name',
                        'createdAt',
                        'updatedAt',
                    ]
                ]
            ]
        ]
    ];

    protected function getFactory(Model $user): Factory
    {
        return UserSite::factory()
            ->for(
                UserSolution::factory()
                    ->for(Solution::factory()->create())
                    ->for($user)
            )->for($user);
    }

    protected function getResponseCreate(Model $user): TestResponse
    {
        $userSolution = UserSolution::factory()
            ->for($user)
            ->for(Solution::factory())
            ->create();

        return $this->requestQpickApi('post', '/v1/user/' . $user->id . '/site', [
            'userSolutionId' => $userSolution->id,
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'bizType' => $this->faker->text(16)
        ]);
    }

    protected function getResponseIndex(Model $user): TestResponse
    {
        for ($i = 0; $i < 3; $i++) {
            $this->getFactory($user)->create();
        }

        return $this->requestQpickApi('get', '/v1/user/' . $user->id . '/site', []);
    }

    protected function getResponseShow(Model $user): TestResponse
    {
        $factory = $this->getFactory($user)->create();

        return $this->requestQpickApi('get', '/v1/user/' . $user->id . '/site/' . $factory->id, []);
    }

    protected function getResponseUpdate(Model $user): TestResponse
    {
        $factory = $this->getFactory($user)->create();

        return $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/site/' . $factory->id, [
            'name' => $this->faker->name(),
            'url' => $this->faker->url(),
            'bizType' => $this->faker->text(16)
        ]);
    }

    protected function getResponseDestroy(Model $user): TestResponse
    {
        $factory = $this->getFactory($user)->create();

        return $this->requestQpickApi('delete', '/v1/user/' . $user->id . '/site/' . $factory->id, []);
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->getResponseCreate(User::factory()->create());
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testCreateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testCreateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $response = $this->getResponseShow(User::factory()->create());
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->getResponseIndex(User::factory()->create());
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseIndex($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testIndexByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseIndex($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testIndexByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseIndex($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $response = $this->getResponseUpdate(User::factory()->create());
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseUpdate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseUpdate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseUpdate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $response = $this->getResponseDestroy(User::factory()->create());
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseDestroy($user);
        $response->assertNoContent();
    }

    public function testDestroyByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseDestroy($user);
        $response->assertNoContent();
    }

    public function testDestroyByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseDestroy($user);
        $response->assertNoContent();
    }
}
