<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class EmailTemplateTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];
    protected array $searchResource = [];

    public array $structureShow = [
        'id',
        'userId',
        'code',
        'category',
        'name',
        'title',
        'contents',
        'sendingTime',
        'enable',
        'ignoreAgree',
        'createdAt',
        'updatedAt',
        'user' => [
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
                'userId',
                'category',
                'name',
                'title',
                'sendingTime',
                'createdAt',
                'user' => [
                    'id', 'name', 'email'
                ]
            ]
        ]
    ];

    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = [
            'code' => $this->faker->text(16),
            'name' => $this->faker->text(16),
            'title' => $this->faker->text(36)
        ];

        $this->updateResource = [
            'name' => $this->faker->text(16),
            'title' => $this->faker->text(36)
        ];

        $this->searchResource = [
        ];
    }

    protected function createReq(): array
    {
        return $this->createResource;
    }

    protected function createEmailTemplate()
    {
        $user = $this->createAsQpickUser('backoffice');

        return EmailTemplate::factory()->create(['user_id' => $user->id]);
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/email-template', $this->createReq());
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/email-template', $this->createReq());
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/email-template', $this->createReq());
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/email-template', $this->createReq());
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $temp = $this->createEmailTemplate();

        $response = $this->requestQpickApi('get', '/v1/email-template/' . $temp->id);
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $temp = $this->createEmailTemplate();

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/email-template/' . $temp->id);
        $response->assertForbidden();
    }

    public function testShowByRegular()
    {
        $temp = $this->createEmailTemplate();

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/email-template/' . $temp->id);
        $response->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $temp = $this->createEmailTemplate();

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/email-template/' . $temp->id);
        $response->assertOk();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/email-template');
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/email-template');
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/email-template');
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/email-template');
        $response->assertOk();
        $response->assertJsonStructure($this->structureList);
    }

    /**
     * update
     */
    public function testUpdateByGuest()
    {
        $temp = $this->createEmailTemplate();

        $response = $this->requestQpickApi('patch', '/v1/email-template/' . $temp->id, $this->updateResource);
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/email-template/' . $temp->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/email-template/' . $temp->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/email-template/' . $temp->id, $this->updateResource);
        $response->assertCreated();
        $response->assertJsonStructure($this->structureShow);
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $temp = $this->createEmailTemplate();

        $response = $this->requestQpickApi('delete', '/v1/email-template/' . $temp->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/email-template/' . $temp->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/email-template/' . $temp->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $temp = $this->createEmailTemplate();
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/email-template/' . $temp->id);
        $response->assertNoContent();
    }
}
