<?php

namespace Tests\Feature;

use App\Models\Inquiry;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class InquiryTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'title' => $this->faker->text(30),
            'question' => $this->faker->text(200)
        ];

    }

    protected function createInquiry($user)
    {
        return Inquiry::factory()->create([
            'user_id' => $user->id
        ]);
    }


    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/inquiry', array_merge(
            $this->createResource
        ));
        $response
            ->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('post', '/v1/inquiry', array_merge(
            $this->createResource
        ));
        $response
            ->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/inquiry', array_merge(
            $this->createResource
        ));
        $response
            ->assertCreated();
    }

    public function testCreateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/inquiry', array_merge(
            $this->createResource
        ));
        $response
            ->assertForbidden();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/inquiry');
        $response
            ->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('get', '/v1/inquiry');
        $response
            ->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $this->createInquiry($user);

        $response = $this->requestQpickApi('get', '/v1/inquiry');
        $response
            ->assertOk();

        $lists = collect($response->json('list'));
        $lists->each(function ($item) use ($user) {
            $this->assertTrue(
                $user->id == $item['user']['id'],
                'ERROR::작성자가 다른 유저의 1:1 문의가 나옵니다.'
            );
        });
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('get', '/v1/inquiry');
        $response
            ->assertOk();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertForbidden();
    }

    public function testShowByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertOk();
    }

    public function testShowByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertOk();
    }

    /**
     * update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '?' . Arr::query($this->updateResource));
        $response
            ->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '?' . Arr::query($this->updateResource));
        $response
            ->assertCreated();
    }

    public function testUpdateByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertNoContent();
    }

    public function testDestroyByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $inquiry->id);
        $response
            ->assertForbidden();
    }


    /**
     * Non-CRUD
     */
    public function testUpdateInquiryAssigneeByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $manager = $this->createAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '/assignee/' . $manager->id);
        $response
            ->assertUnauthorized();
    }

    public function testUpdateInquiryAssigneeByAssociate()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $manager = $this->createAsQpickUser('backoffice');

        $this->actingAsQpickUser('associate');
        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '/assignee/' . $manager->id);
        $response
            ->assertForbidden();
    }

    public function testUpdateInquiryAssigneeByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $manager = $this->createAsQpickUser('backoffice');

        $this->actingAsQpickUser('regular');
        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '/assignee/' . $manager->id);
        $response
            ->assertForbidden();
    }

    public function testUpdateInquiryAssigneeByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $inquiry = $this->createInquiry($user);

        $manager = $this->createAsQpickUser('backoffice');

        $this->actingAsQpickUser('backoffice');
        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $inquiry->id . '/assignee/' . $manager->id);
        $response
            ->assertCreated();
    }


    public function testGetCountPerStatusByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/inquiry/count-per-status');
        $response
            ->assertUnauthorized();
    }

    public function testGetCountPerStatusByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/inquiry/count-per-status');
        $response
            ->assertForbidden();
    }

    public function testGetCountPerStatusByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/inquiry/count-per-status');
        $response
            ->assertForbidden();
    }

    public function testGetCountPerStatusByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/inquiry/count-per-status');
        $response
            ->assertOk();
    }

}
