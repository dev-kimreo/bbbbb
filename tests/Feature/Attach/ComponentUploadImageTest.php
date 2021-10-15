<?php

namespace Tests\Feature\Attach;

use App\Models\Attach\AttachFile;
use App\Models\Attach\ComponentUploadImage;
use App\Models\Users\User;
use App\Services\AttachService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Storage;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class ComponentUploadImageTest extends TestCase
{
    use WithFaker;
    use QpickTestBase;
    use DatabaseTransactions;

    protected string $storagePath = 'test';
    protected UploadedFile $fakeFile;

    public array $structureShow = [
        'id',
        'server',
        'attachableType',
        'attachableId',
        'userId',
        'url',
        'path',
        'name',
        'orgName',
        'size',
        'etc',
        'createdAt',
        'updatedAt',
        'thumb' => [
            'server',
            'url',
            'path',
            'name',
            'orgName',
            'size',
            'etc'
        ],
        'componentUploadImage' => [
            'id',
            'userId',
            'width',
            'height'
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
                'server',
                'attachableType',
                'attachableId',
                'userId',
                'url',
                'path',
                'name',
                'orgName',
                'size',
                'etc',
                'createdAt',
                'updatedAt',
                'thumb' => [
                    'server',
                    'url',
                    'path',
                    'name',
                    'orgName',
                    'size',
                    'etc'
                ],
                'componentUploadImage' => [
                    'id',
                    'userId',
                    'width',
                    'height'
                ]
            ]
        ]
    ];

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake($this->storagePath);
        AttachService::setStorageDisk($this->storagePath);
    }

    protected function getFactory(?User $user = null): Factory
    {
        $user = $user ?? User::factory()->create();

        return ComponentUploadImage::factory()
            ->for($user, 'uploader')
            ->has(AttachFile::factory()->for($user, 'uploader'), 'attachFile');
    }

    /**
     * Create
     */
    protected function getResponseCreate(): TestResponse
    {
        $this->fakeFile = UploadedFile::fake()->image('photo.jpg');

        return $this->requestQpickApi('post', '/v1/component-upload-image', [
            'files' => $this->fakeFile
        ]);
    }

    public function testCreateComponentUploadImageByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreateComponentUploadImageByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    public function testCreateComponentUploadImageByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    public function testCreateComponentUploadImageByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    /**
     * Index
     */
    protected function getResponseList(): TestResponse
    {
        return $this->requestQpickApi('get', '/v1/component-upload-image', []);
    }

    public function testListComponentUploadImageByGuest()
    {
        $response = $this->getResponseList();
        $response->assertUnauthorized();
    }

    public function testListComponentUploadImageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $this->getFactory($user)->create();

        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListComponentUploadImageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $this->getFactory($user)->create();

        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    public function testListComponentUploadImageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $this->getFactory($user)->create();

        $response = $this->getResponseList();
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    /**
     * Show
     */
    protected function getResponseShow(?User $user = null, bool $owned = true): TestResponse
    {
        $id = $this->getFactory($owned ? $user : null)->create()->getAttribute('id');
        return $this->requestQpickApi('get', '/v1/component-upload-image/' . $id, []);
    }

    public function testShowComponentUploadImageByGuest()
    {
        $response = $this->getResponseShow();
        $response->assertUnauthorized();
    }

    public function testShowOwnedComponentUploadImageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowOwnedComponentUploadImageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowOwnedComponentUploadImageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow($user);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testShowOtherComponentUploadImageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseShow($user, false);
        $response->assertForbidden();
    }

    public function testShowOtherComponentUploadImageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseShow($user, false);
        $response->assertForbidden();
    }

    public function testShowOtherComponentUploadImageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseShow($user, false);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Delete
     */
    protected function getResponseDelete(?User $user = null, bool $owned = true): TestResponse
    {
        $id = $this->getFactory($owned ? $user : null)->create()->getAttribute('id');
        return $this->requestQpickApi('delete', '/v1/component-upload-image/' . $id, []);
    }

    public function testDeleteComponentUploadImageByGuest()
    {
        $response = $this->getResponseDelete();
        $response->assertUnauthorized();
    }

    public function testDeleteOwnedComponentUploadImageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteOwnedComponentUploadImageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteOwnedComponentUploadImageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete($user);
        $response->assertNoContent();
    }

    public function testDeleteOtherComponentUploadImageByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete($user, false);
        $response->assertForbidden();
    }

    public function testDeleteOtherComponentUploadImageByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete($user, false);
        $response->assertForbidden();
    }

    public function testDeleteOtherComponentUploadImageByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete($user, false);
        $response->assertNoContent();
    }
}
