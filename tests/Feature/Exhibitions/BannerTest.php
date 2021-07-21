<?php

namespace Tests\Feature\Exhibitions;

use App\Models\AttachFile;
use App\Models\Exhibitions\Exhibition;
use App\Models\Exhibitions\ExhibitionCategory;
use App\Models\Exhibitions\Banner;
use App\Models\Exhibitions\BannerDeviceContent;
use App\Models\User;
use App\Models\Users\UserPrivacyActive;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class BannerTest extends TestCase
{
    use QpickTestBase, WithFaker, DatabaseTransactions;

    public array $structureShow = [
        'id',
        'title',
        'url',
        'gaCode',
        'memo',
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
            ['id', 'device', 'attachFile']
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
                'url',
                'gaCode',
                'memo',
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
        return Banner::factory()
            ->has(
                Exhibition::factory()->for(
                    ExhibitionCategory::factory()->create(),
                    'category'
                )
            )->for(User::factory()->has(
                UserPrivacyActive::factory(), 'privacy'
            )->create(), 'creator')
            ->has(
                BannerDeviceContent::factory()->has(
                    AttachFile::factory()->for(
                        User::factory()->has(
                            UserPrivacyActive::factory(), 'privacy'
                        )->create(),
                        'uploader'
                    ),
                    'attachFile'
                ),
                'contents'
            );
    }

    protected function getReqStructure($exhibitionCategoryId, $targetOpt): array
    {
        $req = [
            'exhibitionCategoryId' => $exhibitionCategoryId,
            'title' => '메인 공지팝업',
            'url' => $this->faker->url,
            'gaCode' => $this->faker->text(32),
            'memo' => $this->faker->text(rand(20, 100)),
            'startedAt' => '2021-07-01',
            'endedAt' => '2021-07-22',
            'targetOpt' => $targetOpt,
            'contents' => [
                'mobile' => 1,
                'pc' => 1
            ]
        ];

        if ($targetOpt == 'grade') {
            $req['targetGrade'] = ['associate', 'regular'];
        } elseif ($targetOpt == 'designate') {
            $req['targetUsers'] = [3, 1];
        }

        return $req;
    }

    protected function getResponseCreate($targetOpt = 'all')
    {
        $exhibitionCategoryId = ExhibitionCategory::factory()->create()->id;
        $req = $this->getReqStructure($exhibitionCategoryId, $targetOpt);

        return $this->requestQpickApi('post', '/v1/exhibition/banner', $req);
    }

    protected function getResponseList()
    {
        for ($i=0; $i<=3; $i++) {
            $this->getFactory()->create();
        }

        return $this->requestQpickApi('get', '/v1/exhibition/banner', []);
    }

    protected function getResponseShow()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('get', '/v1/exhibition/banner/' . $factory->id, []);
    }

    protected function getResponseUpdate($targetOpt = 'all')
    {
        $factory = $this->getFactory()->create();
        $req = $this->getReqStructure($factory->exhibition->category->id, $targetOpt);

        return $this->requestQpickApi('patch', '/v1/exhibition/banner/' . $factory->id, $req);
    }

    protected function getResponseDelete()
    {
        $factory = $this->getFactory()->create();

        return $this->requestQpickApi('delete', '/v1/exhibition/banner/' . $factory->id, []);
    }

    public function testCreateBannerByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreateBannerByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreateBannerByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseCreate();
        $response->assertForbidden();
    }

    public function testCreateBannerByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        foreach (['all', 'grade', 'designate'] as $targetOpt) {
            $response = $this->getResponseCreate($targetOpt);
            $response->assertCreated();
            $response->assertJsonStructure($this->structureShow);
        }
    }

    public function testListBannerByGuest()
    {
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListBannerByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListBannerByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListBannerByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testShowBannerByGuest()
    {
        $response = $this->getResponseShow();
        $response->assertUnauthorized();
    }

    public function testShowBannerByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow();
        $response->assertForbidden();
    }

    public function testShowBannerByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow();
        $response->assertForbidden();
    }

    public function testShowBannerByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow();
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateBannerByGuest()
    {
        $response = $this->getResponseUpdate();
        $response->assertUnauthorized();
    }

    public function testUpdateBannerByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdateBannerByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseUpdate();
        $response->assertForbidden();
    }

    public function testUpdateBannerByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        foreach (['all', 'grade', 'designate'] as $targetOpt) {
            $response = $this->getResponseUpdate($targetOpt);
            $response->assertCreated();
            $response->assertJsonStructure($this->structureShow);
        }
    }

    public function testDeleteBannerByGuest()
    {
        $response = $this->getResponseDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteBannerByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeleteBannerByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete();
        $response->assertForbidden();
    }

    public function testDeleteBannerByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete();
        $response->assertNoContent();
    }
}
