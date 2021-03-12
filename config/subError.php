<?php

return [

    /**
     * Members Error
     * code => language trans string
     * ex) 10000 => 'validation.unique' // /resources/lang/[locale]/validation.php => unique key
     */
    10000 => 'validation.unique',     // email 필드 중복시 에러

    // password errors
    10101 => 'validation.custom.password.combination',  // 문자 조합 및 길이제한 체크
    10102 => 'validation.custom.password.continue',  // 연속된 문자, 동일한 문자 연속 체크
    10103 => 'validation.custom.password.empty',  // 공백 문자 체크



];
