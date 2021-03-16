<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

/**
 * @OA\Info(
 *    title="Your super  ApplicationAPI",
 *    version="1.0.0",
 * )
 *
 *
 */

/**
 * @OA\Tag(
 *     name="Commons",
 *     description="공통 관련"
 * )
 *
 *
 * @OA\Tag(
 *     name="Members",
 *     description="회원 관련"
 * )
 *
 *
 *
 */


/**
 * @OA\SecurityScheme(
 *   securityScheme="davinci_auth",
 *   type="http",
 *   scheme="bearer"
 * )
 */

/**
 * @OA\SecurityScheme(
 *   securityScheme="sample_auth",
 *   type="oauth2",
 *   @OA\Flow(
 *      authorizationUrl="http://petstore.swagger.io/oauth/dialog",
 *      flow="implicit",
 *      scopes={
 *         "read:pets": "read your pets",
 *         "write:pets": "modify pets in your account"
 *      }
 *   )
 * )
 */

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
}
