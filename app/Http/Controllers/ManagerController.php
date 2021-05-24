<?php

namespace App\Http\Controllers;

use App\Libraries\CollectionLibrary;
use App\Models\{Manager, User, Authority};
use App\Http\Requests\Members\Managers\StoreManagerRequest;
use Illuminate\Support\Collection;
use Illuminate\Http\Response;

class ManagerController extends Controller
{
    private $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Display a listing of the resource.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return CollectionLibrary::toCamelCase($this->manager::all());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreManagerRequest  $request
     * @return Collection
     */
    public function store(StoreManagerRequest $request): Collection
    {
        // Additional Validation
        User::findOrFail($request->get('user_id'));
        Authority::findOrFail($request->get('authority_id'));

        // store
        $manager = $this->manager;
        $manager->user_id = $request->get('user_id');
        $manager->authority_id = $request->get('authority_id');
        $manager->save();

        // response
        return CollectionLibrary::toCamelCase(collect($this->manager::find($manager->id)));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return CollectionLibrary::toCamelCase(collect($this->manager::findOrFail($id)));
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
