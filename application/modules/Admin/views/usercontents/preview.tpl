{%include file="header.tpl"%}
<body>

<link href="/static/backend/video/video-js-cdn.min.css" rel="stylesheet">
<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>
    .layui-form.form-dialog .layui-input-block { margin-right: 30px  }
    .preview{width: 660px;height: 100%;margin: 0 auto;background: #fff;padding: 10px 50px;}
    .preview img{width: 580px;}
    .preview span.button{
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 0 1.5rem;
        margin-left: 0.5rem;
        display: inline-block;
        max-width: 100%;
        text-overflow: ellipsis;
        white-space: nowrap !important;
        overflow: hidden;
    }
</style>

<div class="preview">
    <h1>{%$title%}</h1>
    {%$html%}
</div>

<script src="/static/backend/video/video.js"></script>
<script src="/static/backend/video/videojs-contrib-hls.js"></script>
<script>
    document.querySelectorAll('.video-js').forEach(function (v){
        videojs(v);
    })
</script>
{%include file="fooler.tpl"%}
