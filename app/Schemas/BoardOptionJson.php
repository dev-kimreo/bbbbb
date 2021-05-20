<?php

namespace App\Schemas;

/**
 *  @OA\Schema(
 *      @OA\Xml(name="게시판 옵션"),
 *      @OA\Property(property="id", type="string", example=7, description="게시판 고유번호" ),
 *      @OA\Property(property="name", type="string", example="공지사항", description="게시판 명" ),
 *      @OA\Property(property="options", type="object",
 *          @OA\Property(property="board", type="enum", example="all", description="작성 권한<br/>all:모두 작성가능<br/>manager:운영진만 작성가능<br/>member:회원만 작성가능", default="all"),
 *          @OA\Property(property="thema", type="string", example="boardDefaultThema", description="게시판 테마", default="boardDefaultThema"),
 *          @OA\Property(property="thumbnail", type="enum", example="0", description="섬네일 사용<br/>0:사용안함<br/>1:사용함", default="0" ),
 *          @OA\Property(property="reply", type="enum", example="0", description="댓글 사용<br/>0:사용안함<br/>1:사용함", default="0" ),
 *          @OA\Property(property="editor", type="enum", example="all", description="에디터<br/>all:모두 사용<br/>ck:CK에디터 사용<br/>markd:마크다운 사용", default="all" ),
 *          @OA\Property(property="attach", type="enum", example="0", description="파일 첨부<br/>0:사용안함<br/>1:사용함", default="0" ),
 *          @OA\Property(property="attachLimit", type="integer", example=10, description="파일 첨부 갯수 제한", default=10 ),
 *      ),
 *  )
 */
abstract class BoardOptionJson extends Model {}
