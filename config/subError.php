<?php

return [

    /**
     * Members Error
     * code => language trans string
     * ex) 10000 => 'validation.unique' // /resources/lang/[locale]/validation.php => unique key
     */
    10000 => 'validation.unique',       // email 필드 중복시 에러
    10001 => 'common.incorrect',          // 일치 하는 정보가 없습니다.
    10002 => 'validation.exists',       // 필드 값 오류

    10010 => 'validation.required',          // 필수값을 입력해주세요.

    // password errors
    10100 => 'validation.custom.password.same_org', // 이전 비밀번호와 동일합니다.
    10101 => 'validation.custom.password.combination',  // 문자 조합 및 길이제한 체크
    10102 => 'validation.custom.password.continue',  // 연속된 문자, 동일한 문자 연속 체크
    10103 => 'validation.custom.password.empty',  // 공백 문자 체크

    10111 => 'validation.custom.password.same_email',  // 비밀번호가 아이디와 4자이상 동일 할 경우

    // login errors
    10301 => 'validation.custom.email.exists',  // 존재하지 않는 아이디(이메일) 입니다.
    10311 => 'validation.custom.password.not_match',  // 로그인 정보가 올바르지 않습니다.


    // verify errors
    10401 => 'validation.custom.email.verify',  // 잘못된 인증 방식입니다.
    10411 => 'validation.custom.email.limit_send',  // 짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.
    10421 => 'validation.custom.email.verified',  // 이미 인증된 회원입니다.
    10431 => 'auth.custom.incorrect_timeout',  // 잘못된 인증방식이거나 token의 유효시간이 지났습니다.




    /**
     * Admin Error
     * code => language trans string
     * ex) 10000 => 'validation.unique' // /resources/lang/[locale]/validation.php => unique key
     */
    20000 => 'validation.required',     // 필수값을 입력해주세요.

    20010 => 'validation.unique',       // 필드 중복시 에러

    20401 => 'validation.exists',       // 필드 값 오류


];
