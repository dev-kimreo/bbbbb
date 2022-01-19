<div style="width:640px; border:1px solid #e3e3e3; font-family: 'SF Pro Text','Helvetica Neue',Helvetica,'Helvetica',Arial,sans-serif; padding:24px;margin:auto; background-color:#ffffff">
    <div>
        <img src="http://showcase.qpicki.com/qpick-bi.png" width="125" height="36.5" alt="Qpick" />
    </div>
    <h1 style="font-size:30px; font-family: 'SF Pro Text','Helvetica Neue',Helvetica,'Helvetica',Arial,sans-serif; color:#333333; letter-spacing:-0.08px; line-height:1.4; font-weight:700; margin-top: 20px; margin-bottom:60px;">
        @yield('title')
    </h1>
    <div style="font-size:18px; font-family: 'SF Pro Text','Helvetica Neue',Helvetica,'Helvetica',Arial,sans-serif; color:#333333; letter-spacing:-0.03px; line-height:1.6">
        @yield('contents')
    </div>
    <div style="border-top: 1px solid #e3e3e3; padding-top:16px; margin-top:60px; color:#888888; font-size:13px; font-family: 'SF Pro Text','Helvetica Neue',Helvetica,'Helvetica',Arial,sans-serif;">
        © @php echo date('Y') @endphp. Koreacenter All rights reserved.<br />
        본 메일은 발신 전용입니다.
    </div>
</div>
