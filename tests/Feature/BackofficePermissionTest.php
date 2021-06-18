<?php

namespace Tests\Feature;

use App\Models\Authority;
use App\Models\BackofficeMenu;
use App\Models\BackofficePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class BackofficePermissionTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'authorityId' => $this->createAuthority()->id,
            'backofficeMenuId' => $this->createMenu()->id
        ];
    }

    protected function createAuthority()
    {
        return Authority::factory()->create();
    }

    protected function createMenu($parent = null, $sort = null)
    {
        return BackofficeMenu::factory()->create();
    }

    protected function createPermission()
    {
        return BackofficePermission::create([
            'authority_id' => $this->createAuthority()->id,
            'backoffice_menu_id' => $this->createMenu()->id
        ]);
    }


    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/backoffice-permission', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/backoffice-permission', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/backoffice-permission', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/backoffice-permission', $this->createResource);
        $response->assertCreated();
    }

    /**
     * show
     */
    public function testShowByGuest()
    {
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission/' . $permission->id);
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission/' . $permission->id);
        $response->assertForbidden();
    }

    public function testShowByRegular()
    {
        $this->actingAsQpickUser('regular');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission/' . $permission->id);
        $response->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission/' . $permission->id);
        $response->assertOk();
    }

    /**
     * index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/backoffice-permission');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/backoffice-permission');
        $response->assertOk();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-permission/' . $permission->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-permission/' . $permission->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $this->actingAsQpickUser('regular');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-permission/' . $permission->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $permission = $this->createPermission();

        $response = $this->requestQpickApi('delete', '/v1/backoffice-permission/' . $permission->id);
        $response->assertNoContent();
    }




}
