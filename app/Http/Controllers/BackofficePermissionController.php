<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\BackofficeMenus\Permissions\StoreRequest;
use App\Models\BackofficeMenu;
use App\Models\BackofficePermission;
use Illuminate\Http\Request;
use Illuminate\Http\Response;


class BackofficePermissionController extends Controller
{
    private BackofficePermission $permission;

    public function __construct(BackofficePermission $permission)
    {
        $this->permission = $permission;
    }

    public function store(StoreRequest $req): BackofficePermission
    {
        $menuCollect = BackofficeMenu::find($req->input('backoffice_menu_id'));
        if (!$menuCollect->last) {
            throw new QpickHttpException(422, 'menu.permission.only.last');
        }

        $this->permission = $this->permission->create($req->all());
        $this->permission->refresh();

        return $this->permission;
    }

    public function show(Request $req, $permission_id)
    {
        return $this->permission->findOrfail($permission_id);
    }

    public function index(Request $req)
    {
        return $this->permission->all();
    }

    public function destroy(Request $req, $permission_id): Response
    {
        $this->permission->findOrFail($permission_id)->delete();

        return response()->noContent();
    }
}
