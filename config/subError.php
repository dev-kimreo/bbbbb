<?php

return [

    /**
     * Common Error
     * code => language trans string
     * ex) 10000 => 'validation.unique' // /resources/lang/[locale]/validation.php => unique key
     */
//    100000 => 'validation.unique',                   // email 필드 중복시 에러
//    100001 => 'common.incorrect',                    // 일치 하는 정보가 없습니다.
//    100002 => 'validation.exists',                   // 필드 값 오류

    100001 => 'validation.required',                // 필수값을 입력해주세요.
    100002 => 'validation.unique',                  // 이미 사용중입니다.
    100003 => 'validation.required_without_all',    // :values이(가) 모두 없는 경우 :key 필드는 필수입니다.

    100005 => 'common.incorrect',                   // 일치하는 정보가 없습니다.

    100011 => 'validation.same',                    // 다른 항목과 같아야 합니다

    100021 => 'common.exists',                      // 존재하지 않는 :key 입니다.

    100022 => 'validation.exists',                  // 선택된 :key은(는) 올바르지 않습니다.

    100051 => 'validation.between.numeric',         // 값의 제한
    100052 => 'validation.between.file',            // 파일의 바이트 수 제한
    100053 => 'validation.between.string',          // 문자 길이 제한
    100054 => 'validation.between.array',           // 배열의 항목 수 제한

    100061 => 'validation.min.numeric',             // 최소 값 제한
    100062 => 'validation.min.file',                // 최소 파일 크기 제한
    100063 => 'validation.min.string',              // 최소 길이 제한
    100064 => 'validation.min.array',               // 최소 항목 수 제한

    100071 => 'validation.max.numeric',             // 최대 값 제한
    100072 => 'validation.max.file',                // 최대 파일 크기 제한
    100073 => 'validation.max.string',              // 최대 길이 제한
    100074 => 'validation.max.array',               // 최대 항목 수 제한

    100081 => 'validation.in',                      // 선택된 :key은(는) 올바르지 않습니다.
    100083 => 'validation.array',                   // :key은(는) 배열이어야 합니다.


    100101 => 'validation.email',                   // 이메일 형식

    100155 => 'validation.mimes',                   // 파일 확장자 형식


    110501 => 'common.auth.incorrect_timeout',      // 잘못된 인증방식이거나 token의 유효시간이 지났습니다.


    /**
     * 회원
     */

    // 비밀번호
    110100 => 'common.password.same_org',           // 이전 비밀번호와 동일합니다.
    110101 => 'common.password.combination',        // 문자 조합 및 길이제한 체크
    110102 => 'common.password.continue',           // 연속된 문자, 동일한 문자 연속 체크
    110103 => 'common.password.empty',              // 공백 체크

    110114 => 'common.password.same_email',         // 비밀번호가 이메일과 4자 이상 동일할 경우 체크


    // login errors
    110311 => 'common.password.not_match',          // 비밀번호가 일치하지 않습니다.


    // 이메일 인증
    110401 => 'common.email.verify',  // 잘못된 인증 방식입니다.
    110402 => 'common.email.verified',  // 이미 인증된 회원입니다.
    110411 => 'common.email.limit_send',  // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.






    10020 => 'common.incorrect_page',          // 정상적인 페이지 요청이 아닙니다.

    // password errors
    10100 => 'validation.custom.password.same_org', // 이전 비밀번호와 동일합니다.
    10101 => 'validation.custom.password.combination',  // 문자 조합 및 길이제한 체크
    10102 => 'validation.custom.password.continue',  // 연속된 문자, 동일한 문자 연속 체크
    10103 => 'validation.custom.password.empty',  // 공백 문자 체크

    10111 => 'validation.custom.password.same_email',  // 비밀번호가 아이디와 4자이상 동일 할 경우




    //
    10500 => 'common.required_login',           // 로그인이 필요한 서비스입니다.



    /**
     * Admin Error
     * code => language trans string
     * ex) 10000 => 'validation.unique' // /resources/lang/[locale]/validation.php => unique key
     */

    20401 => 'validation.exists',       // 필드 값 오류


];
