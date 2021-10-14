<?php

namespace Tests\Feature\Attach;

use App\Services\AttachService;
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
            'attachFileId',
            'width',
            'height'
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
}
