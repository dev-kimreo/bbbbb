<?php

namespace Tests\Feature\Components;

use App\Models\Components\Component;
use App\Models\Components\ComponentVersion;
use App\Models\Solution;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Illuminate\Testing\Assert as PHPUnit;
use Tests\TestCase;

class ComponentVersionTest extends TestCase
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

    /**
     * Create 06701150248589 24120
     */
    public function testCreateByGuest()
    {
        $component = $this->createComponent($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('post', '/v1/component/' . $component->getAttribute('id') . '/version', $this->createResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $component = $this->createComponent($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('post', '/v1/component/' . $component->getAttribute('id') . '/version', $this->createResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $version = $this->createComponentVersion($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $version = $this->createComponentVersion($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $version = $this->createComponentVersion($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $version->getAttribute('component_id') . '/version');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $version = $this->createComponentVersion($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('get', '/v1/component/' . $version->getAttribute('component_id') . '/version');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $version = $this->createComponentVersion($this->createAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testUsableDestroyByPartner()
    {
        $version = $this->createComponentVersion($this->actingAsQpickUser('partner'));

        $response = $this->requestQpickApi('delete', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id'));
        $this->assertSame(json_decode($response->content())->errors[0]->code, 'component_version.disable.destroy.in_use', 'miss error code');
        $response->assertStatus(422);
    }

    public function testUnUsableDestroyByPartner()
    {
        $version = $this->createComponentVersion($this->actingAsQpickUser('partner'), 0);

        $response = $this->requestQpickApi('delete', '/v1/component/' . $version->getAttribute('component_id') . '/version/' . $version->getAttribute('id'));
        $response->assertNoContent();
    }


}
