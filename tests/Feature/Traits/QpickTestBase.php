<?php

namespace Tests\Feature\Traits;

use App\Models\Manager;
use App\Models\Users\User;
use App\Models\Users\UserPartner;
use App\Models\Users\UserPrivacyActive;
use Hash;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait QpickTestBase
{
    protected string $accessToken = '';
    protected string $userPassword = 'password!1';

    protected function createAsQpickUser(string $role)
    {
        User::status('active');

        $newArrays = [
            'password' => Hash::make($this->userPassword)
        ];

        switch ($role) {
            case 'associate':
                $newArrays['grade'] = 0;
                break;

            case 'regular':
                $newArrays['grade'] = 1;
                break;

            case 'partner':
                $newArrays['grade'] = 1;
                break;

            case 'backoffice':
                $user = User::find(Manager::offset(1)->first()->user_id);
                break;
        }

        if ($role == 'partner') {
            $user = User::factory()->has(
                UserPrivacyActive::factory(), 'privacy'
            )->has(
                UserPartner::factory(), 'partner'
            )->create($newArrays);
        } elseif ($role != 'backoffice') {
            $user = User::factory()->has(
                UserPrivacyActive::factory(), 'privacy'
            )->create($newArrays);
        }

        return $user;
    }

    protected function actingAsQpickUser(string $role): User
    {
        $user = $this->createAsQpickUser($role);

        switch ($role) {
            case 'associate':
            case 'regular':
                $this->getPassportToken($user, 'front');
                break;

            case 'backoffice':
                $this->getPassportToken($user);
                break;

            case 'partner':
                $this->getPassportToken($user, 'partner');
                break;
        }

        return $user;
    }

    protected function getPassportToken($user, $service = 'crm')
    {
        $oauth_client = Client::where(['name' => 'qpicki_' . $service])->firstOrFail();

        $body = [
            'username' => $user->privacy->email,
            'password' => $this->userPassword,
            'grant_type' => 'password',
            'client_id' => $oauth_client->id,
            'client_secret' => $oauth_client->secret,
        ];

        $response = $this->postJson('/v1/user/auth', $body)->getContent();
        $this->accessToken = json_decode($response)->accessToken;
    }

    protected function requestQpickApi(string $method, string $url, array $param = [])
    {
        $header = [];

        if ($this->accessToken) {
            $header['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        switch ($method) {
            case 'get':
                $response = $this->getJson($url, $header);
                break;
            case 'post':
                $response = $this->postJson($url, $param, $header);
                break;
            case 'patch':
                $response = $this->patchJson($url, $param, $header);
                break;
            case 'delete':
                $response = $this->deleteJson($url, $param, $header);
                break;
            default:
                throw new MethodNotAllowedHttpException(['GET', 'POST', 'PATCH', 'DELETE']);
        }

        return $response;
    }
}
