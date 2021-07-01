<?php

namespace Tests\Feature\Exhibitions;

use App\Models\Exhibitions\Exhibition;
use App\Models\Exhibitions\ExhibitionCategory;
use App\Models\Exhibitions\Popup;
use App\Models\Exhibitions\PopupDeviceContent;
use App\Models\User;
use App\Models\Users\UserPrivacyActive;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class PopupTest extends TestCase
{
    use QpickTestBase, WithFaker, DatabaseTransactions;

    public array $structureShow = [
        'id',
        'title',
        'createdAt',
        'updatedAt',
        'exhibition' => [
            'startedAt',
            'endedAt',
            'sort',
            'visible',
            'target' => [
                'opt', 'grade', 'users'
            ],
            'category' => [
                'id', 'name'
            ]
        ],
        'contents' => [
            ['id', 'device', 'contents']
        ],
        'creator' => [
            'id', 'name', 'email'
        ]
    ];

    public array $structureList = [
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
            [
                'id',
                'title',
                'createdAt',
                'updatedAt',
                'devices',
                'exhibition' => [
                    'startedAt',
                    'endedAt',
                    'sort',
                    'visible',
                    'target' => [
                        'opt', 'grade', 'users'
                    ],
                    'category' => [
                        'id', 'name'
                    ]
                ],
                'creator' => [
                    'id', 'name', 'email'
                ]
            ]
        ]
    ];

    protected function getFactory(): Factory
    {
        return Popup::factory()
            ->has(
                Exhibition::factory()->for(
                    ExhibitionCategory::factory()->create(), 'category'
                )
            )->for(User::factory()->has(
                UserPrivacyActive::factory(), 'privacy'
            )->create(), 'creator')
            ->has(PopupDeviceContent::factory(), 'contents');
    }

    protected function getResponseCreate()
    {
        return $this->requestQpickApi('post', '/v1/exhibition/popup', [
            'exhibitionCategoryId' => 1,
            'title' => '메인 공지팝업',
            'startedAt' => '2021-07-01',
            'endedAt' => '2021-07-22',
            'targetOpt' => 'all',
            'targetUsers' => [3, 1],
            'contents' => [
                'mobile' => '모바일용 콘텐츠',
                'pc' => 'PC용 콘텐츠'
            ]
        ]);
    }

    protected function getResponseList()
    {
        for ($i=0; $i<=3; $i++) {
            $this->getFactory()->create();
        }

        return $this->requestQpickApi('get', '/v1/exhibition/popup', []);
    }

    protected function getResponseShow()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('get', '/v1/exhibition/popup/' . $factory->id, []);
    }

    protected function getResponseUpdate()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('patch', '/v1/exhibition/popup/' . $factory->id, [
            'exhibitionCategoryId' => 1,
            'title' => '메인 공지팝업',
            'startedAt' => '2021-07-01',
            'endedAt' => '2021-07-22',
            'targetOpt' => 'all',
            'targetUsers' => [3, 1],
            'contents' => [
                'mobile' => '모바일용 콘텐츠',
                'pc' => 'PC용 콘텐츠'
            ]
        ]);
    }

    protected function getResponseDelete()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('delete', '/v1/exhibition/popup/' . $factory->id, []);
    }

    public function testCreatePopupByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreatePopupByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreatePopupByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreatePopupByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testListPopupByGuest()
    {
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListPopupByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListPopupByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListPopupByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testShowPopupByGuest()
    {
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowPopupByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowPopupByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowPopupByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdatePopupByGuest()
    {
        $response = $this->getResponseUpdate();
        $response->assertUnauthorized();
    }

    public function testUpdatePopupByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdatePopupByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdatePopupByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseUpdate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testDeletePopupByGuest()
    {
        $response = $this->getResponseDelete();
        $response->assertUnauthorized();
    }

    public function testDeletePopupByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeletePopupByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeletePopupByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete();
        $response->assertNoContent();
    }
}
