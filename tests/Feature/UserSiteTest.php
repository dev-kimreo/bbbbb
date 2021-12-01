<?php

namespace Tests\Feature;

use App\Models\Solution;
use App\Models\Users\User;
use App\Models\Users\UserSite;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class UserSiteTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];
    protected array $searchResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'solution_id' => Solution::factory()->create()->id,
            'type' => '남성의류',
            'name' => $this->faker->text(16),
            'url' => $this->faker->url,
            'solutionUserId' => $this->faker->text(16),
            'apikey' => $this->faker->text(16),
        ];

    }

    protected function getFactory()
    {
        return UserSite::factory()
            ->for(User::factory()->create(), 'user')
            ->for(Solution::factory()->create(), 'solution');
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/site', $this->createResource);
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/site', $this->createResource);
        $response->assertCreated();
    }

    public function testCreateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/site', $this->createResource);
        $response->assertCreated();
    }

    public function testCreateOtherOwnerByRegular()
    {
        $other = $this->createAsQpickUser('regular');
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/' . $other->id . '/site', $this->createResource);
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/site', $this->createResource);
        $response->assertCreated();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertCreated();
    }

    public function testUpdateByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertCreated();
    }

    public function testUpdateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertCreated();
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertNoContent();
    }

    public function testDestroyByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertNoContent();
    }

    public function testDestroyByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $site = $this->getFactory()->create(['user_id' => $user->id]);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id . '/site/' . $site->id, $this->updateResource);
        $response->assertNoContent();
    }


}
