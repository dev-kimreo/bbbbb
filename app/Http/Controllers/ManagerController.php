<?php

namespace App\Http\Controllers;

use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use Illuminate\Http\Request;
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
     * @OA\Get(
     *      path="/v1/manager",
     *      summary="관리자 목록",
     *      description="관리자의 전체목록을 표시합니다",
     *      operationId="managerGetList",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={}
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination" ),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(ref="#/components/schemas/Manager")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
    /**
     * Display a listing of the resource.
     *
     * @return array
     */
    public function index(Request $request): array
    {
        $data = $this->manager::all();

        return [
            'header' => PaginationLibrary::set($request->page, $data->count(), $request->per_page),
            'list' => CollectionLibrary::toCamelCase($data)
        ];
    }

    /**
     * @OA\Post (
     *      path="/v1/manager",
     *      summary="관리자 등록",
     *      description="기존 사용자를 새로운 관리자로 지정합니다",
     *      operationId="managerCreate",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"userId", "authorityId"},
     *              @OA\Property(property="userId", type="integer", example="2", description="사용자의 고유번호(PK)"),
     *              @OA\Property(property="authorityId", type="integer", example="5", description="관리자그룹의 고유번호(PK)")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Manager")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */
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
     * @OA\Get(
     *      path="/v1/manager/{id}",
     *      summary="관리자 상세",
     *      description="1명의 관리자에 대한 상세정보를 표시합니다",
     *      operationId="managerGetInfo",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={}
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Manager")
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      )
     *  )
     */
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
     * @OA\Delete(
     *      path="/v1/manager/{id}",
     *      summary="관리자 삭제",
     *      description="기존에 등록된 관리자를 삭제합니다",
     *      operationId="managerDelete",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=204,
     *          description="successfully"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="unauthenticated"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="not found"
     *      ),
     *      security={{
     *          "davinci_auth":{}
     *      }}
     *  )
     */

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
