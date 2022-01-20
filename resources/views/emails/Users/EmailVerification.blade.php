@extends('emails.layout')

@section('title')
    <span style="color:#54C7A2">큐픽</span>에 가입하기 위한<br />
    인증메일을 보내드립니다.
@endsection

@section('contents')
    <b>{{ $user['privacy']['name']}}</b>님, 큐픽에 가입해주셔서 대단히 감사합니다.<br />
    아래의 링크를 클릭하면 이메일 인증이 완료됩니다.<br />
    <br />
    <a href="{{ $data['url'] }}">이메일 인증 진행하기 &gt;</a><br />
    <br />
    큐픽을 이용해 더 쉽게 더 멋진 쇼핑몰을 가꾸어가세요.
@endsection
