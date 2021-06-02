<?php

namespace Tests\Feature\Traits;

use App\Models\Manager;
use App\Models\User;
use Laravel\Passport\Passport;
use Laravel\Passport\Client;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;

trait QpickTestBase
{
    protected string $accessToken = '';

    protected function actingAsQpickUser()
    {
        $user = User::factory()->create();
        Passport::actingAs($user);

        return $user;
    }

    protected function actingAsQpickManager()
    {
        $user = User::find(Manager::first()->user_id);
        $this->getPassportToken($user);

        return $user;
    }

    protected function getPassportToken($user)
    {
        $oauth_client = Client::where(['name' => 'qpicki_crm'])->firstOrFail();

        $body = [
            'username' => $user->email,
            'password' => 'password',
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

        if($this->accessToken) {
            $header['Authorization'] = 'Bearer ' . $this->accessToken;
        }

        echo $this->accessToken . ' ' . date('H:i:s') . "\n";

        switch($method) {
            case 'get': $response = $this->getJson($url, $header); break;
            case 'post': $response = $this->postJson($url, $param, $header); break;
            case 'patch': $response = $this->patchJson($url, $param, $header); break;
            case 'delete': $response = $this->deleteJson($url, $param, $header); break;
            default:
                throw new MethodNotAllowedHttpException(['GET', 'POST', 'PATCH', 'DELETE']);
        }

        return $response;
    }
}