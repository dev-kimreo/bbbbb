<?php

namespace App\Http\Controllers;

use App\Libraries\CollectionLibrary;
use App\Libraries\PaginationLibrary;
use App\Models\Authority;
use App\Http\Requests\Members\Authorities\StoreAuthorityRequest;
use App\Http\Requests\Members\Authorities\UpdateAuthorityRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class AuthorityController extends Controller
{
    /**
     * @OA\Get(
     *      path="/v1/authority",
     *      summary="관리자그룹 목록",
     *      description="관리자그룹의 전체목록을 표시합니다",
     *      operationId="authorityGetList",
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
     *                  @OA\Items(ref="#/components/schemas/Authority")
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
        $data = Authority::all();

        return [
            'header' => PaginationLibrary::set($request->page, $data->count(), $request->per_page),
            'list' => CollectionLibrary::toCamelCase($data)
        ];
    }

    /**
     * @OA\Post (
     *      path="/v1/authority",
     *      summary="관리자그룹 등록",
     *      description="새로운 관리자그룹을 등록합니다",
     *      operationId="authorityCreate",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={"code", "title", "display_name"},
     *              @OA\Property(property="code", type="string", example="120", description="그룹번호" ),
     *              @OA\Property(property="title", type="string", example="시스템관리자", description="그룹명" ),
     *              @OA\Property(property="displayName", type="string", example="운영자", description="닉네임" ),
     *              @OA\Property(property="memo", type="string", example="큐픽 사이트 운영", description="설명" )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
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
     * @param  Request|StoreAuthorityRequest  $request
     * @return Collection
     */
    public function store(StoreAuthorityRequest $request): Collection
    {
        // store
        $authority = new Authority;
        $authority->code = $request->get('code');
        $authority->title = $request->get('title');
        $authority->display_name = $request->get('display_name');
        $authority->memo = $request->get('memo');
        $authority->save();

        return CollectionLibrary::toCamelCase(collect(Authority::find($authority->id)));
    }

    /**
     * @OA\Get(
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 상세",
     *      description="관리자그룹 1개의 상세정보를 표시합니다",
     *      operationId="authorityGetInfo",
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
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
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
        return CollectionLibrary::toCamelCase(collect(Authority::findOrFail($id)));
    }

    /**
     * @OA\Put (
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 수정",
     *      description="기존에 등록된 관리자그룹을 수정합니다",
     *      operationId="authorityModify",
     *      tags={"관리자"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              required={},
     *              @OA\Property(property="code", type="string", example="13", description="그룹번호" ),
     *              @OA\Property(property="title", type="string", example="시스템관리자", description="그룹명" ),
     *              @OA\Property(property="displayName", type="string", example="운영자", description="닉네임" ),
     *              @OA\Property(property="memo", type="string", example="큐픽 사이트 운영", description="설명" )
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(ref="#/components/schemas/Authority")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      )
     *  )
     */

    /**
     * Update the specified resource in storage.
     *
     * @param  Request|UpdateAuthorityRequest  $request
     * @param  int  $id
     * @return Collection
     */
    public function update(UpdateAuthorityRequest $request, int $id): Collection
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
        return CollectionLibrary::toCamelCase(collect(Authority::find($id)));
    }

    /**
     * @OA\Delete(
     *      path="/v1/authority/{id}",
     *      summary="관리자그룹 삭제",
     *      description="기존에 등록된 관리자그룹을 삭제합니다",
     *      operationId="authorityDelete",
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
        Authority::destroy($id);
        return response()->noContent();
    }
}
