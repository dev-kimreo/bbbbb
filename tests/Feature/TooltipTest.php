<?php

namespace Tests\Feature;

use App\Models\Tooltip;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use Tests\Feature\Traits\QpickTestBase;
use Tests\TestCase;

class TooltipTest extends TestCase
{
    use DatabaseTransactions, QpickTestBase;

    protected array $resJsonForUser = [
        'id',
        'userId',
        'type',
        'title',
        'visible',
        'createdAt',
        'updatedAt',
        'contents' => ['*' => []],
        'code'
    ];

    protected array $resJsonForBackoffice = [
        'id',
        'userId',
        'type',
        'title',
        'visible',
        'createdAt',
        'updatedAt',
        'contents' => ['*' => []],
        'code',
        'user' => [
            'id',
            'name',
            'email'
        ],
        'backofficeLogs' => [
            '*' => [
                'id',
                'memo',
                'createdAt',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]
        ]
    ];

    public function testUserCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertStatus(401);
    }

    public function testUserListByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/tooltip/', []);
        $response->assertStatus(200);
    }

    public function testUserReadByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(200)->assertJsonStructure($this->resJsonForUser);
    }

    public function testUserUpdateByGuest()
    {
        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . rand(1, 10), $this->getReqJson());
        $response->assertStatus(401);
    }

    public function testUserDeleteByGuest()
    {
        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(401);
    }

    public function testUserCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserListByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/tooltip/', []);
        $response->assertStatus(200);
    }

    public function testUserReadByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(200)->assertJsonStructure($this->resJsonForUser);
    }

    public function testUserUpdateByRegular()
    {
        $this->actingAsQpickUser('regular');
        
        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . rand(1, 10), $this->getReqJson());
        $response->assertStatus(403);
    }

    public function testUserDeleteByRegular()
    {
        $this->actingAsQpickUser('regular');
        
        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(403);
    }

    public function testUserCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/tooltip/', $this->getReqJson());
        $response->assertStatus(201)->assertJsonStructure($this->resJsonForBackoffice);
    }

    public function testUserListByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/tooltip/', []);
        $response->assertStatus(200);
    }

    public function testUserReadByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(200)->assertJsonStructure($this->resJsonForBackoffice);
    }

    public function testUserUpdateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/tooltip/' . rand(1, 10), $this->getReqJson());
        $response->assertStatus(201)->assertJsonStructure($this->resJsonForBackoffice);
    }

    public function testUserDeleteByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/tooltip/' . rand(1, 10), []);
        $response->assertStatus(204);
    }

    public function getReqJson(): array
    {
        return [
            'title' => Str::random(rand(10, 50)),
            'type' => collect(Tooltip::$prefixes)->random(1)->pop(),
            'visible' => rand(0, 1),
            'content[ko]' => Str::random(rand(10, 50)),
            'content[en]' => Str::random(rand(10, 50))
        ];
    }
}
