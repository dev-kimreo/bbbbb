<?php

namespace Tests\Feature\Components;

use App\Models\Components\ComponentType;
use App\Models\Components\ComponentTypeProperty;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentTypePropertyTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;


    public function setUp(): void
    {
        parent::setUp();
    }

    protected function createResource()
    {
        return [
            'type' => ComponentTypeProperty::$types[array_rand(ComponentTypeProperty::$types)]
        ];
    }

    protected function createType()
    {
        return ComponentType::factory()->create();
    }

    protected function createTypeProperty()
    {
        return ComponentTypeProperty::factory()->for(
            ComponentType::factory(),
            'componentType'
        )->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $type = $this->createType();

        $response = $this->requestQpickApi('post', '/v1/component-type/' . $type->getAttribute('id') . '/property', $this->createResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $type = $this->createType();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('post', '/v1/component-type/' . $type->getAttribute('id') . '/property', $this->createResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $property = $this->createTypeProperty();

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property/' . $property->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $property = $this->createTypeProperty();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property/' . $property->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $property = $this->createTypeProperty();

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $property = $this->createTypeProperty();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('get', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $property = $this->createTypeProperty();

        $response = $this->requestQpickApi('delete', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property/' . $property->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $property = $this->createTypeProperty();
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('delete', '/v1/component-type/' . $property->getAttribute('component_type_id') . '/property/' . $property->getAttribute('id'));
        $response->assertNoContent();
    }


}
