<?php
/* This file is generated by artisan build:translations command at KST 2021-06-14 17:49:30.*/
return array (
  'common' => 
  array (
    'not_found' => 'Not found.',
    'unauthorized' => 'Unauthorized.',
    'bad_request' => 'Bad Request.',
    'forbidden' => 'Forbidden.',
    'pagination' => 
    array (
      'out_of_bounds' => 'The page is out of bounds.',
    ),
  ),
  'auth' => 
  array (
    'incorrect_timeout' => 'Either incorrect information or the token expiration time has expired.',
  ),
  'user' => 
  array (
    'registered' => 'successfully registered.',
    'username' => 
    array (
      'incorrect' => 'Incorrect email address.',
    ),
    'password' => 
    array (
      'incorrect' => 'Incorrect password.',
      'reuse' => 'Can\'t reuse the password you used before the change.',
      'validation' => 
      array (
        'characters' => 'The Password must have alphabets, numbers and special characters.',
        'repetition' => 'The password must not repeat the same character 4 times.',
        'used_space' => 'Spaces cannot be used in the password.',
        'matched_email' => 'Passwords cannot contain words that match your email address.',
      ),
    ),
  ),
  'email' => 
  array (
    'too_many_send' => 'Too many attempting to send mail.',
    'already_verified' => 'Already verified.',
    'incorrect' => 'Incorrect verifying.',
    'verification_resend' => 'A verification mail has been sent again.',
    'failed_validation_signature' => 'Wring validation key.',
    'not_found_sign_code' => 'The validation key is not found.',
  ),
  'board' => 
  array (
    'disable' => 
    array (
      'not_permitted' => 'Not permitted to create, edit or remove boards.',
    ),
    'option' => 
    array (
      'disable' => 
      array (
        'unknown_key' => 'Can\'t use the key as board\'s option.',
        'wrong_value' => 'Can\'t use the value as board\'s option.',
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
      'hidden' => 'Can\'t read because post is hidden.',
    ),
  ),
  'reply' => 
  array (
    'disable' => 
    array (
      'board_option' => 'Can\'t writing a reply on this board.',
      'post_hidden' => 'Can\'t writing a reply because this post is hidden.',
      'writer_only' => 'Can\'t writing a reply because you are not writer.',
    ),
  ),
  'inquiry' => 
  array (
    'disable' => 
    array (
      'writer_only' => 'Can\'t read because you are not writer.',
    ),
    'answer' => 
    array (
      'disable' => 
      array (
        'already_exists' => 'Can\'t write answer because the inquiry already has an answer.',
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