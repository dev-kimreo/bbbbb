<?php

namespace Tests\Feature;

use App\Models\BackofficeMenu;
use App\Models\BackofficePermission;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Laravel\Passport\Client;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class UserAuthTest extends TestCase
{
    use WithFaker, QpickTestBase, DatabaseTransactions;

    protected array $createResource = [];
    protected array $updateResource = [];


    public function setUp(): void
    {
        parent::setUp();

        $this->createResource = $this->updateResource = [
            'username' => '',
            'password' => '',
            'grantType' => 'password',
            'clientId' => '',
            'clientSecret' => ''
        ];
    }

    public function createReq($user, $service = 'front')
    {
        $client = Client::where(['name' => 'qpicki_' . $service])->first();

        $this->createResource['username'] = $user->email;
        $this->createResource['password'] = $this->userPassword;
        $this->createResource['clientId'] = $client->id;
        $this->createResource['clientSecret'] = $client->secret;
        return $this->createResource;
    }


    /**
     * Auth
     */
    public function testAuthRegularFrontByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user));
        $response->assertOk();
    }

    public function testAuthRegularBackofficeByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user, 'crm'));
        $response->assertForbidden();
    }

    public function testAuthAssociateFrontByGuest()
    {
        $user = $this->createAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user));
        $response->assertOk();
    }

    public function testAuthAssociateBackofficeByGuest()
    {
        $user = $this->createAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user, 'crm'));
        $response->assertForbidden();
    }

    public function testAuthManagerFrontByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user));
        $response->assertOk();
    }

    public function testAuthManagerBackofficeByGuest()
    {
        $user = $this->createAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/auth', $this->createReq($user, 'crm'));
        $response->assertOk();
    }


    /**
     * Auth Show
     */
    public function testShowByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/user/auth');
        $response->assertUnauthorized();
    }

    public function testShowByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/user/auth');
        $response->assertOk();
    }

    public function testShowByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/auth');
        $response->assertOk();
    }

    public function testShowByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/user/auth');
        $response->assertOk();
    }

    /**
     * Auth Destroy
     */
    public function testDestroyByGuest()
    {
        $response = $this->requestQpickApi('delete', '/v1/user/auth');
        $response->assertUnauthorized();
    }

    public function testDestroyByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/user/auth');
        $response->assertNoContent();
    }

    public function testDestroyByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/user/auth');
        $response->assertNoContent();
    }

    public function testDestroyByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/user/auth');
        $response->assertNoContent();
    }

}
