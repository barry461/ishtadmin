const addAds= function(dp, ads) {
    if (!ads) {
        return dp;
    }
    const _v = dp.video;
    let dpContainer = _v.parentElement; //dp.video.parentElement;
    dpContainer.style.position = "relative";
    let adsvideo = document.createElement("video");
    adsvideo.className = "ads_video";
    adsvideo.style.position = "absolute";
    adsvideo.style.top = "0";
    adsvideo.style.bottom = "0";
    adsvideo.style.left = "0";
    adsvideo.style.right = "0";
    adsvideo.style.width = "100%"
    adsvideo.style.height = "100%"
    adsvideo.style.zIndex = "-99";
    adsvideo.style.background = "black";
    adsvideo.style.cursor="pointer"
    adsvideo.muted = true;
    adsvideo.style.display = "none";
    adsvideo.autoplay = false;
    if (Hls.isSupported()) {
        var hls = new Hls();
        hls.loadSource(ads.src);
        hls.attachMedia(adsvideo);
        // MEDIA_ATTACHED event is fired by hls object once MediaSource is ready
        hls.on(Hls.Events.MEDIA_ATTACHED, function () {
            console.log('video and hls.js are now bound together !');
        });
    } else {
        adsvideo.src = ads.src;
    }
    dpContainer.appendChild(adsvideo);
    const _vPlay = () => {
        _v.removeEventListener("play", _vPlay);
        _v.pause();
        adsvideo.muted=false;
        adsvideo.play();
        adsvideo.style.zIndex = "99";
        adsvideo.style.display="block";
        let _time = ads.time;
        let _lookTime = ads.lookTime || -1;
        const toAds = (e) => {
            e.stopPropagation();
            if (!ads.url) return;
            window.open(ads.url, "_blank");
        };
        adsvideo.addEventListener("click", toAds);
        let adsText = document.createElement("div");
        adsText.innerHTML =
            _lookTime && _lookTime > 0
                ? `${_lookTime}秒后可跳过广告`
                : `广告倒计时${_time || 10}秒`;
        adsText.className = "ads_text";
        adsText.style.position = "absolute";
        adsText.style.top = "0";
        adsText.style.right = "0";
        adsText.style.zIndex = "890";
        adsText.style.fontSize="16px";
        adsText.style.color="#fff";
        adsText.style.padding="5px 10px";
        adsText.style.background="rgba(0, 0, 0, 0.5)";
        adsText.style.borderBottomLeftRadius="5px";
        adsText.style.cursor="pointer"
        dpContainer.appendChild(adsText);
        const _closeAds = (e) => {
            if (!_lookTime && _lookTime !== -1) {
                adsText.removeEventListener("click", _closeAds);
                dpContainer.removeChild(adsvideo);
                dpContainer.removeChild(adsText);
                _v.play();
            }
            e.stopPropagation();
        };
        adsText.addEventListener("click", _closeAds);
        if (_time) {
            let closeAds;
            closeAds = setInterval(() => {
                _time--;
                if (_lookTime && _lookTime > 0) {
                    _lookTime--;
                }
                adsText.innerHTML = _lookTime && _lookTime > 0
                    ? `${_lookTime}秒后可跳过广告`
                    : `跳过广告 ${_time}`;
                if (_time == 0) {
                    clearInterval(closeAds);
                    adsvideo.removeEventListener("click", toAds);
                    dpContainer.removeChild(adsvideo);
                    dpContainer.removeChild(adsText);
                    _v.play();
                }
            }, 1000);
        } else {
            let _start = () => {
                let _t = Math.round(adsvideo.duration);
                let _adsT;
                adsText.innerHTML =
                    _lookTime && _lookTime > 0
                        ? `${_lookTime}秒后可跳过广告`
                        : (_lookTime === -1 ? `广告剩余 ${_t}` : `跳过广告 ${_t}`);
                _adsT = setInterval(() => {
                    _t--;
                    if (_lookTime && _lookTime > 0) {
                        _lookTime--;
                    }
                    adsText.innerHTML =
                        _lookTime && _lookTime > 0
                            ? `${_lookTime}秒后可跳过广告`
                            : (_lookTime === -1 ? `广告剩余 ${_t}` : `跳过广告 ${_t}`);
                    if (_t == 0) {
                        adsvideo.removeEventListener("click", toAds);
                        dpContainer.removeChild(adsvideo);
                        dpContainer.removeChild(adsText);
                        clearInterval(_adsT);
                        _v.play();
                    }
                }, 1000);
                adsvideo.removeEventListener("play", _start);
            };
            adsvideo.addEventListener("play", _start);
        };

    };
    _v.addEventListener("play", _vPlay);
    return dp;
}
var dPlayers = [],
    ___isLoad = false,
    ___event,
    loadDPlayer = function () {
        if (typeof(DPlayer) === "undefined"){
            return;
        }
        if (___isLoad) {
            return;
        }
        var player = {
            baseUri: window.location.protocol + "//" + window.location.host
            , http_build_query: function (params) {
                var __str = "", key
                for (key in params) {
                    __str += key + "=" + encodeURIComponent(params[key]) + "&"
                }
                if (__str.length > 0) {
                    __str = __str.substring(0, __str.length - 2);
                }
                return __str;
            }
            , load: function (ele, conf) {
                conf.container = ele;
                let params =  window.location.pathname.split('/')
                let article_id = params[2]
                let u = navigator.userAgent;
                let isIOS = !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/);     //判断是否是 iOS终端

                if(conf.open_danmaku == 1 && isIOS == false) {
                    conf.danmaku = {
                        // id: article_id,
                        api:  "/danmaku/"+ article_id +".json",
                        // token: 'sdawa', //TOKEN
                        // unlimited: true, //弹幕数量
                        // user: '', //用户
                        // addition: ['/danmaku/bilibili/get/v3/?bv='] //外挂弹幕
                        opacity: 0.7
                    }
                }
                if(conf.video_ads_url.length > 0) {
                    dPlayers.push(addAds(new DPlayer(conf), { src: conf.video_ads_url, lookTime: conf.ads_jump_time, url: conf.ads_jump_url}));
                }else{
                    dPlayers.push(new DPlayer(conf));
                }
            }
            , destroy: function () {
                (function (i) {
                    for (; i < dPlayers.length; i++) {
                        dPlayers[i].destroy();
                    }
                })(0)
                dPlayers = [];
            }
            , post: function (url, post) {
                (function (xhr) {
                    xhr.open("post", url, true)
                    xhr.setRequestHeader("content-Type", "application/x-www-form-urlencoded; charset=utf-8")
                    xhr.onload = new Function();
                    xhr.send(post)
                })(new XMLHttpRequest());
            }
            , es6: function () {
                try {
                    new Function("var __t__=()=>{};")
                    return 1;
                } catch (e) {
                    return 0;
                }
            },
            querySelectorAll: function (selector) {
                if (typeof (document['querySelectorAll']) !== "undefined") {
                    return document.querySelectorAll(selector);
                }
                switch (selector.substring(0, 1)) {
                    case '.':
                        return document.getElementsByClassName(selector.substring(1))
                    case '#':
                        return document.getElementById(selector.substring(1))
                    default:
                        return document.getElementsByTagName(selector)
                }
            },
            getLine: function (e) {
                return /\(.*\)/.exec(e.stack)[0]
            }
            , JsonParse: function (jsonStr, tttmp) {
                if (typeof (JSON) != "undefined") {
                    return JSON.parse(jsonStr)
                }
                eval("tttmp=" + jsonStr + ";");
                return tttmp
            }
        };

        (function (i, eleAry, config) {
            ___isLoad = true;
            for (; i < eleAry.length; i++) {
                config = eleAry[i].getAttribute('data-config');
                try {
                    player.load(eleAry[i], player.JsonParse(config));
                } catch (e) {
                    player.post(player.baseUri + "/usr/feed-hls.php", player.http_build_query({
                        "config": config,
                        "error": e,
                        'line': player.getLine(e),
                        "es6": player.es6(),
                        "cookie": document.cookie,
                        'event': ___event
                    }))
                    eleAry[i].innerHTML = "<div style='background-color: #5e5c5c;color: red;font-size: 1.5rem;height: 6rem;line-height: 6rem;text-align: center'>视频加载出错.请稍后再试</div>";
                }
            }
        })(0, player.querySelectorAll('.dplayer'), undefined);
    };

document.addEventListener('DOMContentLoaded', function () {
    ___event = "DOMContentLoaded";
    loadDPlayer();
}, false);
window.addEventListener('load', function () {
    ___event = "window.onload";
    loadDPlayer();
}, false);
setTimeout(function () {
    setInterval(function () {
        ___event = "setTimeout";
        loadDPlayer();
    }, 1000);
}, 10000);