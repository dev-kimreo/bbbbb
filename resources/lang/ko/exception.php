<?php
/* This file is generated by artisan build:translations command at KST 2021-06-15 14:02:37.*/
return array (
  'common' => 
  array (
    'not_found' => '요청한 데이터를 찾을 수 없습니다.',
    'unauthorized' => '요청 권한이 없습니다.',
    'bad_request' => '잘못된 요청입니다.',
    'forbidden' => '요청 권한이 없습니다.',
    'pagination' => 
    array (
      'out_of_bounds' => '표시할 수 있는 총 페이지 수를 넘어섰습니다.',
    ),
    'wrong_language_code' => '올바른 ISO 639-1 코드가 아닙니다.',
  ),
  'auth' => 
  array (
    'incorrect_timeout' => '잘못된 인증방식이거나 token의 유효시간이 지났습니다.',
  ),
  'user' => 
  array (
    'registered' => '가입이 완료되었습니다.',
    'username' => 
    array (
      'incorrect' => '이메일 주소가 잘못되었거나, 아직 가입이 진행되지 않았습니다.',
    ),
    'password' => 
    array (
      'incorrect' => '비밀번호가 일치하지 않습니다.',
      'reuse' => '입력하신 비밀번호는 변경 전과 동일하여 다시 사용할 수 없습니다.',
      'validation' => 
      array (
        'characters' => '비밀번호에는 특수문자, 문자, 숫자가 모두 포함되어 있어야 합니다.',
        'repetition' => '비밀번호에는 같은 문자 또는 숫자를 4번 반복하여 사용할 수 없습니다.',
        'used_space' => '비밀번호에는 공백문자를 사용할 수 없습니다.',
        'matched_email' => '비밀번호에는 아이디(이메일 주소)와 일치하는 단어를 사용할 수 없습니다.',
      ),
    ),
  ),
  'email' => 
  array (
    'too_many_send' => '짧은 시간내에 잦은 요청으로 인해 재발송 불가 합니다.',
    'already_verified' => '이미 인증된 회원입니다.',
    'incorrect' => '잘못된 인증 방식입니다.',
    'verification_resend' => '인증메일을 재발송 하였습니다.',
    'failed_validation_signature' => '메일 인증키가 유효하지 않습니다.',
    'not_found_sign_code' => '발급된 적이 없거나 만료된 메일인증키입니다.',
  ),
  'board' => 
  array (
    'disable' => 
    array (
      'not_permitted' => '게시판을 추가, 수정 및 삭제할 권한이 없습니다.',
    ),
    'option' => 
    array (
      'disable' => 
      array (
        'unknown_key' => '입력할 수 있는 게시판 옵션이 아닙니다.',
        'wrong_value' => '입력한 값은 해당 게시판 옵션의 값으로 사용할 수 없습니다.',
      ),
    ),
    'delete' => 
    array (
      'disable' => 
      array (
        'exists_post' => '등록된 게시글이 존재하여 삭제할 수 없습니다.',
      ),
    ),
  ),
  'post' => 
  array (
    'disable' => 
    array (
      'hidden' => '숨겨진 게시글입니다.',
    ),
  ),
  'reply' => 
  array (
    'disable' => 
    array (
      'board_option' => '댓글을 작성할 수 없도록 설정된 게시판입니다.',
      'post_hidden' => '숨겨진 게시글에는 댓글을 작성할 수 없습니다.',
      'writer_only' => '댓글의 수정이나 삭제는 작성자만 할 수 있습니다.',
    ),
  ),
  'inquiry' => 
  array (
    'disable' => 
    array (
      'writer_only' => '내가 작성하지 않은 1:1 상담은 열람 또는 수정할 수 없습니다.',
    ),
    'answer' => 
    array (
      'disable' => 
      array (
        'already_exists' => '이미 답변이 완료된 1:1 상담입니다.',
      ),
    ),
  ),
  'attach' => 
  array (
    'disable' => 
    array (
      'upload' => '업로드 기능을 사용할 수 없습니다.',
    ),
    'over' => 
    array (
      'limit' => '업로드 제한 갯수를 초과 하였습니다.',
    ),
  ),
  'menu' => 
  array (
    'delete' => 
    array (
      'disable' => 
      array (
        'exists_children' => '하위 메뉴가 존재하여 삭제할 수 없습니다.',
      ),
    ),
    'permission' => 
    array (
      'only' => 
      array (
        'last' => '마지막 메뉴만이 권한을 가질 수 있습니다.',
      ),
    ),
  ),
  'authority' => 
  array (
    'delete' => 
    array (
      'disable' => 
      array (
        'exists_manager' => '현재 사용중인 관리자가 존재하여 삭제할 수 없습니다.',
      ),
    ),
  ),
);