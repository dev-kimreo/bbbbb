<?php

namespace Tests\Feature;

use App\Models\Inquiries\Inquiry;
use App\Models\Inquiries\InquiryAnswer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class InquiryAnswerTest extends TestCase
{

    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];
    protected Inquiry $inquiry;

    public function setUp(): void
    {
        parent::setUp();

        $this->inquiry = $this->createInquiry($this->createAsQpickUser('regular'));

        $this->createResource = $this->updateResource = [
            'answer' => $this->faker->text(100),
        ];

        InquiryAnswer::unsetEventDispatcher();
    }

    protected function createInquiry($user)
    {
        return Inquiry::factory()->create([
            'user_id' => $user->id
        ]);
    }

    protected function createInquiryAnswer($user): Model
    {
        return $this->inquiry->answer()->create([
            'user_id' => $user->id,
            'answer' => $this->faker->text(100)
        ]);
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/inquiry/' . $this->inquiry->id . '/answer', $this->createResource);
        $response
            ->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/inquiry/' . $this->inquiry->id . '/answer', $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/inquiry/' . $this->inquiry->id . '/answer', $this->createResource);
        $response
            ->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/inquiry/' . $this->inquiry->id . '/answer', $this->createResource);
        $response
            ->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $this->inquiry->id . '/answer');
        $response
            ->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $this->inquiry->id . '/answer');
        $response
            ->assertForbidden();
    }

    public function testShowByRegular()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $this->inquiry->id . '/answer');
        $response
            ->assertForbidden();
    }

    public function testShowByBackoffice()
    {
        $this->createInquiryAnswer($this->actingAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('get', '/v1/inquiry/' . $this->inquiry->id . '/answer');
        $response
            ->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $this->createInquiryAnswer($this->actingAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('patch', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertCreated();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $this->createInquiryAnswer($this->createAsQpickUser('backoffice'));
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $this->createInquiryAnswer($this->actingAsQpickUser('backoffice'));

        $response = $this->requestQpickApi('delete', '/v1/inquiry/' . $this->inquiry->id . '/answer?' . Arr::query($this->updateResource));
        $response
            ->assertNoContent();
    }

}
