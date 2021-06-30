<?php

namespace Tests\Feature\Exhibitions;

use App\Models\Exhibitions\ExhibitionCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ExhibitionCategoryTest extends TestCase
{
    use QpickTestBase, WithFaker, DatabaseTransactions;

    public array $structure = [
        'id',
        'name',
        'url',
        'division',
        'site',
        'max',
        'enable',
        'createdAt',
        'updatedAt'
    ];

    public function __construct()
    {
        parent::__construct();
    }

    protected function getFactory(): Factory
    {
        return ExhibitionCategory::factory();
    }

    protected function getResponseCreate()
    {
        return $this->requestQpickApi('post', '/v1/exhibition/category', [
            'name' => $this->faker->text(8),
            'url' =>  $this->faker->url,
            'division' => array_rand(array_flip(ExhibitionCategory::$divisions)),
            'site' => array_rand(array_flip(ExhibitionCategory::$sites)),
            'max' => 10,
            'enable' => 1,
        ]);
    }

    protected function getResponseList()
    {
        for ($i=0; $i<=3; $i++) {
            $this->getFactory()->create();
        }

        return $this->requestQpickApi('get', '/v1/exhibition/category', []);
    }

    protected function getResponseShow()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('get', '/v1/exhibition/category/' . $factory->id, []);
    }

    protected function getResponseUpdate()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('patch', '/v1/exhibition/category/' . $factory->id, [
            'name' => $this->faker->text(8),
            'url' =>  $this->faker->url,
            'division' => array_rand(array_flip(ExhibitionCategory::$divisions)),
            'site' => array_rand(array_flip(ExhibitionCategory::$sites)),
            'max' => 10,
            'enable' => 1,
        ]);
    }

    protected function getResponseDelete()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('delete', '/v1/exhibition/category/' . $factory->id, []);
    }

    public function testCreateCategoryByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreateCategoryByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreateCategoryByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreateCategoryByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structure);
    }

    public function testListCategoryByGuest()
    {
        $response = $this->getResponseList();
        $response->assertUnauthorized();
    }

    public function testListCategoryByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseList();
        $response->assertForbidden();
    }

    public function testListCategoryByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseList();
        $response->assertForbidden();
    }

    public function testListCategoryByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure([
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
            'list' => [
                $this->structure
            ]
        ]);
    }

    public function testShowCategoryByGuest()
    {
        $response = $this->getResponseShow();
        $response->assertUnauthorized();
    }

    public function testShowCategoryByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow();
        $response->assertForbidden();
    }

    public function testShowCategoryByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow();
        $response->assertForbidden();
    }

    public function testShowCategoryByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structure);
    }

    public function testUpdateCategoryByGuest()
    {
        $response = $this->getResponseUpdate();
        $response->assertUnauthorized();
    }

    public function testUpdateCategoryByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdateCategoryByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdateCategoryByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUpdate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structure);
    }

    public function testDeleteCategoryByGuest()
    {
        $response = $this->getResponseDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteCategoryByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeleteCategoryByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeleteCategoryByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete();
        $response->assertNoContent();
    }
}
