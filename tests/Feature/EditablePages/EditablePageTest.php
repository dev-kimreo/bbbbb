<?php

namespace Tests\Feature\EditablePages;

use App\Models\EditablePages\EditablePage;
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

class EditablePageTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'name' => $this->faker->text(16)
        ];
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

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $theme = $this->createTheme($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme/' . $theme->getAttribute('id') . '/editable-page', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $theme = $this->createTheme($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme/' . $theme->getAttribute('id') . '/editable-page', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $editablePage = $this->createEditablePage($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $editablePage = $this->createEditablePage($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $editablePage = $this->createEditablePage($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $editablePage = $this->createEditablePage($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page');
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $editablePage = $this->createEditablePage($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $editablePage = $this->createEditablePage($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $editablePage = $this->createEditablePage($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $editablePage = $this->createEditablePage($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme/' . $editablePage->getAttribute('theme_id') . '/editable-page/' . $editablePage->getAttribute('id'));
        $response->assertNoContent();
    }




}
