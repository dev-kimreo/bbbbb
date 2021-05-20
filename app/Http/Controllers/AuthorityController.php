<?php

namespace App\Http\Controllers;

use App\Models\Authority;
use App\Http\Requests\Members\Authorities\StoreAuthorityRequest;
use App\Http\Requests\Members\Authorities\UpdateAuthorityRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthorityController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return Authority::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request|StoreAuthorityRequest  $request
     * @return Authority
     */
    public function store(StoreAuthorityRequest $request): Authority
    {
        // store
        $authority = new Authority;
        $authority->code = $request->get('code');
        $authority->title = $request->get('title');
        $authority->display_name = $request->get('display_name');
        $authority->memo = $request->get('memo');
        $authority->save();

        return Authority::find($authority->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Authority
     */
    public function show(int $id): Authority
    {
        return Authority::findOrFail($id);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request|UpdateAuthorityRequest  $request
     * @param  int  $id
     * @return Authority
     */
    public function update(UpdateAuthorityRequest $request, int $id): Authority
    {
        // getting original data
        $authority = Authority::findOrFail($id);

        // update
        $authority->code = $request->get('code') ?? $authority->code;
        $authority->title = $request->get('title') ?? $authority->title;
        $authority->display_name = $request->get('display_name') ?? $authority->display_name;
        $authority->memo = $request->get('memo') ?? $authority->memo;
        $authority->saveOrFail();

        // response
        return Authority::find($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        Authority::destroy($id);
        return response()->noContent();
    }
}
