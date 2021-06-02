<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;
use Tests\Feature\Traits\QpickTestBase;

class UserTest extends TestCase
{
    use DatabaseTransactions, QpickTestBase;

    /**
     * A basic test example.
     *
     * @return void
     */
    public function testUserByUser()
    {
        $user = $this->actingAsQpickUser();
        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'emailVerifiedAt',
                'grade',
                'mallType',
                'mallName',
                'mallUrl',
                'language',
                'registeredAt',
                'inactivatedAt',
                'lastAuthorizedAt',
                'createdAt',
                'updatedAt',
                'advAgree',
                'solutions'
            ]);
    }

    public function testUserByManager()
    {
        $user = $this->actingAsQpickManager();
        $response = $this->requestQpickApi('get', '/v1/user/2', []);
        $response
            ->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'emailVerifiedAt',
                'grade',
                'mallType',
                'mallName',
                'mallUrl',
                'language',
                'registeredAt',
                'inactivatedAt',
                'lastAuthorizedAt',
                'createdAt',
                'updatedAt',
                'advAgree',
                'solutions'
            ]);
    }

    public function testAdvAgreeByUser()
    {
        $this->assertTrue(true);
    }

    public function testAdvAgreeByManager()
    {
        $this->assertTrue(true);
    }

    public function testLinkedSolutionByUser()
    {
        $this->assertTrue(true);
    }

    public function testLinkedSolutionByManager()
    {
        $this->assertTrue(true);
    }
}
