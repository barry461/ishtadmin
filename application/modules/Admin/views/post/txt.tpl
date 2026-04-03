<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="utf-8"/>
    <title>@links - Editor.md examples</title>
    <link rel="stylesheet" href="/static/editor/css/style.css"/>
    <link rel="stylesheet" href="/static/editor/css/editormd.css"/>
    <script src="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js/dist/hls.min.js"></script>
    <link rel="shortcut icon" href="https://pandao.github.io/editor.md/favicon.ico" type="image/x-icon"/>
</head>
<body>
<div id="layout">
    <header style="min-width: 1165px">
        <h1 class="back-div"><a class="back-btn" onclick="close_iframe(false)"><< 关闭</a> 文章:{%$post_title%}</h1>
    </header>
    <div id="test-editormd" style="min-width: 1165px">
        <textarea style="display:none;">{%$post_txt%}</textarea>
    </div>
    <div class="btn-div" onclick="save()"><a class="btn-check">确定</a></div>
</div>
<input type="file" id="imagex" accept="image/*" style="display: none">
<input type="file" id="imagey" accept="image/*" multiple style="display: none">
<input type="file" id="videox" accept="video/*" style="display: none">
<script src="/static/editor/js/jquery.min.js"></script>
<script src="/static/editor/js/jquery.md5.js"></script>
<script src="/static/editor/editormd.js?v=1"></script>
<script src="/static/editor/swiper-bundle.min.js"></script>
<script src="/static/js/plugins/layer/layer.min.js"></script>
<script src="/static/backend/util.js"></script>
<link rel="stylesheet" href="/static/editor/swiper-bundle.min.css">
<style>
    .back-div {
        display: inline-block;
    }

    .back-btn {
        background-color: #009688;
        display: inline-block;
        white-space: nowrap;
        border: none;
        border-radius: 2px;
        cursor: pointer;
        text-align: center;
        line-height: 30px;
        padding: 0 24px;
        font-size: 12px;
        opacity: .8;
        color: #fff;
        text-decoration: none;
    }

    .btn-div {
        margin: 0px auto;
        width: 90%;
        text-align: right;
    }

    .btn-check {
        background-color: #009688;
        display: inline-block;
        white-space: nowrap;
        border: none;
        border-radius: 2px;
        cursor: pointer;
        text-align: center;
        height: 30px;
        line-height: 30px;
        padding: 0 24px;
        font-size: 12px;
        opacity: .8;
        color: #fff;
    }
     .dplayer{
         margin-bottom: 10px;
     }
