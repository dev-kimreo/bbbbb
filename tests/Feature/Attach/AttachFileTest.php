<?php

namespace Tests\Feature\Attach;

use App\Models\Attach\AttachFile;
use App\Models\Boards\Board;
use App\Models\Boards\Post;
use App\Models\Inquiries\Inquiry;
use App\Models\Users\User;
use App\Services\AttachService;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Testing\TestResponse;
use Storage;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class AttachFileTest extends TestCase
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
                ]
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake($this->storagePath);
        AttachService::setStorageDisk($this->storagePath);
    }

    protected function getFactory(User $user): Factory
    {
        return AttachFile::factory()->for($user, 'uploader');
    }

    /**
     * Create
     */
    protected function getResponseCreate(): TestResponse
    {
        $this->fakeFile = UploadedFile::fake()->image('photo.jpg');

        return $this->requestQpickApi('post', '/v1/attach', [
            'files' => $this->fakeFile
        ]);
    }

    public function testCreateAttachFileByGuest()
    {
        $response = $this->getResponseCreate();
        $response->assertUnauthorized();
    }

    public function testCreateAttachFileByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    public function testCreateAttachFileByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    public function testCreateAttachFileByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseCreate();
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
        Storage::disk($this->storagePath)->assertExists($response->json('path'));
    }

    /**
     * Update
     */
    protected function getResponseUpdateForInquiry(?User $user, bool $owned): TestResponse
    {
        if (!$owned) {
            $user = User::factory()->create();
        }

        $id = $this->getFactory($user)->create()->getAttribute('id');
        $inquiryId = Inquiry::factory()->for($user, 'user')->create();
        $inquiryId = $inquiryId->getAttribute('id');

        return $this->requestQpickApi('patch', '/v1/attach/' . $id, [
            'type' => 'inquiry',
            'typeId' => $inquiryId
        ]);
    }

    protected function getResponseUpdateForPost(?User $user, bool $owned): TestResponse
    {
        if (!$owned) {
            $user = User::factory()->create();
        }

        $id = $this->getFactory($user)->create()->getAttribute('id');
        $postId = Post::factory()
            ->for($user, 'user')
            ->for(Board::factory()->for($user, 'user')->create())
            ->create()
            ->getAttribute('id');

        return $this->requestQpickApi('patch', '/v1/attach/' . $id, [
            'type' => 'post',
            'typeId' => $postId
        ]);
    }

    public function testUpdateAttachFileByGuest()
    {
        $response = $this->getResponseUpdateForInquiry(null, false);
        $response->assertUnauthorized();

        $response = $this->getResponseUpdateForPost(null, false);
        $response->assertUnauthorized();
    }

    public function testUpdateOwnedAttachFileByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseUpdateForInquiry($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);

        $response = $this->getResponseUpdateForPost($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateOwnedAttachFileByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseUpdateForInquiry($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);

        $response = $this->getResponseUpdateForPost($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateOwnedAttachFileByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseUpdateForInquiry($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);

        $response = $this->getResponseUpdateForPost($user, true);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    public function testUpdateOtherAttachFileByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->getResponseUpdateForInquiry($user, false);
        $response->assertForbidden();

        $response = $this->getResponseUpdateForPost($user, false);
        $response->assertForbidden();
    }

    public function testUpdateOtherAttachFileByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->getResponseUpdateForInquiry($user, false);
        $response->assertForbidden();

        $response = $this->getResponseUpdateForPost($user, false);
        $response->assertForbidden();
    }

    public function testUpdateOtherAttachFileByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->getResponseUpdateForInquiry($user, false);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);

        $response = $this->getResponseUpdateForPost($user, false);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Delete
     */
    protected function getResponseDelete(?User $user, bool $owned): TestResponse
    {
        if (!$owned) {
            $user = User::factory()->create();
        }

        $id = $this->getFactory($user)->create()->getAttribute('id');
        return $this->requestQpickApi('delete', '/v1/attach/' . $id, []);
    }

    public function testDeleteAttachFileByGuest()
    {
        $response = $this->getResponseDelete(null, false);
        $response->assertUnauthorized();
    }

    public function testDeleteOwnedAttachFileByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete($user, true);
        $response->assertNoContent();
    }

    public function testDeleteOwnedAttachFileByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete($user, true);
        $response->assertNoContent();
    }

    public function testDeleteOwnedAttachFileByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete($user, true);
        $response->assertNoContent();
    }

    public function testDeleteOtherAttachFileByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->getResponseDelete($user, false);
        $response->assertForbidden();
    }

    public function testDeleteOtherAttachFileByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->getResponseDelete($user, false);
        $response->assertForbidden();
    }

    public function testDeleteOtherAttachFileByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->getResponseDelete($user, false);
        $response->assertNoContent();
    }
}
