<?php

namespace Tests\Feature;

use App\Models\Tooltip;
use App\Models\TranslationContent;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Str;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class TooltipTest extends TestCase
{
    use WithFaker, DatabaseTransactions, QpickTestBase;


    protected array $createResource = [];
    protected array $updateResource = [];

    public function getReqJson(): array
    {
        return [
            'title' => $this->faker->realText(30),
            'type' => collect(Tooltip::$prefixes)->random(1)->pop(),
            'visible' => rand(0, 1),
            'content' => [
                'ko' => $this->faker->realText(30),
                'en' => $this->faker->realText(30)
            ]
        ];
    }

    protected function createTooltip($user)
    {
        $res = [];
        $res['user_id'] = $user->id;

        $tooltip = Tooltip::factory()
            ->hasTranslation(1)
            ->create($res ?? []);

        TranslationContent::factory()->create([
            'translation_id' => $tooltip->translation()->first()->id
        ]);

        return $tooltip;
    }


    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertCreated();
    }


    /**
     * Show
     */
    public function testShowByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . $tooltip->id);
        $response->assertOk();
    }

    public function testShowByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . $tooltip->id);
        $response->assertOk();
    }

    public function testShowByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . $tooltip->id);
        $response->assertOk();
    }

    public function testShowByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . $tooltip->id);
        $response->assertOk();
    }

    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/tooltip/');
        $response->assertOk();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/tooltip/');
        $response->assertOk();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/tooltip/');
        $response->assertOk();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/tooltip/');
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . $tooltip->id, $this->getReqJson());
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . $tooltip->id, $this->getReqJson());
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . $tooltip->id, $this->getReqJson());
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . $tooltip->id, $this->getReqJson());
        $response->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . $tooltip->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . $tooltip->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . $tooltip->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $tooltip = $this->createTooltip($user);

        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . $tooltip->id);
        $response->assertNoContent();
    }

}
