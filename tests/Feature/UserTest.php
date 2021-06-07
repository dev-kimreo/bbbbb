<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\TestCase;
use Tests\Feature\Traits\QpickTestBase;

class UserTest extends TestCase
{
    use DatabaseTransactions, QpickTestBase;

    protected array $resJson = [];

    public function __construct()
    {
        parent::__construct();

        $this->resJson = [
            'id',
            'name',
            'email',
            'emailVerifiedAt',
            'grade',
            'language',
            'registeredAt',
            'inactivatedAt',
            'lastAuthorizedAt',
            'createdAt',
            'updatedAt',
            'advAgree',
            'sites'
        ];
    }

    public function testUserCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertStatus(201);
    }

    public function testUserListByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/user/', []);
        $response->assertStatus(401);
    }

    public function testUserReadByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/user/' . rand(1, 10), []);
        $response->assertStatus(401);
    }

    public function testUserUpdateByGuest()
    {
        $response = $this->requestQpickApi('patch', '/v1/user/' . rand(1, 10), []);
        $response->assertStatus(401);
    }

    public function testUserDeleteByGuest()
    {
        $response = $this->requestQpickApi('delete', '/v1/user/' . rand(1, 10), []);
        $response->assertStatus(401);
    }

    public function testUserCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserListByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/', []);
        $response->assertStatus(403);
    }

    public function testUserReadOtherByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $other = $this->getExistsData($user->id);

        $response = $this->requestQpickApi('get', '/v1/user/' . $other->id, []);
        $response->assertStatus(403);
    }

    public function testUserUpdateOtherByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $other = $this->getExistsData($user->id);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $other->id, $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserDeleteOtherByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $other = $this->getExistsData($user->id);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $other->id, $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserReadOwnByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertStatus(200)->assertJsonStructure($this->resJson);
    }

    public function testUserUpdateOwnByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertStatus(201)->assertJsonStructure($this->resJson);
    }

    public function testUserDeleteOwnByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertStatus(204);
    }

    public function testUserCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserListByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/user/', []);
        $response->assertStatus(200);
    }

    public function testUserReadOtherByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $other = $this->getExistsData($user->id);

        // try to access others
        $response = $this->requestQpickApi('get', '/v1/user/' . $other->id, []);
        $response->assertStatus(200)->assertJsonStructure($this->resJson);
    }

    public function testUserUpdateOtherByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $other = $this->getExistsData($user->id);

        $response = $this->requestQpickApi('patch', '/v1/user/' . $other->id, $this->getReqJson());
        $response->assertStatus(201)->assertJsonStructure($this->resJson);
    }

    public function testUserDeleteOtherByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $other = $this->getExistsData($user->id);

        $response = $this->requestQpickApi('delete', '/v1/user/' . $other->id, $this->getReqJson());
        $response->assertStatus(204);
    }

    public function testUserReadOwnByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertStatus(200)->assertJsonStructure($this->resJson);
    }

    public function testUserUpdateOwnByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertStatus(201)->assertJsonStructure($this->resJson);
    }

    public function testUserDeleteOwnByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertStatus(204);
    }

    public function getExistsData($exceptId): Model
    {
        return User::where('id', '!=', $exceptId)->get()->random(1)->first();
    }

    public function getReqJson(): array
    {
        return [
            'name' => Str::random(10) . ' ' . Str::random(10),
            'email' => Str::random(20) . '@qpick.cocen.com',
            'password' => $this->userPassword,
            'password_confirmation' => $this->userPassword
        ];
    }
}
