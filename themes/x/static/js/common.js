var autoHideElements = {};
var CSS = function (css) {
    var link = document.createElement('link');
    link.setAttribute('rel', 'stylesheet');
    link.href = css;
    document.head.appendChild(link);
};
var STYLE = function (style, type) {
    type = type || 'text/css';
    var s = document.createElement('style');
    s.type = type;
    s.textContent = style;
    document.head.appendChild(s);
};
var JS = function (js, async) {
    async = async || false;
    var sc = document.createElement('script'), s = document.scripts[0];
    sc.src = js; sc.async = async;
    s.parentNode.insertBefore(sc, s);
};
var registAutoHideElement = function (selector) {
    var tmp = autoHideElements[selector];
    if (typeof(tmp) !== 'undefined') {
        return;
    }
    var element = document.querySelector(selector);
    if (element && typeof(Headroom) !== "undefined") {
        var headroom = new Headroom(element, {
            tolerance: 5,
            offset : 5,
            classes: {
                initial: "show",
                pinned: "show",
                unpinned: "hide"
            }
        });
        headroom.init();
        autoHideElements[selector] = headroom;
    }
};
var ab2b64 = function (t) {
    return new Promise(function (e) {
        const n = new Blob([t]);
        const r = new FileReader();
        r.onload = function (t) {
            const n = t.target.result;
            const r = n.substring(n.indexOf(",") + 1);
            e(r);
        };
        r.readAsDataURL(n);
    });
};
var getImageAddon = function (cdnType, width, height) {
    if (!LocalConst.ENABLE_IMAGE_SIZE_OPTIMIZE) {
        return "";
    }
    if (cdnType == LocalConst.CDN_TYPE_LOCAL || cdnType == LocalConst.CDN_TYPE_OTHERS) {
        return "";
    }
    var addon = "?";
    if (cdnType == LocalConst.CDN_TYPE_UPYUN) {
        addon = LocalConst.UPYUN_SPLIT_TAG;
    }
    var ratio = window.devicePixelRatio || 1;
    width = width || window.innerWidth;
    height = height || window.innerHeight;
    width = width || 0;
    height = height || 0;
    if (width == 0 && height == 0) {
        return "";
    }
    var format = "";
    if (LocalConst.ENABLE_WEBP) {
        if (cdnType == LocalConst.CDN_TYPE_ALIYUN_OSS) {
            format = "/format,webp"
        } else {
            format = "/format/webp";
        }
    }
    if (width >= height) {
        if (cdnType == LocalConst.CDN_TYPE_UPYUN) {
            addon += "/fw/" + parseInt(width * ratio) + "/quality/75" + format;
        } else if(cdnType == LocalConst.CDN_TYPE_ALIYUN_OSS) {
            addon += "x-oss-process=image/resize,w_" + parseInt(width * ratio) + "/quality,Q_75" + format;
        } else {
            addon += "imageView2/2/w/" + parseInt(width * ratio) + "/q/75" + format;
        }
    } else {
        if (cdnType == LocalConst.CDN_TYPE_UPYUN) {
            addon += "/fh/" + parseInt(width * ratio) + "/quality/75" + format;
        } else if(cdnType == LocalConst.CDN_TYPE_ALIYUN_OSS) {
            addon += "x-oss-process=image/resize,h_" + parseInt(width * ratio) + "/quality,Q_75" + format;
        } else {
            addon += "imageView2/2/h/" + parseInt(height * ratio) + "/q/75" + format;
        }
    }
    return addon;
};
var getBgHeight = function(windowHeight, bannerHeight, mobileBannerHeight){
    windowHeight = windowHeight || 560;
    if (windowHeight > window.screen.availHeight) {
        windowHeight = window.screen.availHeight;
    }
    bannerHeight = bannerHeight.trim();
    mobileBannerHeight = mobileBannerHeight.trim();
    if (window.innerHeight > window.innerWidth) {
        bannerHeight = parseFloat(mobileBannerHeight);
    } else {
        bannerHeight = parseFloat(bannerHeight);
    }
    bannerHeight = Math.round(windowHeight * bannerHeight / 100);
    return bannerHeight;
};
var registLoadBanner = function () {
    if (window.asyncBannerLoadNum >= 0) {
        window.asyncBannerLoadNum ++;
        Mlog("Loading Banner: " + window.asyncBannerLoadNum);
    }
};
var remove = function (element) {
    if (element) {
        if (typeof element['remove'] === 'function') {
            element.remove();
        } else if (element.parentNode) {
            element.parentNode.removeChild(element);
        }
    }
};

var is_cdnimg = function (path) {
    if (typeof (path) !== "string") {
        return false
    }
    if (path.indexOf("/xiao/") !== -1) {
        return true;
    }
    if (path.indexOf("/upload/upload/") !== -1) {
        return true;
    }
    if (path.indexOf("/upload_01/") !== -1) {
        return true;
    }
    return false;
}

