<?php

namespace App\Http\Controllers;

use App\Models\{Manager, User, Authority};
use App\Http\Requests\Members\Managers\StoreManagerRequest;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ManagerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return Manager::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreManagerRequest  $request
     * @return Manager
     */
    public function store(StoreManagerRequest $request): Manager
    {
        // Additional Validation
        User::findOrFail($request->get('user_id'));
        Authority::findOrFail($request->get('authority_id'));

        // store
        $manager = new Manager;
        $manager->user_id = $request->get('user_id');
        $manager->authority_id = $request->get('authority_id');
        $manager->save();

        // response
        return Manager::find($manager->id);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Manager
     */
    public function show(int $id): Manager
    {
        return Manager::findOrFail($id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        Manager::destroy($id);
        return response()->noContent();
    }
}
