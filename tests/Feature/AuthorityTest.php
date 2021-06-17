<?php

namespace Tests\Feature;

use App\Models\Authority;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class AuthorityTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'code' => $this->faker->unique(true)->numberBetween(1, 1000),
            'title' => $this->faker->realText(16),
            'displayName' => $this->faker->realText(10),
            'memo' => $this->faker->realText(10)
        ];
    }

    protected function createAuthority()
    {
        return Authority::create($this->createResource);
    }


    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/authority', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/authority', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/authority', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/authority', $this->createResource);
        $response->assertOk();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id);
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id);
        $response->assertForbidden();
    }

    public function testShowByRegular()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id);
        $response->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id);
        $response->assertOk();
    }

    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/authority');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/authority');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/authority');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/authority');
        $response->assertOk();
    }

    /**
     * update
     */
    public function testUpdateByGuest()
    {
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('patch', '/v1/authority/' . $authority->id . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/authority/' . $authority->id . '?' . Arr::query($this->updateResource));
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/authority/' . $authority->id . '?' . Arr::query($this->updateResource));
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $authority = $this->createAuthority();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/authority/' . $authority->id . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('delete', '/v1/authority/' . $authority->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('delete', '/v1/authority/' . $authority->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $this->actingAsQpickUser('regular');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('delete', '/v1/authority/' . $authority->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('delete', '/v1/authority/' . $authority->id);
        $response->assertNoContent();
    }

    /**
     * Non-CRUD
     */
    public function testGetMenuListWithPermissionByGuest()
    {
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id . '/menu-permission');
        $response->assertUnauthorized();
    }

    public function testGetMenuListWithPermissionByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id . '/menu-permission');
        $response->assertForbidden();
    }

    public function testGetMenuListWithPermissionByRegular()
    {
        $this->actingAsQpickUser('regular');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id . '/menu-permission');
        $response->assertForbidden();
    }

    public function testGetMenuListWithPermissionByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $authority = $this->createAuthority();

        $response = $this->requestQpickApi('get', '/v1/authority/' . $authority->id . '/menu-permission');
        $response->assertOk();
    }

}
