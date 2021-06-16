<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SiteRequest;
use App\Models\UserAdvAgree;

class UserAdvAgreeController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param int $user_id
     * @param SiteRequest $req
     * @return UserAdvAgree
     */
    public function update(SiteRequest $req, int $user_id): UserAdvAgree
    {
        // delete
        UserAdvAgree::where('user_id', $user_id)->first()->delete();

        // create
        return UserAdvAgree::create([
            'user_id' => $user_id,
            'agree' => boolval($req->input('agree'))
        ]);
    }
}
