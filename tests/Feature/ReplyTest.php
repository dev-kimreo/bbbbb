<?php

namespace Tests\Feature;

use App\Models\Board;
use App\Models\Reply;
use Arr;
use Str;
use Tests\Feature\Traits\QpickTestBase;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ReplyTest extends TestCase
{
    use QpickTestBase, DatabaseTransactions;

    // TODO 현재 댓글은 게시글의 hidden 의 값과 상관없이, 백오피스관리자만 관리가 가능하고, 유저는 불가.

    public array $createResource = [
        'content' => '등록하는 댓글 내용입니다. 잘 부탁드립니다.'
    ];

    public array $updateResource = [
        'content' => '수정하는 댓글 내용입니다. 잘 부탁드립니다.'
    ];

    public array $resourceStruct = [
    ];

    protected $enableBoard;
    protected $enablePost;
    protected $url;

    public function setUp(): void
    {
        parent::setUp();

        $this->enableBoard = Board::where(['enable' => 1])->whereJsonContains('options->reply', 1)->first();
        $this->enablePost = $this->enableBoard->posts->where('hidden', 0)->first();


        $this->url = '/v1/board/' . $this->enableBoard->id . '/post/' . $this->enablePost->id . '/reply';

        Board::unsetEventDispatcher();
    }

    public function modelSave()
    {
        $this->enableBoard->save();
        $this->enablePost->save();
    }

    public function disableBoard(): ReplyTest
    {
        $this->enableBoard->enable = 0;
        return $this;
    }

    public function invisiblePost(): ReplyTest
    {
        $this->enablePost->hidden = 1;
        return $this;
    }

    public function updateBoardOption(array $opt): ReplyTest
    {
        $this->enableBoard->options = array_merge($this->enableBoard->options, $opt);
        return $this;
    }

    public function createReply($user)
    {
        $post = $this->enableBoard->posts()->where('hidden', 0)->first();
        $replyCreateResource = Reply::factory()->make(['post_id' => $post->id, 'user_id' => $user->id])->toArray();
        return $post->replies()->create($replyCreateResource);
    }


    /**
     * Create
     */
    public function testCreateReplyByGuest()
    {
        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertUnauthorized();
    }

    public function testCreateReplyByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreateReplyByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', $this->url, $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreateReplyEnabledBoardWithReplyOptionByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', $this->url, $this->createResource);

        $response
            ->assertCreated();
    }

    public function testCreateReplyEnabledBoardWithoutReplyOptionByBackoffice()
    {
        $this->updateBoardOption(['reply' => 0])->modelSave();

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', $this->url, $this->createResource);

        $response
            ->assertForbidden();
    }


    /**
     * Read (Index)
     */
    public function testIndexRepliesOnVisiblePostOnEnabledBoardByGuest()
    {
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnInvisiblePostOnEnabledBoardByGuest()
    {
        $this->invisiblePost()->modelSave();

        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnVisiblePostOnDisabledBoardByGuest()
    {
        $this->disableBoard()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnInvisiblePostOnDisabledBoardByGuest()
    {
        $this->disableBoard()->invisiblePost()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }


    public function testIndexRepliesOnVisiblePostOnEnabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnInvisiblePostOnEnabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $this->invisiblePost()->modelSave();

        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnVisiblePostOnDisabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $this->disableBoard()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnInvisiblePostOnDisabledBoardByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $this->disableBoard()->invisiblePost()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnVisiblePostOnEnabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnInvisiblePostOnEnabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $this->invisiblePost()->modelSave();

        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnVisiblePostOnDisabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $this->disableBoard()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }

    public function testIndexRepliesOnInvisiblePostOnDisabledBoardByRegular()
    {
        $this->actingAsQpickUser('regular');
        $this->disableBoard()->invisiblePost()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertForbidden();
    }


    public function testIndexRepliesOnVisiblePostOnEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnInvisiblePostOnEnabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $this->invisiblePost()->modelSave();

        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnVisiblePostOnDisabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $this->disableBoard()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    public function testIndexRepliesOnInvisiblePostOnDisabledBoardByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $this->disableBoard()->invisiblePost()->modelSave();
        $response = $this->requestQpickApi('get', $this->url);

        $response
            ->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateReplyByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertUnauthorized();
    }

    public function testUpdateReplyByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertForbidden();
    }

    public function testUpdateReplyByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertForbidden();
    }

    public function testUpdateOwnerReplyOnVisiblePostOnEnabledBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertCreated();
    }

    public function testUpdateOwnerReplyOnInvisiblePostOnEnabledBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $reply = $this->createReply($user);
        $this->invisiblePost()->modelSave();

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertCreated();
    }

    public function testUpdateOwnerReplyOnVisiblePostOnDisabledBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $reply = $this->createReply($user);
        $this->disableBoard()->modelSave();

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertCreated();
    }

    public function testUpdateOwnerReplyOnInvisiblePostOnDisabledBoardByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $reply = $this->createReply($user);
        $this->invisiblePost()->disableBoard()->modelSave();

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertCreated();
    }

    public function testUpdateOtherAssociateReplyByBackoffice()
    {
        $user = $this->createAsQpickUser('associate');
        $reply = $this->createReply($user);

        $otherUser = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertForbidden();
    }

    public function testUpdateOtherRegularReplyByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $reply = $this->createReply($user);

        $otherUser = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', $this->url . '/' . $reply->id, $this->updateResource);

        $response
            ->assertForbidden();
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('delete', $this->url . '/' . $reply->id);

        $response
            ->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $reply = $this->createReply($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', $this->url . '/' . $reply->id);

        $response
            ->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $reply = $this->createReply($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', $this->url . '/' . $reply->id);

        $response
            ->assertForbidden();
    }

    public function testDestroyOwnerReplyByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $reply = $this->createReply($user);

        $response = $this->requestQpickApi('delete', $this->url . '/' . $reply->id);

        $response
            ->assertNoContent();
    }

    public function testDestroyOtherReplyByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $reply = $this->createReply($user);

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', $this->url . '/' . $reply->id);

        $response
            ->assertForbidden();
    }


}
