<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Post;
use Arr;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Str;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class PostTest extends TestCase
{
    use QpickTestBase, DatabaseTransactions;

    ## TODO 추후 게시글 작성 권한이 Backoffice에서만 가능이 Front도 가능으로 변경되면 확인해야함. 현재 Board->options->board(작성권한) 의 값과 상관없이 Backoffice 에서만 사용가능.

    public array $createResource = [
        'title' => '게시글 제목 테스트',
        'content' => '게시글 내용 테스트 입니다. 게시글의 내용입니다.',
        'sort' => 1,
        'hidden' => 0,
    ];

    public array $updateResource = [
        'title' => '게시글 제목을 수정해봅니다.',
        'content' => '게시글 내용을 수정해봅니다. 수정수정수정',
        'sort' => 999,
        'hidden' => 1
    ];


    public array $searchGetPostList = [];

    protected $enableBoard;
    protected $enablePost;
    protected string $url;

    public function setUp(): void
    {
        parent::setUp();

        $this->enableBoard = Board::where(['enable' => 1])->whereJsonContains('options->board', 'all')->first();
        $this->enablePost = $this->enableBoard->posts()->where('hidden', 0)->first();

        $this->url = '/v1/board/' . $this->enableBoard->id . '/post';

        $this->searchGetPostList = [
            'page' => 1,
            'perPage' => 10,
            'boardId' => $this->enableBoard->id,
            'name' => '홍길동',
            'postId' => $this->enablePost->id,
            'title' => Str::random(10),
            'sortBy' => '-id',
            'multiSearch' => Str::random(10)
        ];

        Board::unsetEventDispatcher();
    }

    public function disableBoard(): PostTest
    {
        $this->enableBoard->enable = 0;
        return $this;
    }

    public function invisiblePost(): PostTest
    {
        $this->enablePost->hidden = 1;
        return $this;
    }

    public function modelSave()
    {
        $this->enableBoard->save();
        $this->enablePost->save();
    }

    public function updateBoardWriteOption(string $opt): PostTest
    {
        $this->enableBoard->options = array_merge($this->enableBoard->options, ['board' => $opt]);
        return $this;
    }

    public function createPost($user)
    {
        $postCreateResource = Post::factory()->make(['user_id' => $user->id])->toArray();
        return $this->enableBoard->posts()->create($postCreateResource);
    }

    /**
     * Create
     */
    public function testCreatePostByGuest()
    {
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertUnauthorized();
    }

    public function testCreatePostByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreatePostByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreatePostEnabledBoardWithAllWriteOptionByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }

    public function testCreatePostEnabledBoardWithManagerWriteOptionByBackoffice()
    {
        $this->updateBoardWriteOption('manager')->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }

    public function testCreatePostEnabledBoardWithMemberWriteOptionByBackoffice()
    {
        $this->updateBoardWriteOption('member')->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }

    public function testCreatePostDisabledBoardWithAllWriteOptionByBackoffice()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }

    public function testCreatePostDisabledBoardWithManagerWriteOptionByBackoffice()
    {
        $this->disableBoard()->updateBoardWriteOption('manager')->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }

    public function testCreatePostDisabledBoardWithMemberWriteOptionByBackoffice()
    {
        $this->disableBoard()->updateBoardWriteOption('member')->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertCreated();
    }


    /**
     * Read (Show)
     */
    public function testShowPostEnabledBoardByGuest()
    {
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowInvisiblePostEnabledBoardByGuest()
    {
        $this->invisiblePost()->modelSave();
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostDisabledBoardByGuest()
    {
        $this->disableBoard();
        $this->enableBoard->save();

        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowInvisiblePostDisabledBoardByGuest()
    {
        $this->invisiblePost()->disableBoard()->modelSave();

        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostEnabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowInvisiblePostEnabledBoardByAssociate()
    {
        $this->invisiblePost()->modelSave();

        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostDisabledBoardByAssociate()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowInvisiblePostDisabledBoardByAssociate()
    {
        $this->disableBoard()->invisiblePost()->modelSave();

        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostEnabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowInvisiblePostEnabledBoardByRegular()
    {
        $this->invisiblePost()->modelSave();

        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostDisabledBoardByRegular()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowInvisiblePostDisabledBoardByRegular()
    {
        $this->disableBoard()->invisiblePost()->modelSave();

        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertForbidden();
    }

    public function testShowPostEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowInvisiblePostEnabledBoardByBackoffice()
    {
        $this->invisiblePost()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowPostDisabledBoardByBackoffice()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }

    public function testShowInvisiblePostDisabledBoardByBackoffice()
    {
        $this->disableBoard()->invisiblePost()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url . '/' . $this->enablePost->id, []);
        $response->assertOk();
    }


    /**
     * index
     */
    public function testIndexPostEnabledBoardByGuest()
    {
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertOk()
            ->assertJsonMissing(['hidden' => 1]);
    }

    public function testIndexPostDisabledBoardByGuest()
    {
        $this->disableBoard()->modelSave();

        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertForbidden();
    }

    public function testIndexPostEnabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertOk()
            ->assertJsonMissing(['hidden' => 1]);
    }

    public function testIndexPostDisabledBoardByAssociate()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertForbidden();
    }

    public function testIndexPostEnabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertOk()
            ->assertJsonMissing(['hidden' => 1]);
    }

    public function testIndexPostDisabledBoardByRegular()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertForbidden();
    }

    public function testIndexPostEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertOk();
    }

    public function testIndexPostDisabledBoardByBackoffice()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url, []);
        $response->assertOk();
    }


    /**
     * Update
     */
    public function testUpdatePostByGuest()
    {
        $response = $this->requestQpickApi('patch', $this->url . '/' . $this->enablePost->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertUnauthorized();
    }

    public function testUpdatePostByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('patch', $this->url . '/' . $this->enablePost->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertForbidden();
    }

    public function testUpdatePostByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('patch', $this->url . '/' . $this->enablePost->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertForbidden();
    }

    public function testUpdateOwnerPostEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('patch', $this->url . '/' . $this->enablePost->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertCreated();
    }

    public function testUpdateOwnerPostDisabledBoardByBackoffice()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('patch', $this->url . '/' . $this->enablePost->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertCreated();
    }

    public function testUpdateOtherPostEnabledBoardByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');

        $post = $this->createPost($user);
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', $this->url . '/' . $post->id, array_merge(
            $this->updateResource, ['boardId' => Board::where('id', '!=', $this->enableBoard->id)->get()->random()->id]
        ));
        $response->assertForbidden();
    }


    /**
     * Destroy
     */
    public function testDestroyPostByGuest()
    {
        $response = $this->requestQpickApi('delete', $this->url . '/' . $this->enablePost->id);
        $response->assertUnauthorized();
    }

    public function testDestroyPostByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('delete', $this->url . '/' . $this->enablePost->id);
        $response->assertForbidden();
    }

    public function testDestroyPostByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('delete', $this->url . '/' . $this->enablePost->id);
        $response->assertForbidden();
    }

    public function testDestroyOwnerPostEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('delete', $this->url . '/' . $this->enablePost->id);
        $response->assertNoContent();
    }

    public function testDestroyOwnerPostDisabledBoardByBackoffice()
    {
        $this->disableBoard()->modelSave();

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('delete', $this->url . '/' . $this->enablePost->id);
        $response->assertNoContent();
    }

    public function testDestroyOtherPostEnabledBoardByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $post = $this->createPost($user);

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('delete', $this->url . '/' . $post->id);
        $response->assertForbidden();
    }


    /**
     * Non-CRUD
     */
    public function testGetPostListByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/post?' . Arr::query($this->searchGetPostList));
        $response->assertUnauthorized();
    }

    public function testGetPostListByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/post?' . Arr::query($this->searchGetPostList));
        $response->assertForbidden();
    }

    public function testGetPostListByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', '/v1/post?' . Arr::query($this->searchGetPostList));
        $response->assertForbidden();
    }

    public function testGetPostListByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/post?' . Arr::query($this->searchGetPostList));
        $response->assertOk();
    }


}
