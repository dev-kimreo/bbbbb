<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SiteRequest;
use App\Models\UserAdvAgree;
use Illuminate\Http\JsonResponse;

class UserAdvAgreeController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param int $user_id
     * @param SiteRequest $req
     * @return JsonResponse
     */
    public function update(SiteRequest $req, int $user_id): JsonResponse
    {
        // delete
        $userAdvAgree = UserAdvAgree::where('user_id', $user_id)->first();
        if ($userAdvAgree) {
            $userAdvAgree->delete();
        }

        // create
        return response()->json(UserAdvAgree::create([
            'user_id' => $user_id,
            'agree' => boolval($req->input('agree'))
        ]), 201);

    }
}
