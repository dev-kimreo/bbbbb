<?php

namespace Tests\Feature\Components;

use App\Models\Components\ComponentType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentTypeTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;


    public function setUp(): void
    {
        parent::setUp();
    }

    protected function createResource()
    {
        return [
            'name' => $this->faker->text(10)
        ];
    }

    protected function createOptionType()
    {
        return ComponentType::factory()->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/component-type', $this->createResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('post', '/v1/component-type', $this->createResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $type = $this->createOptionType();

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $type->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $type = $this->createOptionType();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $type->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $type = $this->createOptionType();

        $response = $this->requestQpickApi('get', '/v1/component-type');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $type = $this->createOptionType();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('get', '/v1/component-type');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $type = $this->createOptionType();

        $response = $this->requestQpickApi('delete', '/v1/component-type/' . $type->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $type = $this->createOptionType();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('delete', '/v1/component-type/' . $type->getAttribute('id'));
        $response->assertNoContent();
    }



}
