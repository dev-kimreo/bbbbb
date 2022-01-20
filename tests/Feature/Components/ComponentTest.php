<?php

namespace Tests\Feature\Components;

use App\Models\Components\Component;
use App\Models\Solution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentTest extends TestCase
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
            'solution_id' => Solution::factory()->create()->getAttribute('id'),
            'name' => $this->faker->text(10),
            'first_category' => array_rand(Component::$firstCategory),
            'second_category' => array_rand(Component::$secondCategory),
            'use_blank' => rand(0, 1),
            'use_all_page' => rand(0, 1),
            'icon' => array_rand(Component::$icon),
            'display' => rand(0, 1),
            'status' => array_rand(Component::$status),
            'manager_memo' => $this->faker->realText(50)
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

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/component', $this->createResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('post', '/v1/component', $this->createResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $component = $this->createComponent($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $component->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $component = $this->createComponent($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $component->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $component = $this->createComponent($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $component = $this->createComponent($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $component = $this->createComponent($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $component->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $component = $this->createComponent($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $component->getAttribute('id'));
        $response->assertNoContent();
    }


}