</style>
<script type="text/javascript">
    var testEditor;

    function save() {
        $.post("{%url('txt_save')%}", {'_pk':{%$post_id%},'txt':testEditor.getMarkdown()})
            .then(function (json) {
                if (json.code) {
                    Util.msgErr(json.msg);
                } else {
                    Util.msgOk(json.msg);
                    close_iframe(true)
                }
            })
    }

    function close_iframe(is_reload){
        let index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.window.reload_test(is_reload)
        parent.layer.close(index);
    }

    function baiduHash(filename) {
        let key = 'INhaDFiNgamplaTE';
        let timestamp = Date.parse(new Date()) / 1000 + 120 * 60;
        let rand = 0;
        let str = filename + '-' + timestamp + '-' + rand + '-0-' + key;
        let sign = $.md5(str);
        let query = timestamp + '-' + rand + '-0-' + sign;
        return "auth_key=" + query;
    }

    function afterChangeInit() {
        $(".dplayer").each(function (item, prop) {
            let jsonStr = $(prop).attr("config");
            let config = JSON.parse(jsonStr);
            config['container'] = prop
            new DPlayer(config);
        });
    }

    $(function () {
        // You can custom @link base url.
        editormd.urls.atLinkBase = "https://github.com/";

        testEditor = editormd("test-editormd", {
            width: "90%",
            height: $(window).innerHeight,
            toc: true,
            htmlDecode: true,
            emoji: true,
            breaks: true,
            //atLink    : false,    // disable @link
            //emailLink : false,    // disable email address auto link
            todoList: true,
            path: '/static/editor/lib/',
            toolbarIcons: function () {
                return ["undo", "redo", "bold", "del", "italic", "quote", "ucwords", "uppercase", "lowercase", "h1", "h2", "h3", "h4", "h5", "h6", "list-ul", "list-ol", "hr", "link", "reference-link", "videox", "videolinkx", "imagex", "imagey", "code", "table", "datetime", "emoji", "html-entities", "pagebreak", "goto-line", "watch", "clear", "search"]
            },
            toolbarIconsClass: {
                videox: "fa-file-video-o",  // 插入视频
                imagex: "fa-file-photo-o", // 插入图片
                imagey: "fa-file-powerpoint-o", // 插入多图片
                videolinkx: "fa-file-photo-o", // 插入视频链接、
            },
            toolbarIconTexts: {
                videox: "插入视频",
                imagex: "插入图片",
                imagey: "插入多图片",
                videolinkx: "插入视频链接",
            },
            toolbarHandlers: {
                // 上传视频
                videox: function (cm, icon, cursor, selection) {
                    $('#videox').click()
                },
                // 插入视频链接
                videolinkx: function () {
                    this.executePlugin("videolinkxDialog", "video-linkx-dialog/video-linkx-dialog");
                },
                // 插入图片
                imagex: function (cm, icon, cursor, selection) {
                    $('#imagex').click()
                },
                // 插入多单图片
                imagey: function (cm, icon, cursor, selection) {
                    $('#imagey').click()
                }
            },
            prevHandler: function (cmValue) {
                cmValue = this.parserDomain(cmValue)
                cmValue = this.parserVideo(cmValue)
                console.log(cmValue)
                return cmValue
            },
            parserDomain: function (cmValue) {
                cmValue = cmValue.replace(/\{\{img\-cdn\}\}/g, "{%BASE_IMG_URL%}")
                cmValue = cmValue.replace(/\{\{mp4\-cdn\}\}/g, "{%$mp4_domain%}")
                cmValue = cmValue.replace(/\{\{m3u8\-cdn\}\}/g, "{%$m3u8_domain%}")
                return cmValue
            },
            parserVideo: function (cmValue) {
                let fn = function parserVideox(v) {
                    let attrs = v.replace(/"/g,'').split(" ")

                    let dplayer_attrs = {};
                    for (let i in attrs) {
                        let line = attrs[i].trim()
                        let attr = line.split("=")
                        if (attr.length === 2) {
                            dplayer_attrs[attr[0].trim()] = attr[1].trim();
                        }
                    }
                    return dplayer_attrs;
                }


                $reg2 = /\[dplayer\s+(.+)\/]/g;
                return cmValue.replace($reg2, (item, prop) => {
                    let obj = fn(prop)

                    let ext = obj['url'].substr(-4)
                    let video = undefined
                    if(ext === '.mp4'){
                        video = {
                            "url": obj['url'],
                            "pic": "",
                            "type": "mp4",
                            "thumbnails": null
                        }
                    }else{
                        video = {
                            "url": obj['url'],
                            "pic": obj['pic'],
                            "type": "hls",
                            "thumbnails": null
                        }
                    }
                    // 视频使用dplayer
                    let playerConfig = JSON.stringify({
                        "live": false,
                        "autoplay": false,
                        "theme": "#FADFA3",
                        "loop": false,
                        "screenshot": false,
                        "hotkey": true,
                        "preload": "metadata",
                        "lang": "zh-cn",
                        "logo": null,
                        "volume": 0.6,
                        "mutex": true,
                        "video": video
                    })

                    return '<div class="dplayer" config=\'' + playerConfig + '\'></div>';
                });
            },
            onchange: function () {
                afterChangeInit()
            },
            lang: {
                toolbar: {
                    videox: "插入视频",
                    imagex: "插入图片",
                    imagey: "插入多图片",
                    videolinkx: "插入视频链接",
                },
                dialog: {
                    videolinkx: {
                        debug: false,
                        title: '插入视频链接',
                        cover: '封面',
                        coverWidth: '宽度',
                        coverHeight: '高度',
                        duration: '时长',
                        m3u8: 'm3u8链接',
                        mp4: 'mp4链接',
                        m3u8NotValid: '错误: m3u8链接不合法',
                        m3u8Empty: '错误: m3u8链接未正确识别',
                        m3u8NotFormat: '错误: m3u8链接不是m3u8为后缀的链接',
                        mp4Empty: '错误: mp4链接未正确识别',
                        mp4NotFormat: '错误: mp4链接不是mp4为后缀的链接',
                        coverEmpty: '错误: 封面链接未正确识别',
                        coverNotFormat: '错误: 封面链接只能是JPG|PNG|JPEG|GIF为后缀的链接',
                        coverWidthNotValid: '错误: 封面宽度不合法',
                        coverHeightNotValid: '错误: 封面高度不合法',
                        durationNotValid: '错误: 视频时长不合法',
                    }
                },
            },
            onload: function () {
                afterChangeInit()
                $("#videox").bind("change", function () {
                    let formData = new FormData();
                    let fileData = $(this).prop("files")[0];
                    if (!fileData) {
                        return;
                    }
                    formData.append("file", fileData);
                    $.ajax({
                        url: "{%url('upload/uploadMp4')%}",
                        type: 'POST',
                        async: false,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            let ele = '[dplayer url="' + '{{mp4-cdn}}' + '/' + data.data.url + '" pic="" /]';
                            testEditor.cm.replaceSelection(ele);
                        }
                    })
                });
                $("#imagex").bind("change", function () {
                    let formData = new FormData();
                    let fileData = $(this).prop("files")[0];
                    if (!fileData) {
                        return;
                    }
                    formData.append("file", fileData);
                    $.ajax({
                        url: "{%url('upload/upload')%}",
                        type: 'POST',
                        async: false,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            let ele = '![' + data.data.width + 'X' + data.data.height + ']({{img-cdn}}' + data.data.url + ')' + "\n"
                            testEditor.cm.replaceSelection(ele);
                        }
                    })
                });
                $("#imagey").bind("change", function () {
                    let files = $(this).prop("files");
                    if (!files.length) {
                        return;
                    }
                    for (let i = 0; i < files.length; i++) {
                        let formData = new FormData();
                        let fileData = files[i];
                        formData.append("file", fileData);
                        $.ajax({
                            url: "{%url('upload/upload')%}",
                            type: 'POST',
                            async: false,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                let ele = '![' + data.data.width + 'X' + data.data.height + ']({{img-cdn}}' + data.data.url + ')' + "\n"
                                testEditor.cm.replaceSelection(ele);
                            }
                        })
                    }
                });
            }
        });
    });
</script>
</body>
</html>