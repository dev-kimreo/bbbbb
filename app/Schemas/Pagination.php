<?php

namespace App\Schemas;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="페이지네비게이션"),
 *      @OA\Property(property="page", type="integer", example=11, description="현재 페이지" ),
 *      @OA\Property(property="perPage", type="integer", example=10, description="한 페이지당 게시글 수" ),
 *      @OA\Property(property="skip", type="integer", example=100, description="넘겨온 게시글 수" ),
 *      @OA\Property(property="block", type="integer", example=2, description="현재 블럭" ),
 *      @OA\Property(property="perBlock", type="integer", example=10, description="한 블럭당 페이지 수" ),
 *      @OA\Property(property="totalCount", type="integer", example=200, description="총 게시글 수" ),
 *      @OA\Property(property="totalPage", type="integer", example=20, description="총 페이지 수" ),
 *      @OA\Property(property="totalBlock", type="integer", example=2, description="총 블럭 수" ),
 *      @OA\Property(property="startPage", type="integer", example=101, description="현재 블럭의 시작 페이지" ),
 *      @OA\Property(property="endPage", type="integer", example=110, description="현재 블럭의 끝 페이지" ),
 *      @OA\Property(property="prev", type="object", description="이전 블럭이 존재할 경우 생성",
 *          @OA\Property(property="start", type="integer", example=91, description="이전 블럭의 시작 페이지" ),
 *          @OA\Property(property="end", type="integer", example=100, description="이전 블럭의 끝 페이지" )
 *      ),
 *      @OA\Property(property="next", type="object", description="다음 블럭이 존재할 경우 생성",
 *          @OA\Property(property="start", type="integer", example=111, description="다음 블럭의 시작 페이지" ),
 *          @OA\Property(property="end", type="integer", example=120, description="다음 블럭의 끝 페이지" )
 *      ),
 *  )
 */
abstract class Pagination extends Model {}
