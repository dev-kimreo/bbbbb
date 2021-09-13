<?php

namespace Tests\Feature;

use App\Models\Solution;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class SolutionTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = [
            'name' => $this->faker->text(16)
        ];

        $this->updateResource = [
            'name' => $this->faker->text(16)
        ];
    }

    protected function createSolution()
    {
        return Solution::factory()->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/solution', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/solution', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/solution', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/solution', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $solution = $this->createSolution();

        $response = $this->requestQpickApi('get', '/v1/solution/' . $solution->id);
        $response->assertOk();
    }

    public function testShowByAssociate()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/solution/' . $solution->id);
        $response->assertOk();
    }

    public function testShowByRegular()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/solution/' . $solution->id);
        $response->assertOk();
    }

    public function testShowByBackoffice()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/solution/' . $solution->id);
        $response->assertOk();
    }

    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/solution');
        $response->assertOk();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/solution');
        $response->assertOk();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/solution');
        $response->assertOk();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/solution');
        $response->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $solution = $this->createSolution();

        $response = $this->requestQpickApi('patch', '/v1/solution/' . $solution->id, $this->updateResource);
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/solution/' . $solution->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/solution/' . $solution->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/solution/' . $solution->id, $this->updateResource);
        $response->assertCreated();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $solution = $this->createSolution();

        $response = $this->requestQpickApi('delete', '/v1/solution/' . $solution->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/solution/' . $solution->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/solution/' . $solution->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $solution = $this->createSolution();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/solution/' . $solution->id);
        $response->assertNoContent();
    }

}
