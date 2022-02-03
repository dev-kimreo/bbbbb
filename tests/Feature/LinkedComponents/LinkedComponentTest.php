<?php

namespace Tests\Feature\LinkedComponents;

use App\Models\Components\Component;
use App\Models\Components\ComponentOption;
use App\Models\Components\ComponentType;
use App\Models\Components\ComponentVersion;
use App\Models\EditablePages\EditablePage;
use App\Models\EditablePages\EditablePageLayout;
use App\Models\LinkedComponents\LinkedComponent;
use App\Models\LinkedComponents\LinkedComponentGroup;
use App\Models\LinkedComponents\LinkedComponentOption;
use App\Models\Solution;
use App\Models\SupportedEditablePage;
use App\Models\Themes\Theme;
use App\Models\Themes\ThemeProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class LinkedComponentTest extends TestCase
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
            'linked_component_group_id' => '',
            'component_id' => '',
            'name' => $this->faker->text(10),
            'etc' => '',
            'display_on_pc' => rand(0, 1),
            'display_on_mobile' => rand(0, 1),
            'sort' => rand(1, 10),
        ];
    }

    protected function createEditablePageLayout($user)
    {
        $editablePage = $this->createEditablePage($user->getAttribute('partner')->getAttribute('id'));

        return EditablePageLayout::factory()->for(
            LinkedComponentGroup::factory(),
            'linkedHeaderComponentGroup'
        )->for(
            LinkedComponentGroup::factory(),
            'linkedContentComponentGroup'
        )->for(
            LinkedComponentGroup::factory(),
            'linkedFooterComponentGroup'
        )->state([
            'editable_page_id' => $editablePage->getAttribute('id')
        ])->create();
    }

    protected function createThemeProduct(int $userPartnerId): Model|Collection
    {
        return ThemeProduct::factory()->create([
            'user_partner_id' => $userPartnerId
        ]);
    }

    protected function createTheme(int $userPartnerId)
    {
        $this->getSolutionExistEditablePage();

        return Theme::factory()->state([
            'solution_id' => $this->createResource['solution_id'],
            'theme_product_id' => $this->createThemeProduct($userPartnerId)->getAttribute('id')
        ])->create();
    }

    protected function createEditablePage(int $userPartnerId)
    {
        $this->getSolutionExistEditablePage();

        $theme = $this->createTheme($userPartnerId);

        return EditablePage::factory()->state([
            'theme_id' => $theme->getAttribute('id'),
            'supported_editable_page_id' => $this->createResource['supported_editable_page_id'],
            'name' => $this->faker->text(16)
        ])->create();
    }

    protected function getSolutionExistEditablePage()
    {
        $supportedPage = SupportedEditablePage::first();
        $this->createResource['supported_editable_page_id'] = $supportedPage->getAttribute('id');
        $this->createResource['solution_id'] = $supportedPage->getAttribute('solution_id');
        return $supportedPage->getAttribute('solution_id');
    }

    protected function createComponent($user)
    {
        return Component::factory()->for(
            $user->partner,
            'creator'
        )->has(
            ComponentVersion::factory()->state(['usable' => true])->has(
                ComponentOption::factory()->for(
                    ComponentType::factory(),
                    'type'
                ),
                'options'
            ),
            'version'
        )->for(
            Solution::factory()->create(),
            'solution'
        )->create();
    }

    protected function createLinkedComponent($user)
    {
        return LinkedComponent::factory()->for(
            $this->createEditablePageLayout($user)->getAttribute('linkedContentComponentGroup'),
            'linkedComponentGroup'
        )->for(
            $this->createComponent($user),
            'component'
        )->state([
            'name' => $this->faker->text(10),
            'etc' => '',
            'display_on_pc' => rand(0, 1),
            'display_on_mobile' => rand(0, 1),
        ])->create();
    }

    protected function createOptionUnderLinkedComponent($linkedComponent)
    {
        LinkedComponentOption::factory()->for(
            $linkedComponent,
            'linkedComponent'
        )->for(
            $linkedComponent->getAttribute('component')->getAttribute('usableVersion')->first()->getAttribute('options')->first(),
            'componentOption'
        )->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $user = $this->createAsQpickUser('partner');
        $layout = $this->createEditablePageLayout($user);
        $component = $this->createComponent($user);

        $resource = array_merge($this->createResource(), ['linked_component_group_id' => $layout->getAttribute('content_component_group_id'), 'component_id' => $component->getAttribute('id')]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $layout->getAttribute('editablePage')->getAttribute('theme_id') . '/editable-page/' . $layout->getAttribute('editable_page_id') . '/linked-component', $resource);
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $layout = $this->createEditablePageLayout($user);
        $component = $this->createComponent($user);

        $resource = array_merge($this->createResource(), ['linked_component_group_id' => $layout->getAttribute('content_component_group_id'), 'component_id' => $component->getAttribute('id')]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $layout->getAttribute('editablePage')->getAttribute('theme_id') . '/editable-page/' . $layout->getAttribute('editable_page_id') . '/linked-component', $resource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $linkedComponent = $this->createLinkedComponent($this->createAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $linkedComponent = $this->createLinkedComponent($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $linkedComponent = $this->createLinkedComponent($this->createAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $linkedComponent = $this->createLinkedComponent($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $linkedComponent = $this->createLinkedComponent($this->createAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $linkedComponent = $this->createLinkedComponent($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertNoContent();
    }


    /**
     * Non-CRUD
     */
    public function testRelationLinkedComponentCreateByGuest()
    {
        $user = $this->createAsQpickUser('partner');
        $layout = $this->createEditablePageLayout($user);
        $component = $this->createComponent($user);

        $resource = array_merge($this->createResource(), ['linked_component_group_id' => $layout->getAttribute('content_component_group_id'), 'component_id' => $component->getAttribute('id')]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $layout->getAttribute('editablePage')->getAttribute('theme_id') . '/editable-page/' . $layout->getAttribute('editable_page_id') . '/relational-linked-component', $resource);
        $response->assertUnauthorized();
    }

    public function testRelationLinkedComponentCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $layout = $this->createEditablePageLayout($user);
        $component = $this->createComponent($user);

        $resource = array_merge($this->createResource(), ['linked_component_group_id' => $layout->getAttribute('content_component_group_id'), 'component_id' => $component->getAttribute('id')]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $layout->getAttribute('editablePage')->getAttribute('theme_id') . '/editable-page/' . $layout->getAttribute('editable_page_id') . '/relational-linked-component', $resource);
        $response->assertCreated();
    }


    public function testShowDirectlyByGuest()
    {
        $linkedComponent = $this->createLinkedComponent($this->createAsQpickUser('partner'));
        $this->createOptionUnderLinkedComponent($linkedComponent);

        $response = $this->requestQpickApi('get', '/v1/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowDirectlyByPartner()
    {
        $linkedComponent = $this->createLinkedComponent($this->actingAsQpickUser('partner'));
        $this->createOptionUnderLinkedComponent($linkedComponent);

        $response = $this->requestQpickApi('get', '/v1/linked-component/' . $linkedComponent->getAttribute('id'));
        $response->assertOk();
    }


}
