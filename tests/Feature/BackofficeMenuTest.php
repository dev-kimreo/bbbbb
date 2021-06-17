<?php

namespace Tests\Feature;

use App\Models\BackofficeMenu;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class BackofficeMenuTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = [
            'name' => $this->faker->text(16),
            'parent' => 0,
            'sort' => 0
        ];

        $this->updateResource = [
            'name' => $this->faker->text(16),
            'sort' => 100
        ];
    }

    protected function createMenu($parent = null, $sort = null)
    {
        return BackofficeMenu::factory()->create([
            'parent' => $parent ?? 0,
            'sort' => $sort ?? 0
        ]);
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/backoffice-menu', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/backoffice-menu', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/backoffice-menu', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/backoffice-menu', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu/' . $menu->id);
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu/' . $menu->id);
        $response->assertForbidden();
    }

    public function testShowByRegular()
    {
        $this->actingAsQpickUser('regular');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu/' . $menu->id);
        $response->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu/' . $menu->id);
        $response->assertOk();
    }

    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/backoffice-menu');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/backoffice-menu');
        $response->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('patch', '/v1/backoffice-menu/' . $menu->id . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('patch', '/v1/backoffice-menu/' . $menu->id . '?' . Arr::query($this->updateResource));
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $this->actingAsQpickUser('regular');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('patch', '/v1/backoffice-menu/' . $menu->id . '?' . Arr::query($this->updateResource));
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('patch', '/v1/backoffice-menu/' . $menu->id . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-menu/' . $menu->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-menu/' . $menu->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $this->actingAsQpickUser('regular');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-menu/' . $menu->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $menu = $this->createMenu();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-menu/' . $menu->id);
        $response->assertNoContent();
    }

    public function testDestroyExistsChildByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $menu = $this->createMenu();
        $childMenu = $this->createMenu($menu->id);

        $response = $this->requestQpickApi('delete', '/v1/backoffice-menu/' . $menu->id);
        $response->assertStatus(422);
    }


}
