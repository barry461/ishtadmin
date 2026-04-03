<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>PC HLS video</title>
    <link href="/static/backend/video/video-js-cdn.min.css" rel="stylesheet">
</head>
<body>
<video id="hls-video" class="video-js vjs-16-9 vjs-fluid"
       playsinline webkit-playsinline
       autoplay controls preload="auto"
       x-webkit-airplay="true" x5-video-player-fullscreen="true" x5-video-player-typ="h5">
    <!-- 直播的视频源 -->
    <source src="{%$url%}" type="application/x-mpegURL">
    <!--<source src="http://mmmmm.tiansex.net/useruploadfiles/dd31920d282ea72101af9730e9e79c01/dd31920d282ea72101af9730e9e79c01.m3u8?md5=lxfCe2q9sqKAAO02YgYrtg" type="application/x-mpegURL">-->
    <!-- 点播的视频源 -->
    <!--<source src="http://devstreaming.apple.com/videos/wwdc/2015/413eflf3lrh1tyo/413/hls_vod_mvp.m3u8" type="application/x-mpegURL">-->
</video>

<script src="/static/backend/video/video.js"></script>
<!-- PC 端浏览器不支持播放 hls 文件(m3u8), 需要 videojs-contrib-hls 来给我们解码 -->
<script src="/static/backend/video/videojs-contrib-hls.js"></script>
<script>
    // XMLHttpRequest cannot load http://xxx/video.m3u8. No 'Access-Control-Allow-Origin' header is present on the requested resource. Origin 'http://192.168.198.98:8000' is therefore not allowed access.
    // 由于 videojs-contrib-hls 需要通过 XHR 来获取解析 m3u8 文件, 因此会遭遇跨域问题, 请设置浏览器运行跨域
    var player = videojs('hls-video');
    player.play();
</script>
</body>
</html>
<style>
    html, body, video {
        margin: 0px auto;
        padding: 0px;
        text-align: center;
    }
</style>