<?php

namespace Tests\Feature;

use App\Models\Users\User;
use App\Models\Widgets\Widget;
use App\Models\Widgets\WidgetUsage;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Testing\TestResponse;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class WidgetTest extends TestCase
{
    use WithFaker, DatabaseTransactions, QpickTestBase;

    protected array $structureWidgetList = [];
    protected array $structureWidgetShow = [];
    protected array $structureUsageList = [];
    protected array $structureUsageShow = [];

    public function __construct()
    {
        parent::__construct();

        $this->structureWidgetShow = [
            'id',
            'name',
            'description',
            'enable',
            'onlyForManager',
            'createdAt',
            'updatedAt',
            'creator' => [
                'id',
                'name',
                'email'
            ]
        ];

        $this->structureWidgetList = [
            'header' => [
                'page',
                'perPage',
                'skip',
                'block',
                'perBlock',
                'totalCount',
                'totalPage',
                'totalBlock',
                'startPage',
                'endPage'
            ],
            'list' => [$this->structureWidgetShow]
        ];

        $this->structureUsageShow = [
            'id',
            'widgetId',
            'sort',
            'createdAt',
            'widget' => $this->structureWidgetShow
        ];

        $this->structureUsageList = [
            'header' => [
                'page',
                'perPage',
                'skip',
                'block',
                'perBlock',
                'totalCount',
                'totalPage',
                'totalBlock',
                'startPage',
                'endPage'
            ],
            'list' => [$this->structureUsageShow]
        ];
    }

    protected function getFactoryWidget(): Factory
    {
        return Widget::factory();
    }

    protected function getFactoryUsage($user = null): Factory
    {
        return WidgetUsage::factory()
            ->for(Widget::factory(), 'widget')
            ->for($user ?? User::inRandomOrder()->first());
    }

    public function getReqWidgetStructure(): array
    {
        return [
            'name' => $this->faker->lastName,
            'description' => $this->faker->text(rand(12,64)),
            'enable' => $this->faker->boolean(),
            'onlyForManager' => $this->faker->boolean(),
        ];
    }

    /*
     * Get Response CRUD for Widgets
     */
    protected function getResponseWidgetCreate(): TestResponse
    {
        return $this->requestQpickApi('post', '/v1/widget', $this->getReqWidgetStructure());
    }

    protected function getResponseWidgetList(): TestResponse
    {
        return $this->requestQpickApi('get', '/v1/widget', []);
    }

    protected function getResponseWidgetShow(): TestResponse
    {
        $factory = $this->getFactoryWidget()->create();
        return $this->requestQpickApi('get', '/v1/widget/' . $factory->id, []);
    }

    protected function getResponseWidgetUpdate(): TestResponse
    {
        $factory = $this->getFactoryWidget()->create();
        return $this->requestQpickApi('patch', '/v1/widget/' . $factory->id, $this->getReqWidgetStructure());
    }

    protected function getResponseWidgetDelete(): TestResponse
    {
        $factory = $this->getFactoryWidget()->create();
        return $this->requestQpickApi('delete', '/v1/widget/' . $factory->id, []);
    }
    /*
     * Get Response CRUD for Usages
     */
    protected function getResponseUsageCreate(): TestResponse
    {
        return $this->requestQpickApi('post', '/v1/widget/usage', [
            'widget_id' => $this->getFactoryWidget()->create()->id
        ]);
    }

    protected function getResponseUsageList($user = null): TestResponse
    {
        for ($i = 0; $i < 3; $i++) {
            $this->getFactoryUsage($user)->create();
        }
        return $this->requestQpickApi('get', '/v1/widget/usage', []);
    }

    protected function getResponseUsageSort($user = null): TestResponse
    {
        $factory = $this->getFactoryUsage($user)->create();
        return $this->requestQpickApi('patch', '/v1/widget/usage/' . $factory->id . '/sort', [
            'target' => WidgetUsage::inRandomOrder()->first()->id,
            'direction' => collect(['top', 'bottom'])->random()
        ]);
    }

    protected function getResponseUsageDelete($user = null): TestResponse
    {
        $factory = $this->getFactoryUsage($user)->create();
        return $this->requestQpickApi('delete', '/v1/widget/usage/' . $factory->id, []);
    }

    /*
     * Test Create a Widget
     */
    public function testCreateWidgetByGuest()
    {
        $response = $this->getResponseWidgetCreate();
        $response->assertUnauthorized();
    }

    public function testCreateWidgetByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseWidgetCreate();
        $response->assertForbidden();
    }

    public function testCreateWidgetByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseWidgetCreate();
        $response->assertForbidden();
    }

    public function testCreateWidgetByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseWidgetCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    /*
     * Test List a Widget
     */
    public function testListWidgetByGuest()
    {
        $response = $this->getResponseWidgetList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetList);
    }

    public function testListWidgetByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseWidgetList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetList);
    }

    public function testListWidgetByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseWidgetList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetList);
    }

    public function testListWidgetByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseWidgetList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetList);
    }

    /*
     * Test Show a Widget
     */
    public function testShowWidgetByGuest()
    {
        $response = $this->getResponseWidgetShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    public function testShowWidgetByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseWidgetShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    public function testShowWidgetByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseWidgetShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    public function testShowWidgetByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseWidgetShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    /*
     * Test Update a Widget
     */
    public function testUpdateWidgetByGuest()
    {
        $response = $this->getResponseWidgetUpdate();
        $response->assertUnauthorized();
    }

    public function testUpdateWidgetByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseWidgetUpdate();
        $response->assertForbidden();
    }

    public function testUpdateWidgetByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseWidgetUpdate();
        $response->assertForbidden();
    }

    public function testUpdateWidgetByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseWidgetUpdate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureWidgetShow);
    }

    /*
     * Test Delete a Widget
     */
    public function testDeleteWidgetByGuest()
    {
        $response = $this->getResponseWidgetDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteWidgetByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseWidgetDelete();
        $response->assertForbidden();
    }

    public function testDeleteWidgetByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseWidgetDelete();
        $response->assertForbidden();
    }

    public function testDeleteWidgetByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseWidgetDelete();
        $response->assertNoContent();
    }

    /*
     * Test Create a Usage
     */
    public function testCreateUsageByGuest()
    {
        $response = $this->getResponseUsageCreate();
        $response->assertUnauthorized();
    }

    public function testCreateUsageByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseUsageCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    public function testCreateUsageByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseUsageCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    public function testCreateUsageByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUsageCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    /*
     * Test List a Usage
     */
    public function testListUsageByGuest()
    {
        $response = $this->getResponseUsageList();
        $response->assertUnauthorized();
    }

    public function testListUsageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseUsageList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureUsageList);
    }

    public function testListUsageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseUsageList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureUsageList);
    }

    public function testListUsageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUsageList($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureUsageList);
    }

    /*
     * Test Sort a Usage
     */
    public function testSortUsageByGuest()
    {
        $response = $this->getResponseUsageSort();
        $response->assertUnauthorized();
    }

    public function testSortUsageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseUsageSort($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    public function testSortUsageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseUsageSort($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    public function testSortUsageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUsageSort($user);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureUsageShow);
    }

    /*
     * Test Delete a Usage
     */
    public function testDeleteUsageByGuest()
    {
        $response = $this->getResponseUsageDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteUsageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseUsageDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteUsageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseUsageDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteUsageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUsageDelete($user);
        $response->assertNoContent();
    }
}
