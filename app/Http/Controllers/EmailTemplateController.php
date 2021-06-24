<?php

namespace App\Http\Controllers;

use App\Exceptions\QpickHttpException;
use App\Http\Requests\EmailTemplates\CreateRequest;
use App\Http\Requests\EmailTemplates\IndexRequest;
use App\Http\Requests\EmailTemplates\UpdateRequest;
use App\Libraries\CollectionLibrary;
use App\Models\EmailTemplate;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class EmailTemplateController extends Controller
{
    private EmailTemplate $mailTemplate;

    public function __construct(EmailTemplate $mailTemplate)
    {
        $this->mailTemplate = $mailTemplate;
    }

    /**
     * @OA\Post(
     *      path="/v1/email-template",
     *      summary="이메일 템플릿 작성",
     *      description="이메일 템플릿 작성",
     *      operationId="emailTemplateCreate",
     *      tags={"이메일 템플릿"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="code", type="string", example="USER_REGISTED", description="메일 템플릿 코드"),
     *              @OA\Property(property="name", type="string", example="[회원] 회원가입 완료 메일", description="메일 템플릿 명"),
     *              @OA\Property(property="title", type="string", example="{{$name}}님의 가입을 축하합니다.", description="메일 제목"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/EmailTemplate")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Store a newly created resource in storage.
     *
     * @param CreateRequest $request
     * @return JsonResponse
     */
    public function store(CreateRequest $request): JsonResponse
    {
        $data = $this->mailTemplate->create(array_merge($request->all(), ['user_id' => Auth::id()]));

        return response()->json($data->refresh(), 201);
    }


    /**
     * @OA\Get(
     *      path="/v1/email-template/{email_template_id}",
     *      summary="이메일 템플릿 상세",
     *      description="이메일 템플릿 상세",
     *      operationId="emailTemplateShow",
     *      tags={"이메일 템플릿"},
     *      @OA\RequestBody(
     *          required=true,
     *          description=""
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/EmailTemplate")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Client authentication failed"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Display the specified resource.
     *
     * @param int $id
     * @return Collection
     */
    public function show(int $id): Collection
    {
        return $this->getOne($id);
    }

    /**
     * @OA\Get(
     *      path="/v1/email-template",
     *      summary="이메일 템플릿 목록",
     *      description="이메일 템플릿 목록",
     *      operationId="emailTemplateIndex",
     *      tags={"이메일 템플릿"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="sortBy", type="string", example="+name,-id", description="정렬기준<br/>+:오름차순, -:내림차순" )
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="header", type="object", ref="#/components/schemas/Pagination"),
     *              @OA\Property(property="list", type="array",
     *                  @OA\Items(type="object", ref="#/components/schemas/EmailTemplateForList")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return Collection
     * @throws QpickHttpException
     */
    public function index(IndexRequest $request): Collection
    {
        // set relations
        $with = ['user'];

        $mail = $this->mailTemplate->with($with);

        // Sort By
        if ($s = $request->input('sort_by')) {
            $sortCollect = CollectionLibrary::getBySort($s, ['id', 'name']);
            $sortCollect->each(function ($item) use ($mail) {
                $mail->orderBy($item['key'], $item['value']);
            });
        }

        // result
        $result = [
            'header' => [],
            'list' => $mail->get() ?? []
        ];

        return collect($result);
    }

    /**
     * @OA\Patch(
     *      path="/v1/email-template/{email_template_id}",
     *      summary="이메일 템플릿 수정",
     *      description="이메일 템플릿 수정",
     *      operationId="emailTemplateUpdate",
     *      tags={"이메일 템플릿"},
     *      @OA\RequestBody(
     *          required=true,
     *          description="",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="string", example="[회원] 회원가입 완료 메일", description="메일 템플릿 명"),
     *              @OA\Property(property="title", type="string", example="{{$name}}님의 가입을 축하합니다.", description="메일 제목"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="created",
     *          @OA\JsonContent(
     *              allOf={
     *                  @OA\Schema(ref="#/components/schemas/EmailTemplate")
     *              }
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="failed"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(UpdateRequest $request, int $id): JsonResponse
    {
        $this->mailTemplate->findOrFail($id)->update($request->except(['user_id', 'code']));

        return response()->json($this->getOne($id), 201);
    }


    /**
     * @OA\delete(
     *      path="/v1/email-template/{email_template_id}",
     *      summary="이메일 템플릿 삭제",
     *      description="이메일 템플릿 삭제",
     *      operationId="emailTemplateDelete",
     *      tags={"이메일 템플릿"},
     *      @OA\Response(
     *          response=204,
     *          description="deleted"
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated (비로그인)"
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden (백오피스 로그인시에만 가능)"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not found"
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     *
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        $this->mailTemplate->findOrFail($id)->delete();

        return response()->noContent();
    }


    protected function getOne($id): Collection
    {
        // set relations
        $with = [];

        if (Auth::hasAccessRightsToBackoffice()) {
            $with[] = 'user';
            $with[] = 'backofficeLogs';
        }

        // get data
        $data = $this->mailTemplate::with($with)->findOrFail($id);

        // return
        return collect($data);
    }
}
