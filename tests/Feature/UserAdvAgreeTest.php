<?php

namespace Tests\Feature;

use App\Models\UserAdvAgree;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class UserAdvAgreeTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'agree' => 1
        ];

    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testUpdateOwnerByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertCreated();
    }

    public function testUpdateOtherByAssociate()
    {
        $user = $this->createAsQpickUser('associate');
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertForbidden();
    }

    public function testUpdateOwnerByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertCreated();
    }

    public function testUpdateOtherByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertForbidden();
    }

    public function testUpdateOwnerByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertCreated();
    }

    public function testUpdateOtherByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/adv-agree?' . Arr::query($this->createResource));
        $response->assertCreated();
    }


}
