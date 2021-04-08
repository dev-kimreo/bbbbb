<?php

namespace App\Schemas;


/**
 *  @OA\Schema (
 *      @OA\Xml(name="Request Response 스키마"),
 *      @OA\Property(
 *          property="100001",
 *          type="object",
 *          description=":key 필드는 필수입니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="비밀번호 필드는 필수입니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100002",
 *          type="object",
 *          description=":key은(는) 이미 사용 중입니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="이메일은(는) 이미 사용 중입니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100003",
 *          type="object",
 *          description=":values이(가) 모두 없는 경우 :key 필드는 필수입니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="hidden / options이(가) 모두 없는 경우 name 필드는 필수입니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100005",
 *          type="object",
 *          description="일치하는 정보가 없습니다.",
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="일치하는 정보가 없습니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100011",
 *          type="object",
 *          description=":key와(과) :other은(는) 일치해야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="passwordConfirmation",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="비밀번호 확인와(과) 비밀번호은(는) 일치해야 합니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100021",
 *          type="object",
 *          description="존재하지 않는 :key입니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="존재하지 않는 이메일입니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100022",
 *          type="object",
 *          description="선택된 :key은(는) 올바르지 않습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="선택된 이메일은(는) 올바르지 않습니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100053",
 *          type="object",
 *          description=":key의 길이는 :min에서 :max 문자 사이여야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="name",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="이름의 길이는 2에서 100 문자 사이여야 합니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100063",
 *          type="object",
 *          description=":key은(는) 최소한 :min자이어야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="password",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="비밀번호은(는) 최소한 8자이어야 합니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100073",
 *          type="object",
 *          description=":key은(는) :max자보다 클 수 없습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="이메일은(는) 100자보다 클 수 없습니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100081",
 *          type="object",
 *          description="선택된 :key은(는) 올바르지 않습니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="선택된 options은(는) 올바르지 않습니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100083",
 *          type="object",
 *          description=":key은(는) 배열이어야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="options은(는) 배열이어야 합니다."
 *          ),
 *      ),
 *      @OA\Property(
 *          property="100101",
 *          type="object",
 *          description=":key은(는) 유효한 이메일 주소여야 합니다.",
 *          @OA\Property(
 *              property="key",
 *              type="string",
 *              description="필드 값",
 *              example="email",
 *          ),
 *          @OA\Property(
 *              property="message",
 *              type="string",
 *              description="메시지",
 *              example="이메일은(는) 유효한 이메일 주소여야 합니다."
 *          ),
 *      ),
 *  )
 */

abstract class RequestResponse extends Model {}
