<?php

namespace Tests\Feature;

use App\Libraries\CollectionLibrary;
use App\Models\Board;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class BoardTest extends TestCase
{
    use QpickTestBase, DatabaseTransactions;

    public array $resourceStruct = [
        'id',
        'name',
        'enable',
        'userId',
        'sort',
        'createdAt',
        'updatedAt',
        'options' => [
            'board',
            'theme',
            'thumbnail',
            'reply',
            'editor',
            'attach',
            'attachLimit',
            'createdAt'
        ]
    ];

    protected $enableBoard;
    protected $disableBoard;

    public function setUp(): void
    {
        parent::setUp();

        $this->enableBoard = Board::where(['enable' => 1])->first();
        $this->disableBoard = Board::where(['enable' => 0])->first();
    }

    /**
     * Create
     */
    public function testCreateBoardByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/board', [
            'name' => '공지사항',
            'enable' => 1,
        ]);
        $response
            ->assertUnauthorized();
    }


    public function testCreateBoardByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('post', '/v1/board', [
            'name' => '공지사항',
            'enable' => 1,
        ]);
        $response
            ->assertForbidden();
    }

    public function testCreateBoardByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('post', '/v1/board', [
            'name' => '공지사항',
            'enable' => 1,
        ]);
        $response
            ->assertForbidden();
    }


    public function testCreateBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', '/v1/board', [
            'name' => '공지사항',
            'enable' => 0,
            'options' => [
                'reply' => 1
            ]
        ]);
        $response
            ->assertStatus(201)
            ->assertJsonStructure($this->resourceStruct);
    }


    /**
     * Read
     */
    public function testShowEnableBoardByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->enableBoard->id);
        $response
            ->assertOk();
    }

    public function testShowDisableBoardByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->disableBoard->id);
        $response
            ->assertForbidden();
    }

    public function testShowEnableBoardByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->enableBoard->id);
        $response
            ->assertOk();
    }

    public function testShowDisableBoardByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->disableBoard->id);
        $response
            ->assertForbidden();
    }

    public function testShowEnableBoardByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->enableBoard->id);
        $response
            ->assertOk();
    }

    public function testShowDisableBoardByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->disableBoard->id);
        $response
            ->assertForbidden();
    }

    public function testShowEnableBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->enableBoard->id);
        $response
            ->assertOk();
    }

    public function testShowDisableBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/board/' . $this->disableBoard->id);
        $response
            ->assertOk();
    }


    /**
     * index
     */
    public function testIndexBoardByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/board');
        $response
            ->assertOk()
            ->assertJsonMissing(['enable' => 0]);
    }

    public function testIndexBoardByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/board');
        $response
            ->assertOk()
            ->assertJsonMissing(['enable' => 0]);
    }

    public function testIndexBoardByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', '/v1/board');
        $response
            ->assertOk()
            ->assertJsonMissing(['enable' => 0]);
    }

    public function testIndexBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/board');
        $response
            ->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateBoardByGuest()
    {
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id, [
            'name' => '앙팡우유',
            'enable' => 1,
            'options' => [
                'reply' => 0
            ]
        ]);

        $response
            ->assertUnauthorized();
    }

    public function testUpdateBoardByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id, [
            'name' => '앙팡우유',
            'enable' => 1,
            'options' => [
                'reply' => 0
            ]
        ]);

        $response
            ->assertForbidden();
    }

    public function testUpdateBoardByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id, [
            'name' => '앙팡우유',
            'enable' => 1,
            'options' => [
                'reply' => 0
            ]
        ]);

        $response
            ->assertForbidden();
    }

    public function testUpdateBoardByBackoffice()
    {
        $ddd = [
            'name' => '앙팡우유',
            'enable' => 1
        ];
        $this->enableBoard->fill($ddd);

        $user = $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id, $ddd);

        $response
            ->assertCreated();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $response = $this->requestQpickApi('delete', '/v1/board/' . $this->enableBoard->id);

        $response
            ->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('delete', '/v1/board/' . $this->enableBoard->id);

        $response
            ->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('delete', '/v1/board/' . $this->enableBoard->id);

        $response
            ->assertForbidden();
    }

    public function testDestroyBoardWithEmptyPostByBackoffice()
    {
        $board = Board::where(['name' => 'empty'])->first();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('delete', '/v1/board/' . $board->id);
        $response
            ->assertNoContent();
    }

    public function testDestroyBoardWithExistsPostsByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('delete', '/v1/board/' . $this->enableBoard->id);

        // 존재 하는 게시글이 존재하여 삭제할 수 없습니다.
        $response
            ->assertStatus(422);
    }


    /**
     * Non-CRUD
     */

    /**
     * getPostsCount Method
     */
    public function testIndexBoardWithPostCountByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/board/posts-count');
        $response
            ->assertUnauthorized();
    }

    public function testIndexBoardWithPostCountByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/board/posts-count');
        $response
            ->assertForbidden();
    }

    public function testIndexBoardWithPostCountByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', '/v1/board/posts-count');
        $response
            ->assertForbidden();
    }

    public function testIndexBoardWithPostCountByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/board/posts-count');
        $response
            ->assertOk();
    }


    /**
     * updateBoardSort Method
     */
    public function testUpdateBoardSortByGuest()
    {
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id . '/sort', [
            'target' => $this->disableBoard->id,
            'direction' => 'top'
        ]);
        $response
            ->assertUnauthorized();
    }

    public function testUpdateBoardSortByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id . '/sort', [
            'target' => $this->disableBoard->id,
            'direction' => 'top'
        ]);
        $response
            ->assertForbidden();
    }

    public function testUpdateBoardSortByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id . '/sort', [
            'target' => $this->disableBoard->id,
            'direction' => 'top'
        ]);
        $response
            ->assertForbidden();
    }

    public function testUpdateBoardSortByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('patch', '/v1/board/' . $this->enableBoard->id . '/sort', [
            'target' => $this->disableBoard->id,
            'direction' => 'top'
        ]);
        $response
            ->assertNoContent();
    }


}