var loadBackgroundImage = function (bgUrl , bgEle){
    if (is_cdnimg(bgUrl)) {
        $.ajax(bgUrl, {
            xhrFields: {responseType: 'arraybuffer'}
        }).then((res) => {
            ab2b64(res).then((base64str) => {
                let ary = bgUrl.split('.'),decryptStr = decryptImage(base64str);
                bgEle.style.backgroundImage = 'url("data:image/'+ary.pop()+';base64,'+decryptStr+'")';
            });
        })
    } else {
        bgEle.style.backgroundImage = 'url("' + bgUrl + '")';
    }
}

var loadBannerDirect = function (backgroundImage, backgroundPosition, wrap, cdnType, width, height) {
    var background = wrap.querySelector('.blog-background');
    var imageSrc = backgroundImage + getImageAddon(cdnType, width, height);

    Mlog("Start Loading Banner Direct... url: " + imageSrc + "  cdnType: " + cdnType);

    if (typeof (backgroundPosition) === 'string' && backgroundPosition.length > 0) {
        background.style.backgroundPosition = backgroundPosition;
    }
    loadBackgroundImage(backgroundImage, background);
};
var loadBanner = function (img, backgroundImage, backgroundPosition, wrap, cdnType, width, height, blured) {
    var background = wrap.querySelector('.blog-background');
    var container = wrap.querySelector('.lazyload-container');

    if (!background) {
        console.warn("background is null", background);
        return;
    }
    if (!container) {
        console.warn("container is null", container);
        return;
    }

    var imageSrc = backgroundImage + getImageAddon(cdnType, width, height);

    Mlog("Start Loading Banner... url: " + imageSrc + "  cdnType: " + cdnType);


    background.classList.add("loading");

    remove(img);
    if (typeof(backgroundPosition) === 'string' && backgroundPosition.length > 0) {
        container.style.backgroundPosition = backgroundPosition;
        background.style.backgroundPosition = backgroundPosition;
    }
    container.style.backgroundImage = 'url("' + img.src + '")';
    container.classList.add('loaded');

    blured = blured || false;
    if (blured) {
        return;
    }

    // load Src background image
    var largeImage = new Image();
    largeImage.src = imageSrc;
    largeImage.onload = function() {
        remove(this);
        if (typeof imageLoad !== 'undefined' && imageLoad >= 1) {
            background.classList.add('bg-failed');
        } else {
            background.style.backgroundImage = 'url("' + imageSrc + '")';
            background.classList.remove('loading');
            container.classList.remove('loaded');
        }
        setTimeout(function () {
            remove(container);
            if (window.asyncBannerLoadCompleteNum >= 0) {
                window.asyncBannerLoadCompleteNum ++;
                Mlog("Loaded Banner: " + window.asyncBannerLoadCompleteNum);
                if (window.asyncBannerLoadCompleteNum === window.asyncBannerLoadNum) {
                    window.asyncBannerLoadNum = -1170;
                    window.asyncBannerLoadCompleteNum = -1170;
                    $('body').trigger("ajax-banner:done");
                } else if (window.asyncBannerLoadCompleteNum > window.asyncBannerLoadNum) {
                    console.error("loaded num is large than load num.");
                    setTimeout(function () {
                        window.asyncBannerLoadNum = -1170;
                        window.asyncBannerLoadCompleteNum = -1170;
                        $('body').trigger("ajax-banner:done");
                    }, 1170);
                }
            }
        }, 1001);
    };
};
var loadPrefersDarkModeState = function () {
    var indicator = document.createElement('div');
    indicator.setAttribute('data-xxx' , 'my');
    indicator.className = 'dark-mode-state-indicator';
    document.body.appendChild(indicator);
    if (parseInt(mGetComputedStyle(indicator, 'z-index'), 10) === 11) {
        LocalConst.PREFERS_DARK_MODE = true;
    }
    document.body.removeChild(indicator);
    //remove(indicator);
};
var mGetComputedStyle = function (element, style) {
    var value;
    if (window.getComputedStyle) {
        // modern browsers
        value = window.getComputedStyle(element).getPropertyValue(style);
    } else if (element.currentStyle) {
        // ie8-
        value = element.currentStyle[style];
    }
    return value;
};


var loadImageEle = function (imgUrl, imgEle) {
    $.ajax(imgUrl, {
        xhrFields: {responseType: 'arraybuffer'}
    }).then((res) => {
        ab2b64(res).then((base64str) => {
            let ary = imgUrl.split('.'), decryptStr = decryptImage(base64str);
            imgEle.src = 'data:image/' + ary.pop() + ';base64,' + decryptStr + '';
        });
    })
}

var loadImage = function (imgUrl , imgEleId){
    loadImageEle(imgUrl , document.getElementById(imgEleId));
    return null;


    var imgEle = document.getElementById(imgEleId);
    if (imgUrl.indexOf("/new/") > 0 || imgUrl.indexOf("/xiao/") > 0) {
        $.ajax(imgUrl, {
            xhrFields: {responseType: 'arraybuffer'}
        }).then((res) => {
            ab2b64(res).then((base64str) => {
                let ary = imgEleId.split('.'),decryptStr = decryptImage(base64str);
                imgEle.src = 'data:image/'+ary.pop()+';base64,'+decryptStr+'';
            });
        })
    } else {
        imgEle.src = '"' + imgUrl + '"';
    }
}