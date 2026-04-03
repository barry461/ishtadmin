@extends('layouts.app')

@section('seo-head')
<title>跳转中 - {{ $brand }}</title>
<meta name="robots" content="noindex,nofollow">
<link rel="icon" href="{{ $favicon }}">
@endsection

@section('header')
    {{-- 中转页不展示头图 --}}
@endsection

@section('lists')
    {{-- 无列表内容 --}}
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
        <style type="text/css">
            .urlInfoBox {
                padding: 160px 0;
                box-sizing: border-box;
                width: 100%;
                max-width: 600px;
                margin: 0 auto;
                text-align: center;
            }
            .jumpTip {
                font-size: 20px;
                font-weight: 500;
                color: #fafafa;
                margin-bottom: 8px;
                line-height: 28px;
            }
            .countdownTip {
                color: #bdbdbd;
                font-size: 16px;
                font-weight: 400;
                line-height: 22px;
                margin-bottom: 18px;
            }
            .countdown { color: inherit; }
            .urlBox {
                width: 75%;
                min-height: 40px;
                margin: 0 auto 36px;
                border-radius: 12px;
                background: rgba(255,255,255,.1);
                display: flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 14px;
                font-weight: 400;
                padding: 0 10px;
                word-break: break-all;
                text-align: center;
            }
            .jumpBtn {
                width: 28.8%;
                min-width: 120px;
                height: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #fff;
                font-size: 18px;
                border-radius: 12px;
                background: #ff53fd;
                text-decoration: none;
                transition: background 0.3s;
            }
            .jumpBtn:hover { background: #ff6dfd; }
            @media screen and (max-width: 767px) {
                .urlInfoBox { padding: 120px 20px; }
                .jumpTip { font-size: 18px; }
                .countdownTip { font-size: 14px; }
                .urlBox { width: 90%; font-size: 13px; }
                .jumpBtn { width: 60%; }
            }
        </style>
        <div class="urlInfoBox">
            <div class="jumpTip">您即将跳转到外部网站</div>
            <div class="countdownTip">
                <span class="countdown" id="countdown">3</span>秒后自动跳转到
            </div>
            <div class="urlBox" id="urlBox"></div>
            <a class="jumpBtn" id="jumpBtn" href="/" aria-label="跳转链接">继续前往</a>
        </div>
            </div>
        </div>
    </div>
@endsection

@section('body-bottom')
    <script>
        (() => {
            const urlParam = @json($url ?? '');
            let timeLeft = 3;
            const countdownElement = document.getElementById('countdown');
            const urlBoxElement = document.getElementById('urlBox');
            const jumpBtnElement = document.getElementById('jumpBtn');

            if (!urlParam) {
                window.location.href = '/';
                return;
            }

            urlBoxElement.textContent = urlParam;
            jumpBtnElement.setAttribute('href', urlParam);

            const countdown = setInterval(() => {
                timeLeft--;
                countdownElement.textContent = timeLeft;
                if (timeLeft <= 0) {
                    clearInterval(countdown);
                    window.location.href = urlParam;
                }
            }, 1000);
        })();
    </script>
@endsection

