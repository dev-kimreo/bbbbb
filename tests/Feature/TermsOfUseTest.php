<?php

namespace Tests\Feature;

use App\Models\TermsOfUse;
use App\Models\Translation;
use App\Models\TranslationContent;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class TermsOfUseTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];
    protected array $searchResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = [
            'title' => $this->faker->realText(16),
            'content' => [
                'ko' => $this->faker->realText(200),
            ],
            'history' => $this->faker->realText(100)
        ];

        $this->updateResource = [
            'title' => $this->faker->realText(16),
            'content' => [
                'ko' => $this->faker->realText(200),
            ],
            'history' => $this->faker->realText(100)
        ];

        $this->searchResource = [
            'service' => collect(array_keys(TermsOfUse::$services))->random(1)->pop(),
            'type' => collect(array_keys(TermsOfUse::$types))->random(1)->pop(),
        ];
    }

    protected function createReq($type = null, $startedAt = null)
    {
        $this->createResource['service'] = collect(array_keys(TermsOfUse::$services))->random(1)->pop();
        $this->createResource['type'] = collect(array_keys(TermsOfUse::$types))->random(1)->pop();
        $this->createResource['startedAt'] = $startedAt ?? Carbon::now()->addWeeks(1);
        return $this->createResource;
    }

    protected function createTermsOfUse($user, $startedAt = null)
    {
        $res = [];
        $res['user_id'] = $user->id;

        $res['service'] = collect(array_keys(TermsOfUse::$services))->random(1)->pop();
        $res['type'] = collect(array_keys(TermsOfUse::$types))->random(1)->pop();

        if ($startedAt) {
            $res['started_at'] = $startedAt;
        }

        $terms = TermsOfUse::factory()
            ->hasTranslation(1)
            ->create($res ?? []);

        TranslationContent::factory()->create([
            'translation_id' => $terms->translation()->first()->id
        ]);

        return $terms;
    }

    /**
     * Create
     */
    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/terms-of-use', $this->createReq());
        $response->assertUnauthorized();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/terms-of-use', $this->createReq());
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/terms-of-use', $this->createReq());
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/terms-of-use', $this->createReq());
        $response->assertCreated();
    }

    /**
     * Show
     */
    public function testShowByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('get', '/v1/terms-of-use/' . $terms->id);
        $response->assertOk();
    }

    public function testShowByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/terms-of-use/' . $terms->id);
        $response->assertOk();
    }

    public function testShowByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/terms-of-use/' . $terms->id);
        $response->assertOk();
    }

    public function testShowByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('get', '/v1/terms-of-use/' . $terms->id);
        $response->assertOk();
    }

    /**
     * index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/terms-of-use?' . Arr::query($this->searchResource));
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/terms-of-use?' . Arr::query($this->searchResource));
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/terms-of-use?' . Arr::query($this->searchResource));
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/terms-of-use?' . Arr::query($this->searchResource));
        $response->assertOk();
    }

    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('patch', '/v1/terms-of-use/' . $terms->id, $this->updateResource);
        $response->assertUnauthorized();
    }

    public function testUpdateByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/terms-of-use/' . $terms->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/terms-of-use/' . $terms->id, $this->updateResource);
        $response->assertForbidden();
    }

    public function testUpdateByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('patch', '/v1/terms-of-use/' . $terms->id, $this->updateResource);
        $response->assertCreated();
    }

    public function testUpdateOverStartedAtByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user, Carbon::now()->addWeeks(-1));

        $response = $this->requestQpickApi('patch', '/v1/terms-of-use/' . $terms->id, $this->updateResource);

        // Started_at 를 지나 수정이 불가
        $response->assertStatus(422);
    }

    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('delete', '/v1/terms-of-use/' . $terms->id);
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/terms-of-use/' . $terms->id);
        $response->assertForbidden();
    }

    public function testDestroyByRegular()
    {
        $user = $this->createAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/terms-of-use/' . $terms->id);
        $response->assertForbidden();
    }

    public function testDestroyByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user);

        $response = $this->requestQpickApi('delete', '/v1/terms-of-use/' . $terms->id);
        $response->assertNoContent();
    }

    public function testDestroyOverStartedAtByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $terms = $this->createTermsOfUse($user, Carbon::now()->addWeeks(-1));

        $response = $this->requestQpickApi('delete', '/v1/terms-of-use/' . $terms->id);

        // Started_at 를 지나 수정이 불가
        $response->assertStatus(422);
    }


}
