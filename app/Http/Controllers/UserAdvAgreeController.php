<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\AdvAgreeUpdateRequest;
use App\Models\UserAdvAgree;

class UserAdvAgreeController extends Controller
{
    /**
     * Update the specified resource in storage.
     *
     * @param int $user_id
     * @param AdvAgreeUpdateRequest $req
     * @return UserAdvAgree
     */
    public function update(int $user_id, AdvAgreeUpdateRequest $req): UserAdvAgree
    {
        // delete
        UserAdvAgree::where('user_id', $user_id)->delete();

        // create
        return UserAdvAgree::create([
            'user_id' => $user_id,
            'agree' => boolval($req->input('agree'))
        ]);
    }
}