<?php

namespace Tests\Feature\Components;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentVersion;
use App\Models\Solution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentOptionTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    private Collection|Model $solution;

    public function setUp(): void
    {
        parent::setUp();
    }

    protected function createResource()
    {
        return [
            'component_type_id' => $this->createOptionType()->getAttribute('id'),
            'name' => $this->faker->text(10),
            'key' => $this->faker->text(10),
            'hideable' => true
        ];
    }

    protected function createComponent($user)
    {
        return Component::factory()->for(
            $user->partner,
            'creator'
        )->for(
            Solution::factory()->create(),
            'solution'
        )->create();
    }

    protected function createComponentVersion($user, $usable = 1)
    {
        return ComponentVersion::factory()->for(
            $this->createComponent($user),
            'component'
        )->state([
            'usable' => $usable
        ])->create();
    }

    protected function createOptionType()
    {
        return ComponentType::factory()->create();
    }

    protected function createOption($user)
    {
        return ComponentOption::factory()->for(
            $this->createComponentVersion($user),
            'version'
        )->for(
            $this->createOptionType(),
            'type'
        )->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $version = $this->createComponentVersion($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('post', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id') . '/option', $this->createResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $version = $this->createComponentVersion($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('post', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id') . '/option', $this->createResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $option = $this->createOption($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option/' . $option->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $option = $this->createOption($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option/' . $option->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $option = $this->createOption($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $option = $this->createOption($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $option = $this->createOption($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option/' . $option->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $option = $this->createOption($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $option->version->getAttribute('component_id') . '/version/' . $option->getAttribute('component_version_id') . '/option/' . $option->getAttribute('id'));
        $response->assertNoContent();
    }



}
