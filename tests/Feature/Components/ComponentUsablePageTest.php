<?php

namespace Tests\Feature\Components;

use App\Models\Components\Component;
use App\Models\Components\ComponentUsablePage;
use App\Models\Solution;
use App\Models\SupportedEditablePage;
use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentUsablePageTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    private Collection|Model $solution;

    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = [];
    }

    protected function createComponentUsablePage()
    {
        $this->createSolution();

        return ComponentUsablePage::factory()->for(
            $this->createComponent(),
            'component'
        )->for(
            $this->createSupportedEditablePage(),
            'supportedEditablePage'
        )->create();
    }

    protected function createSolution()
    {
        return $this->solution = Solution::factory()->create();
    }

    protected function createComponent()
    {
        return Component::factory()->for(
            $this->createAsQpickUser('partner')->partner,
            'creator'
        )->for(
            $this->solution,
            'solution'
        )->create();
    }

    protected function createSupportedEditablePage()
    {
        return SupportedEditablePage::factory()->for(
            $this->solution,
            'solution'
        )->create();
    }


    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $this->createSolution();
        $component = $this->createComponent();
        $supportedEditablePage = $this->createSupportedEditablePage();

        $response = $this->requestQpickApi('post', '/v1/component-usable-page', ['component_id' => $component->getAttribute('id'), 'supported_editable_page_id' => $supportedEditablePage->getAttribute('id')]);
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $this->createSolution();
        $component = $this->createComponent();
        $supportedEditablePage = $this->createSupportedEditablePage();
        $user = $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('post', '/v1/component-usable-page', ['component_id' => $component->getAttribute('id'), 'supported_editable_page_id' => $supportedEditablePage->getAttribute('id')]);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $page = $this->createComponentUsablePage();

        $response = $this->requestQpickApi('get', '/v1/component-usable-page/' . $page->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $page = $this->createComponentUsablePage();
        $user = $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('get', '/v1/component-usable-page/' . $page->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $page = $this->createComponentUsablePage();

        $response = $this->requestQpickApi('get', '/v1/component-usable-page?' . Arr::query(['component_id' => $page->getAttribute('component_id'), 'supported_editable_page_id' => $page->getAttribute('supported_editable_page_id')]));
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $page = $this->createComponentUsablePage();

        $response = $this->requestQpickApi('get', '/v1/component-usable-page?' . Arr::query(['component_id' => $page->getAttribute('component_id'), 'supported_editable_page_id' => $page->getAttribute('supported_editable_page_id')]));
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $page = $this->createComponentUsablePage();

        $response = $this->requestQpickApi('delete', '/v1/component-usable-page/' . $page->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $page = $this->createComponentUsablePage();

        $response = $this->requestQpickApi('delete', '/v1/component-usable-page/' . $page->getAttribute('id'));
        $response->assertNoContent();
    }


}
