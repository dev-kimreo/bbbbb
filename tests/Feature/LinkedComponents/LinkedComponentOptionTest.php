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

class LinkedComponentOptionTest extends TestCase
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
            'component_option_id' => '',
            'value' => json_encode(['testAttribute' => 'testValue'])
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

    protected function createLinkedComponentOption($user)
    {
        $linkedComponent = $this->createLinkedComponent($user);

        return LinkedComponentOption::factory()->for(
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
        $linkedComponent = $this->createLinkedComponent($user);
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');
        $componentOptionId = $linkedComponent->getAttribute('component')->getAttribute('usableVersion')->first()->getAttribute('options')->first()->getAttribute('id');

        $resource = array_merge($this->createResource(), ['component_option_id' => $componentOptionId]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id') . '/option', $resource);
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $linkedComponent = $this->createLinkedComponent($user);
        $themeId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponent->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');
        $componentOptionId = $linkedComponent->getAttribute('component')->getAttribute('usableVersion')->first()->getAttribute('options')->first()->getAttribute('id');

        $resource = array_merge($this->createResource(), ['component_option_id' => $componentOptionId]);

        $response = $this->requestQpickApi('post', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponent->getAttribute('id') . '/option', $resource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->createAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option/' . $linkedComponentOption->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option/' . $linkedComponentOption->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->createAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('get', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option');
        $response->assertOk();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->createAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option/' . $linkedComponentOption->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $linkedComponentOption = $this->createLinkedComponentOption($this->actingAsQpickUser('partner'));
        $themeId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editablePage')->getAttribute('theme_id');
        $editablePageId = $linkedComponentOption->getAttribute('linkedComponent')->getAttribute('linkedComponentGroup')->getAttribute('usedForContent')->getAttribute('editable_page_id');

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $themeId . '/editable-page/' . $editablePageId . '/linked-component/' . $linkedComponentOption->getAttribute('linked_component_id') . '/option/' . $linkedComponentOption->getAttribute('id'));
        $response->assertNoContent();
    }

}
