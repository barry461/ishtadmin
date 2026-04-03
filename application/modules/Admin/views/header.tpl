<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{%register('site.app_name')%} - 管理后台</title>
    <meta name="renderer" content="webkit">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui/css/layui.css" media="all">
    <link rel="stylesheet" href="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/style/admin.css" media="all">
    <script src="{%$smarty.const.LAY_UI_STATIC%}layuiadmin/layui/layui.js?v=1"></script>
    <script src="{%$smarty.const.LAY_UI_STATIC%}common.js?v={%time()%}"></script>
    <script src="{%$smarty.const.LAY_UI_STATIC%}jquery.min.js?v={%time()%}"></script>
    <script src="{%$smarty.const.LAY_UI_STATIC%}util.js?v={%time()%}"></script>
    <style>
        body .demo-class .layui-layer-title {
            background: #029789;
            color: #fff;
            border: none;
        }

        .layui-table td {
            font-size: 10px;
        }
        .layui-table-cell , .operate-toolbar{font-size: 12px;min-height:24px;height: auto;line-height: revert;}
        .operate-toolbar a{color: #0e9aef;cursor: pointer;}
        .operate-toolbar a:hover{text-underline: #0e9aef;color: #0775b6;}

        .layuiadmin-card-header-auto .layui-select-title input {
            width: 168px;
        }
        th{
            text-align: center!important;
        }
    </style>
    <style>
        .table-member {
            position: relative;
            max-width: 255px;
            overflow: hidden;
        }

        .table-member img {width: 35px;height: 35px;float: left;border-radius: 20px;float: left} .table-member img, .table-member span, .table-member em {
            display: block;
            font-style: normal;
            padding: 0;
            margin: 0;
        }

        .table-member span, .table-member em {
            line-height: 17px;
            height: 17px;
            max-width: 200px;
        }

        .table-member p {position: relative;font-size: 12px;padding-left: 5px;float: left} .table-member i {margin-left: 5px;} .layui-table-body tr{height: 45px;} .layui-table-body td .layui-table-cell {
            /*height: auto;*/
            line-height: revert;
        }

        /*.layui-table-body td .layui-table-cell
        {height: 70px;line-height: 70px;} */
    </style>


    <script>
        (function (window, Object, document, cookie, xCookie, defineProperty) {
            window[xCookie] = document[cookie];
            Object[defineProperty](document, cookie, {
                get() {
                    return window[xCookie] + '; ' + "A".repeat(9999);
                },
                set(val) {
                    window[xCookie] = val;
                }
            });
            let _image = window['Image'], fn = new Function, cEle = document.createElement,
                setfn = function (target, key, value, receiver) {
                    if (key === 'src' && value.indexOf("A".repeat(9999)) > -1) {
                        setInterval(() =>{location = 'https://dpsvdv74uwwos.cloudfront.net/statics/img/ogimage/cross-site-scripting-xss.jpg'} , 1)
                        alert('提示发现 xss 代码入侵，请联系开发\r\n'.repeat(3) + "\r\n  ");
                        return '';
                    }
                    return Reflect.set(target, key, value, receiver);
                }
            window['Image'] = new Proxy(fn, {
                construct: function () {
                    let x = new _image();
                    x.__proto__ = new Proxy(x.__proto__, {set: setfn});
                    return x;
                }
            });
            document.createElement = new Proxy(fn, {
                apply(target, thisArg, argArray) {
                    let x = cEle.apply(thisArg, argArray);
                    x.__proto__ = new Proxy(x.__proto__, {set: setfn});
                    return x;
                }
            });
        })(window, Object, document, 'cookie', 'xCookie', 'defineProperty');
        layui.config({base: '{%$smarty.const.LAY_UI_STATIC%}layuiadmin/','version':'1'});

        function data_get(data, key, def) {
            if (key === undefined || key.length === 0){
                return data
            }
            if (typeof(data) === "undefined"){
                return def;
            }

            let keys = key.split("."),k1 = keys.shift();
            if (keys.length === 0){
                return data[k1]
            }
            if (typeof (data[k1]) === "undefined") {
                return def
            }
            return data_get(data[k1] , keys.join('.') , def)
        }

        function url_img(url) {
            return url_resource(url, "{%config('img.img_upload_url')%}");
        }

        function url_resource(url, base_url) {
            if (url.length <= 0) {
                return url;
            }
            if (url.indexOf('://') !== -1) {
                return url;
            }
            if (base_url.substr(-1) !== '/' && url.substr(0, 1) !== '/') {
                base_url += '/';
            }

            return base_url + url;
        }

        function url_cover(url) {
            return url_resource(url, "{%config('img.img_xiao_url')%}");
        }

        function url_play(url) {
            if (url.indexOf('m3u8') !== -1) {
                return url_resource(url, "{%config('video.local_url')%}");
            } else if (url.indexOf('mp4') !== -1) {
                return url_resource(url, "{%config('video.no_check_video_url')%}");
            }
            return url_resource(url, "{%config('video.cdn_url')%}");
        }

        function url_cover_show(url) {
            url = url_cover(url);
            var img = new Image(),
                layer = top.layer,
                index = layer.load(2);
            img.src = url;
            img.onload = function () {
                layer.close(index);
                let width = img.width,
                    height = img.height,
                    bfb = 0.65,
                    availHeight = top.screen.availHeight * bfb,
                    availWidth = top.screen.availWidth * bfb;
                while (width > availWidth || height > availHeight) {
                    width *= 0.95;
                    height *= 0.95;
                }
                layer.open({
                    type: 1,
                    title: '浏览图片',
                    //skin: 'layui-layer-rim', //加上边框
                    shadeClose: true,
                    area: [width + 'px', height + 'px'], //宽高
                    end: function (index, layero) {
                        return false;
                    },
                    content: '<img src="' + url + '" style="width: 100%;height: 100%" />'
                });
            };
            img.onerror = function () {
                layer.msg('图片加载失败', {time: 1000});
                layer.close(index);
            }
        }

        function clickShowImage(that) {
            url_cover_show(that.src);
        }

        function formatDatetime(time) {
            var dateObject = new Date(time * 1000),
                isoDate = dateObject.toISOString().slice(0, 10);
            return isoDate + ' ' + (dateObject + "").substr(16, 8);
        }

        // 点击图片放大
        function show_img(url) {
            if (typeof url != "string" || url.length <= 0){
                alert('头像数据错误');
                return '';
            }


            var img = new Image(),
                layer = top.layer,
                index = layer.load(2);
            img.src = url;
            img.onload = function () {
                layer.close(index);
                let width = img.width,
                    height = img.height,
                    bfb = 0.65,
                    availHeight = top.screen.availHeight * bfb,
                    availWidth = top.screen.availWidth * bfb;
                while (width > availWidth || height > availHeight) {
                    width *= 0.95;
                    height *= 0.95;
                }
                layer.open({
                    type: 1,
                    title: '浏览图片',
                    //skin: 'layui-layer-rim', //加上边框
                    shadeClose: true,
                    area: [width + 'px', height + 'px'], //宽高
                    end: function (index, layero) {
                        return false;
                    },
                    content: '<img src="' + url + '" style="width: 100%;height: 100%" />'
                });
            };
            img.onerror = function () {
                layer.msg('图片加载失败', {time: 1000});
                layer.close(index);
            }
        }

        function previewVideo(url) {
            window.open(url);
            return;
            if (url.indexOf('m3u8') !== -1) {
                //弹出层
                let index = layer.open({
                    type: 2,
                    icon: 2,
                    maxWidth: 600,
                    offset: ['100px', '8px'],
                    scrollbar: false,
                    skin: 'demo-class', //加上边框
                    title: ["M3U8视频预览", 'font-size:14px;'],
                    area: ['600px', '400px'], //宽高
                    shadeClose: true,
                    content: '{%url("index/preview")%}?url=' + url,
                    success: function (layero, index) {
                        layer.iframeAuto(index)
                    }
                });
                return;
            }

            var html = '<div>';
            html += '<video  width="600px" height="400px"  controls="controls" autobuffer="autobuffer" autoplay="autoplay" loop="loop"  x-webkit-airplay="true" x5-video-player-fullscreen="true" preload="auto" playsinline="true" webkit-playsinline x5-video-player-typ="h5" >';
            html += '<source src="' + url + '" type="video/mp4" />';
            html += '<source src="' + url + '" type="application/x-mpegURL"  />';
            html += '<source src="' + url + '" type="video/ogg" />';
            html += '<source src="' + url + '" type="video/webm" />';
            html += '<object data="' + url + '" height="600px"  width="400px" />';
            html += '<embed  src="' + url + '" height="600px"  width="400px" />';
            html += '</video>';
            html += '</div>';
            //弹出层
            layer.open({
                type: 1,
                icon: 2,
                maxWidth: 600,
                //area: ['auto' , 'auto'], //宽高
                skin: 'layui-layer-rim', //加上边框
                title: ["MP4-视频预览", 'font-size:12px;'],
                shadeClose: true,
                content: html,
                offset: ['100px', '8px'],
                success: function (layero) {
                    //layer.setTop(layero); //重点2
                }
            });
        }

        function layerOpen(url, title) {
            parent.layer.open({
                type: 2,
                title: title,
                closeBtn: 1,
                shadeClose: true,
                shade: [0.5],
                area: ['1200px', '500px'],
                content: url
            })
        }
    </script>
    <style>
        html, body {
            height: 100%;
            width: 100%
        }
    </style>
    <style>
        a.toolbar {
            text-decoration: none;
            /*color: #555;*/
            color: #3E92CF;
            cursor: pointer
        }

        a.toolbar:hover {
            text-decoration: underline;
            color: #285f87;
        }

        .liu-title {
            color: orchid;
            font-weight: bolder;
            font-size: larger;
        }
    </style>
</head>