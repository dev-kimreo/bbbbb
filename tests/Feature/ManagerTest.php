<?php

namespace Tests\Feature;

use App\Models\Authority;
use App\Models\Manager;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'userId' => $this->createAsQpickUser('regular')->id,
            'authorityId' => Authority::factory()->create()->id
        ];
    }

    protected function createManager()
    {
        return Manager::create([
            'user_id' => $this->createAsQpickUser('regular')->id,
            'authority_id' => Authority::factory()->create()->id
        ]);
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/manager', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/manager', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/manager', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/manager', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $manager = $this->createManager();

        $response = $this->requestQpickApi('get', '/v1/manager/' . $manager->id);
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/manager/' . $manager->id);
        $response->assertForbidden();
    }

    public function testShowByRegular()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/manager/' . $manager->id);
        $response->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/manager/' . $manager->id);
        $response->assertOk();
    }

    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/manager');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/manager');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/manager');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/manager');
        $response->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $manager = $this->createManager();

        $response = $this->requestQpickApi('patch', '/v1/manager/' . $manager->id . '?' . Arr::query(['authorityId' => Authority::factory()->create()->id]));
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/manager/' . $manager->id . '?' . Arr::query(['authorityId' => Authority::factory()->create()->id]));
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/manager/' . $manager->id . '?' . Arr::query(['authorityId' => Authority::factory()->create()->id]));
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/manager/' . $manager->id . '?' . Arr::query(['authorityId' => Authority::factory()->create()->id]));
        $response->assertCreated();
    }

    /**
     * destroy
     */
    public function testDestroyByGuest()
    {
        $manager = $this->createManager();

        $response = $this->requestQpickApi('delete', '/v1/manager/' . $manager->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/manager/' . $manager->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/manager/' . $manager->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $manager = $this->createManager();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/manager/' . $manager->id);
        $response->assertNoContent();
    }

}
