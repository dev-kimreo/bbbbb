<?php

namespace App\Http\Controllers\Boards;

use App\Http\Controllers\Controller;
use App\Libraries\CollectionLibrary;
use App\Services\Boards\BoardService;
use Illuminate\Support\Collection;

class OptionController extends Controller
{
    private BoardService $boardService;
    public string $exceptionEntity = "boardOption";

    public function __construct(BoardService $boardService)
    {
        $this->boardService = $boardService;
    }

    /**
     * @OA\Get(
     *      path="/v1/board/option",
     *      summary="게시판 옵션 목록",
     *      description="게시판 옵션 목록",
     *      operationId="adminBoardOptionList",
     *      tags={"게시판"},
     *      @OA\Response(
     *          response=200,
     *          description="successfully",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(type="object", ref="#/components/schemas/BoardOption"),
     *          )
     *      ),
     *      security={{
     *          "admin_auth":{}
     *      }}
     *  )
     */
    /**
     * 게시판 옵션 정보
     */
    public function index(): Collection
    {
        // response init
        $res = [];
        $res['header'] = [];
        $res['list'] = [];

        $data = $this->boardService->getOptionList();
        $res['list'] = $data;

        return CollectionLibrary::toCamelCase(collect($res));
    }


}
