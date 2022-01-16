<?php

namespace Tests\Feature\Themes;

use App\Models\Solution;
use App\Models\Themes\Theme;
use App\Models\Themes\ThemeProduct;
use App\Models\Users\UserPartner;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ThemeTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
        ];
    }

    protected function setCreateResource()
    {
        $this->createResource['solution_id'] = Solution::query()->offset(1)->first()->getAttribute('id');
        return $this->createResource;
    }

    protected function createSolution()
    {
        return Solution::factory()->create();
    }

    protected function createThemeProduct(int $partnerId)
    {
        return ThemeProduct::factory()->state([
            'user_partner_id' => $partnerId
        ])->create();
    }

    protected function createTheme(int $partnerId)
    {
        return Theme::factory()->for(
            Solution::factory(),
            'solution'
        )->for(
            ThemeProduct::factory()->state([
                'user_partner_id' => $partnerId
            ]),
            'product'
        )->create();
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/theme', $this->setCreateResource());
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/theme', $this->setCreateResource());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $theme = $this->createTheme($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $theme = $this->createTheme($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $theme = $this->createTheme($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $theme = $this->createTheme($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme');
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $theme = $this->createTheme($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $theme = $this->createTheme($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $theme = $this->createTheme($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $theme = $this->createTheme($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $theme->product->getAttribute('id') . '/theme/' . $theme->getAttribute('id'));
        $response->assertNoContent();
    }


    /**
     * Non-CRUD
     */
    public function testRelationalStoreByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/relational-theme', $this->setCreateResource());
        $response->assertUnauthorized();
    }

    public function testRelationalStoreByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/relational-theme', $this->setCreateResource());
        $response->assertCreated();
    }
}
