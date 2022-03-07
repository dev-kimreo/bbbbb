<?php

namespace Tests\Feature\UserThemes;

use App\Models\Users\User;
use App\Models\UserThemes\UserTheme;
use App\Models\UserThemes\UserThemeSaveHistory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class SaveHistoryTest extends TestCase
{
    use QpickTestBase, WithFaker, DatabaseTransactions;

    protected array $structureShow = ['id', 'userThemeId', 'data', 'createdAt'];
    protected array $structureList = [['id', 'userThemeId', 'data', 'createdAt']];

    protected function getUserThemeFactory(?User $user = null)
    {
        //$userThemeId = UserTheme::factory()->create()->id;
        return UserTheme::create(
            [
                'user_id' => $user ? $user->id : User::query()->first()->id,
                'theme_id' => 1,
                'name' => $this->faker->name()
            ]
        );
    }

    protected function getFactory(UserTheme $userTheme)
    {
        return UserThemeSaveHistory::factory()
            ->for($userTheme, 'userTheme');
    }

    protected function getResponseCreate(?User $user = null): TestResponse
    {
        $userTheme = $this->getUserThemeFactory($user);

        return $this->requestQpickApi('post', '/v1/user-theme/' . $userTheme->id . '/save-history', [
            'data' => '{"test":"test"}'
        ]);
    }

    protected function getResponseList(?User $user = null)
    {
        $userTheme = $this->getUserThemeFactory($user);

        for ($i = 0; $i <= 3; $i++) {
            $this->getFactory($userTheme)->create();
        }

        return $this->requestQpickApi('get', '/v1/user-theme/' . $userTheme->id . '/save-history', []);
    }

    protected function getResponseShow(?User $user = null): TestResponse
    {
        $userTheme = $this->getUserThemeFactory($user);
        $saveHistory = $this->getFactory($userTheme)->create();

        return $this->requestQpickApi(
            'get',
            '/v1/user-theme/' . $userTheme->id . '/save-history/' . $saveHistory->id,
            []
        );
    }

    protected function getResponseDelete(?User $user = null): TestResponse
    {
        $userTheme = $this->getUserThemeFactory($user);
        $saveHistory = $this->getFactory($userTheme)->create();

        return $this->requestQpickApi(
            'delete',
            '/v1/user-theme/' . $userTheme->id . '/save-history/' . $saveHistory->id,
            []
        );
    }

    public function testCreateSaveHistoryByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreateSaveHistoryByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testCreateSaveHistoryByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testCreateSaveHistoryByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseCreate($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testListSaveHistoryByGuest()
    {
        $response = $this->getResponseList();
        $response->assertUnauthorized();
    }

    public function testListSaveHistoryByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListSaveHistoryByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListSaveHistoryByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testShowSaveHistoryByGuest()
    {
        $response = $this->getResponseShow();
        $response->assertUnauthorized();
    }

    public function testShowSaveHistoryByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowSaveHistoryByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowSaveHistoryByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testDeleteSaveHistoryByGuest()
    {
        $response = $this->getResponseDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteSaveHistoryByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteSaveHistoryByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteSaveHistoryByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }
}
