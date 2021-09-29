<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Tests\Feature\Traits\QpickTestBase;

class UserTest extends TestCase
{
    use WithFaker, DatabaseTransactions, QpickTestBase;

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
            'lastPasswordChangedAt',
            'createdAt',
            'updatedAt',
            'advAgree',
            'sites'
        ];
    }

    public function getReqJson(): array
    {
        return [
            'name' => $this->faker->lastName,
            'email' => $this->faker->unique()->safeEmail,
            'password' => $this->userPassword,
            'password_confirmation' => $this->userPassword
        ];
    }

    /**
     * Create
     */

    public function testCreateByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertCreated();
    }

    public function testCreateByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertForbidden();
    }

    public function testCreateByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertForbidden();
    }

    public function testCreateByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/', $this->getReqJson());
        $response->assertForbidden();
    }


    /**
     * Index
     */
    public function testIndexByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/user', []);
        $response->assertUnauthorized();
    }

    public function testIndexByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/user', []);
        $response->assertForbidden();
    }

    public function testIndexByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user', []);
        $response->assertForbidden();
    }

    public function testIndexByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/user', []);
        $response->assertOk();
    }


    /**
     * Show
     */

    public function testShowByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertUnauthorized();
    }

    public function testShowOwnerByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertOk()
            ->assertJsonStructure($this->resJson);
    }

    public function testShowOtherByAssociate()
    {
        $user = $this->createAsQpickUser('associate');
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertForbidden();
    }

    public function testShowOwnerByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertOk()
            ->assertJsonStructure($this->resJson);
    }

    public function testShowOtherByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertForbidden();
    }

    public function testShowOwnerByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertOk()
            ->assertJsonStructure($this->resJson);
    }

    public function testShowOtherByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('backoffice');

        // try to access others
        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id, []);
        $response->assertOk()
            ->assertJsonStructure($this->resJson);
    }


    /**
     * Update
     */
    public function testUpdateByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, []);
        $response->assertUnauthorized();
    }

    public function testUpdateOwnerByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertCreated()
            ->assertJsonStructure($this->resJson);
    }

    public function testUpdateOtherByAssociate()
    {
        $user = $this->createAsQpickUser('associate');
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertForbidden();
    }


    public function testUpdateOwnerByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertCreated()
            ->assertJsonStructure($this->resJson);
    }

    public function testUpdateOtherByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertForbidden();
    }

    public function testUpdateOwnerByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertCreated()
            ->assertJsonStructure($this->resJson);
    }

    public function testUpdateOtherByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('patch', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertCreated()->assertJsonStructure($this->resJson);
    }


    /**
     * Destroy
     */
    public function testDestroyByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, []);
        $response->assertUnauthorized();
    }

    public function testDestroyOwnerByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertNoContent();
    }

    public function testUserDeleteOtherByAssociate()
    {
        $user = $this->createAsQpickUser('associate');
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertForbidden();
    }


    public function testDestroyOwnerByRegular()
    {
        $user = $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertNoContent();
    }

    public function testUserDeleteOtherByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertForbidden();
    }

    public function testDestroyOwnerByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertNoContent();
    }

    public function testDestroyOtherByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('delete', '/v1/user/' . $user->id, $this->getReqJson());
        $response->assertNoContent();
    }


    /**
     * Non-CRUD
     */

    public function testSendEmailVerificationByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/user/email-verification');
        $response->assertUnauthorized();
    }

    public function testSendEmailVerificationByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/email-verification');
        $response->assertNoContent();
    }

    public function testSendEmailVerificationByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/email-verification');
        $response->assertNoContent();
    }


    // function checkPassword()
    public function testCheckPasswordByGuest()
    {
        $response = $this->requestQpickApi('post', '/v1/user/password');
        $response->assertUnauthorized();
    }

    public function testCheckPasswordByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/password', ['password' => $this->userPassword]);
        $response->assertNoContent();
    }

    public function testCheckPasswordByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/password', ['password' => $this->userPassword]);
        $response->assertNoContent();
    }

    public function testCheckPasswordByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/password', ['password' => $this->userPassword]);
        $response->assertNoContent();
    }


    // function modifyPassword()
    public function testModifyPasswordByGuest()
    {
        $response = $this->requestQpickApi('patch', '/v1/user/password');
        $response->assertUnauthorized();
    }

    public function testModifyPasswordByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/password', [
            'password' => $this->userPassword,
            'changePassword' => 'password!2',
            'passwordConfirmation' => 'password!2'
        ]);

        $response->assertNoContent();
    }

    public function testModifyPasswordByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/password', [
            'password' => $this->userPassword,
            'changePassword' => 'password!2',
            'passwordConfirmation' => 'password!2'
        ]);

        $response->assertNoContent();
    }

    public function testModifyPasswordByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/password', [
            'password' => $this->userPassword,
            'changePassword' => 'password!2',
            'passwordConfirmation' => 'password!2'
        ]);

        $response->assertNoContent();
    }


    // function personalClientLogin
    public function testPersonalLoginByGuest()
    {
        $user = $this->createAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/auth');
        $response->assertUnauthorized();
    }

    public function testPersonalLoginByAssociate()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/auth');
        $response->assertForbidden();
    }

    public function testPersonalLoginByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/auth');
        $response->assertForbidden();
    }

    public function testPersonalLoginByBackoffice()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('post', '/v1/user/' . $user->id . '/auth');
        $response->assertOk();
    }

    // function getLoginLog
    public function testGetLoginLogByGuest()
    {
        $user = $this->createAsQpickUser('regular');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertUnauthorized();
    }

    public function testGetLoginLogOwnerByAssociate()
    {
        $user = $this->actingAsQpickUser('associate');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertOk();
    }

    public function testGetLoginLogOtherByAssociate()
    {
        $user = $this->createAsQpickUser('associate');
        $this->actingAsQpickUser('associate');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertForbidden();
    }

    public function testGetLoginLogOwnerByRegular()
    {
        $user = $this->actingAsQpickUser('regular');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertOk();
    }

    public function testGetLoginLogOtherByRegular()
    {
        $user = $this->createAsQpickUser('regular');
        $this->actingAsQpickUser('regular');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertForbidden();
    }

    public function testGetLoginLogOwnerByBackoffice()
    {
        $user = $this->actingAsQpickUser('backoffice');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertOk();
    }

    public function testGetLoginLogOtherByBackoffice()
    {
        $user = $this->createAsQpickUser('backoffice');
        $this->actingAsQpickUser('backoffice');
        $query = http_build_query([
            'start_date' => Carbon::yesterday()->format('Y-m-d'),
            'end_date' => Carbon::tomorrow()->format('Y-m-d')
        ]);

        $response = $this->requestQpickApi('get', '/v1/user/' . $user->id . '/login-log?' . $query);
        $response->assertOk();
    }

    // function getStatUserByGrade
    public function testGetStatUserByGradeByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/statistics/user/count-per-grade');
        $response->assertUnauthorized();
    }

    public function testGetStatUserByGradeByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/statistics/user/count-per-grade');
        $response->assertForbidden();
    }

    public function testGetStatUserByGradeByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/statistics/user/count-per-grade');
        $response->assertForbidden();
    }

    public function testGetStatUserByGradeByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $response = $this->requestQpickApi('get', '/v1/statistics/user/count-per-grade');
        $response->assertOk();
    }

    // function getCountLoginLogPerGrade
    protected function getCountLoginLogPerGradeReqStructure(): string
    {
        $req = [
            'startDate' => Carbon::now()->subDays(7)->toString(),
            'endDate' => Carbon::now()->toString(),
        ];

        return http_build_query($req);
    }

    public function testGetCountLoginLogPerGradeByGuest()
    {
        $response = $this->requestQpickApi('get', '/v1/statistics/user/login-log/count-per-grade?' . $this->getCountLoginLogPerGradeReqStructure());
        $response->assertUnauthorized();
    }

    public function testGetCountLoginLogPerGradeByAssociate()
    {
        $this->actingAsQpickUser('associate');

        $response = $this->requestQpickApi('get', '/v1/statistics/user/login-log/count-per-grade?' . $this->getCountLoginLogPerGradeReqStructure());
        $response->assertForbidden();
    }

    public function testGetCountLoginLogPerGradeByRegular()
    {
        $this->actingAsQpickUser('regular');

        $response = $this->requestQpickApi('get', '/v1/statistics/user/login-log/count-per-grade?' . $this->getCountLoginLogPerGradeReqStructure());
        $response->assertForbidden();
    }

    public function testGetCountLoginLogPerGradeByBackoffice()
    {
        $this->actingAsQpickUser('backoffice');

        $param = $this->getCountLoginLogPerGradeReqStructure();
        $response = $this->requestQpickApi('get', '/v1/statistics/user/login-log/count-per-grade?' . $param);
        $response->assertOk();
    }


}
