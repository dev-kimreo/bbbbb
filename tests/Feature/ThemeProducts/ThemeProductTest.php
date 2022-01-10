<?php

namespace Tests\Feature\ThemeProducts;

use App\Models\Themes\ThemeProduct;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ThemeProductTest extends TestCase
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

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/theme-product', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/theme-product', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/theme-product', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/theme-product', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByPartner()
    {
        $this->actingAsQpickUser('partner');

        $response = $this->requestQpickApi('post', '/v1/theme-product', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testShowByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product/' . $themeProduct->getAttribute('id'));
        $response->assertOk();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/theme-product');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/theme-product');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/theme-product');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/theme-product');
        $response->assertForbidden();
    }

    public function testIndexByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('get', '/v1/theme-product');
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertUnauthorized();
    }

    public function testUpdateByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('patch', '/v1/theme-product/' . $themeProduct->getAttribute('id') . '?' . Arr::query($this->updateResource));
        $response->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $themeProduct = $this->createThemeProduct($this->createAsQpickUser('partner')->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $themeProduct->getAttribute('id'));
        $response->assertUnauthorized();
    }

    public function testDestroyByPartner()
    {
        $user = $this->actingAsQpickUser('partner');
        $themeProduct = $this->createThemeProduct($user->partner->getAttribute('id'));

        $response = $this->requestQpickApi('delete', '/v1/theme-product/' . $themeProduct->getAttribute('id'));
        $response->assertNoContent();
    }





}
