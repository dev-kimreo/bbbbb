<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Members\Managers\StoreManagerRequest;
use App\Http\Requests\Members\Managers\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Libraries\StringLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\{Manager, User, Authority};
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class ManagerController extends Controller
{
    private Manager $manager;

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
     *              required={},
     *              @OA\Property(property="authorityId", type="integer", example=2, description="관리자 그룹"),
     *              @OA\Property(property="id", type="integer", example=1, description="회원 번호"),
     *              @OA\Property(property="email", type="string", example="abcd@qpicki.com", description="ID(메일)"),
     *              @OA\Property(property="name", type="string", example="홍길동", description="이름"),
     *              @OA\Property(property="multiSearch", type="string", example="홍길동", description="전체 검색"),
     *              @OA\Property(property="page", type="integer", example=1, description="페이지" ),
     *              @OA\Property(property="perPage", type="integer", example=15, description="한 페이지에 보여질 수" ),
     *              @OA\Property(property="sortBy", type="string", example="+user.id", description="정렬기준<br/>+:오름차순, -:내림차순" )
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
     * @param Request $request
     * @return array
     * @throws \App\Exceptions\QpickHttpException
     */
    public function index(Request $request): array
    {
        $manager = DB::table('managers as manager')
            ->select('manager.*')
            ->whereNull('manager.deleted_at');

        $manager->join('users AS user', 'manager.user_id', 'user.id');
        $manager->leftjoin('user_privacy_active AS privacy', 'manager.user_id', 'user.id');

        if ($s = $request->input('authority_id')) {
            $manager->where('authority_id', $s);
        }

        if ($s = $request->input('name')) {
            $manager->where('privacy.name', $s);
        }

        if ($s = $request->input('id')) {
            $manager->where('user.id', $s);
        }

        if ($s = $request->input('email')) {
            $manager->where('privacy.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');
        }

        // 통합 검색
        if ($s = $request->input('multi_search')) {
            $manager->where(function ($q) use ($s) {
                $q->orWhere('privacy.name', $s);
                $q->orWhere('privacy.email', 'like', '%' . StringLibrary::escapeSql($s) . '%');

                if (is_numeric($s)) {
                    $q->orWhere('user.id', $s);
                }
            });
        }

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['user.id']);
            $sortCollect->each(function ($item) use ($manager) {
                $manager->orderBy($item['key'], $item['value']);
            });
        }

        // Set Pagination Information
        $pagination = PaginationLibrary::set($request->input('page'), $manager->count(), $request->input('per_page'));

        // Get Data from DB
        $data = $manager->skip($pagination['skip'])->take($pagination['perPage'])->get();

        $data->each(function (&$item) {
            $item->user = $this->getUser($item->user_id);
            $item->authority = $this->getAuthority($item->authority_id);
            unset($item->updated_at, $item->deleted_at);
        });

        return [
            'header' => $pagination,
            'list' => $data
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
     *          response=201,
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
     * @param StoreManagerRequest $request
     * @return JsonResponse
     */
    public function store(StoreManagerRequest $request): JsonResponse
    {
        // Additional Validation
        User::findOrFail($request->get('user_id'));
        Authority::findOrFail($request->get('authority_id'));

        // store
        $manager = $this->manager::firstOrCreate(
            ['user_id' => $request->input('user_id')],
            ['authority_id' => $request->input('authority_id')]
        );

        // response
        return response()->json($this->manager::find($manager->id), 201);
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
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return collect($this->manager::findOrFail($id));
    }


    /**
     * @OA\Patch(
     *      path="/v1/manager/{id}",
     *      summary="관리자 수정",
     *      description="1명의 관리자에 대한 상세정보를 수정합니다",
     *      operationId="updateManagerInfo",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"authorityId"},
     *              @OA\Property(property="authorityId", type="integer", example="5", description="관리자그룹의 고유번호(PK)")
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
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
     * @param UpdateRequest $req
     * @param $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $req, $id): JsonResponse
    {
        $this->manager = $this->manager::findOrFail($id);
        $this->manager->delete();

        $managerArray = $this->manager->toArray();
        unset($managerArray['id'], $managerArray['deleted_at']);

        $manager = $this->manager->create(array_merge($managerArray, $req->all()));

        return response()->json($this->manager->findOrFail($manager->id), 201);
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
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        Manager::destroy($id);
        return response()->noContent();
    }


    /* Custom Methods */
    protected function getUser($id)
    {
        static $users = [];

        return $users[$id] ?? ($users[$id] = User::status('active')->simplify('user')->find($id));
    }

    protected function getAuthority($id)
    {
        static $authorities = [];

        return $authorities[$id] ?? ($authorities[$id] = Authority::find($id));
    }
}
