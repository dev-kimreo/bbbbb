<?php

namespace Tests\Feature\ThemeProducts;

use App\Models\Themes\ThemeProduct;
use App\Models\Themes\ThemeProductInformation;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ThemeProductInformationTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'description' => $this->faker->text(16)
        ];
    }

    protected function createThemeProduct(int $userPartnerId, bool $createInformation = false): Model|Collection
    {
        if ($createInformation) {
            return ThemeProduct::factory()->has(
                    ThemeProductInformation::factory(),
                    'themeInformation'
                )->create([
                'user_partner_id' => $userPartnerId
            ]);
        } else {
            return ThemeProduct::factory()->create([
                'user_partner_id' => $userPartnerId
            ]);
        }
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('post', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'), true);
        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information');
        $response->assertUnauthorized();
    }

    public function testIndexByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information');
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'), true);

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '/information/' . $themeProduct->themeInformation->getAttribute('id'));
        $response->assertNoContent();
    }


}
