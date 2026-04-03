@php
    $ads = AdvertModel::getAdsByPosition(AdvertModel::POSITION_HOME_POP, true);
    $apps = AdvertModel::getAdsByPosition(AdvertModel::POSITION_APP_CENTER_POP, true);
    list($popup_x,$popup_y) = theme_options()->appCenterPopSizeXY;
@endphp
<script type="text/javascript">

    let apps = {!! json_encode($apps) !!},
        sortApps = [],
        unsortedApps = [];
    if (apps.length === 0) {
        // console.log("get data of apps error ");
    }
    for (let i = 0; i < apps.length; i++) {
        if (parseInt(apps[i].sort) > 0) {
            sortApps.push(apps[i])
        } else {
            unsortedApps.push(apps[i])
        }
    }

    function shuffle(a) {
        for (let i = a.length; i; i--) {
            let j = Math.floor(Math.random() * i);
            [a[i - 1], a[j]] = [a[j], a[i - 1]];
        }
        return a;
    }


    $(function () {
        var storage = {
            get: function (k) {
                return window.localStorage ? localStorage.getItem(k) : null;
            },
            set: function (k, v) {
                return window.localStorage ? localStorage.setItem(k, v) : null;
            },
            incr: function (k) {
                var v = this.get(k);
                return this.set(k, (v ? parseInt(v) : 0) + 1)
            },
            del: function (k) {
                return window.localStorage && localStorage.removeItem(k)
            }
        }
        var referrer = document.referrer,
            key = 0;
        if (referrer.length > 0) {
            if (!/archives\/[\d]+/i.test(referrer)) {
                if (referrer.indexOf(location.host) >= 0) {
                    return;
                }
            } else {
                if (window.localStorage) {
                    let key = "last-pop-ad",
                        curDate = formatDate(new Date()),
                        adDate = storage.get(key) || formatDate(0)
                    if (curDate === adDate) {
                        return;
                    }
                    storage.set(key, curDate)
                }
            }
        }

        function formatDate(date) {
            return (new Date(date)).toISOString().slice(0, 10).replaceAll('-', '')
        }

        function jmImg(selector, warpEle) {
            var ele = $(selector), url = ele.data('src')
            $.ajax(url, {xhrFields: {responseType: 'arraybuffer'}}).then((res) => {
                ab2b64(res).then((base64str) => {
                    let ary = url.split('.'), decryptStr = decryptImage(base64str);
                    ele.attr('src', 'data:image/' + ary.pop() + ';base64,' + decryptStr);
                    $(warpEle).show();
                });
            })
        }

        function render(data) {
            let html = `<div class="adspop" style="display:none">
        <div class="popup-container">
            <div class="popup-content">
                <div class="popup-close">{!! theme()->importImg('/usr/themes/ads-close.png', '关闭广告') !!}</div>
                <div class="popup-picture">
                    <img data-src="${data.img_url}" data-uri="${data.link}" alt="${data.title || data.name || ''}">
                </div>
            </div>
        </div>
    </div>`;
            $(body).append(html);
            jmImg('.adspop .popup-picture>img', '.adspop');
        }

        let json = {!! json_encode($ads) !!};
        if (json.length === 0) {
            return;
        }

        $(body).delegate('.adspop .popup-close', 'click', function () {
            $('.adspop').remove();
            if (++key < json.length) {
                render(json[key]);
            } else {
            }
        })
        $(body).delegate('.adspop .popup-picture, .adspop .application-list a', 'click', function (e) {
            let uri = $(this).find('img').data('uri');
            if (typeof (uri) !== "string" || uri.length < 1) {
                return;
            }
            window.open(uri);
            e.stopPropagation();
        });

        const showImg = (url, ele) => {
            if (!url) return;
            $.ajax(url, {xhrFields: {responseType: 'arraybuffer'}}).then((res) => {
                ab2b64(res).then((base64str) => {
                    let ary = url.split('.'), decryptStr = decryptImage(base64str);
                    ele.attr('src', 'data:image/' + ary.pop() + ';base64,' + decryptStr);
                });
            })
        }

        async function jmImgs(selector) {
            let eles = $(selector);
            eles.each((index, ele) => {
                let url = $(ele).data('src');
                showImg(url, $(ele));
            })
        }

        function renderAppCenter() {
            let html = `<div class="adspop" style="display:none">
                            <div class="application-popup">
                              <div class="application-content">
                                <div class="application-header">
                                  <img src="{!! theme()->image('/usr/themes/Mirages/images/popup_header.png') !!}" alt="应用推荐" />
                                </div>
                                <div class="application-list">
                                </div>
                              </div>
                            </div>
                        </div>`;
            $(body).append(html);

            let count = <?=$popup_x?> * <?=$popup_y?>;
            let viewed = 0;
            let randApps = shuffle(sortApps);
            for (let i = 0; i < randApps.length; i++) {
                if (count > viewed) {
                    $('.adspop .application-list').append(
                        `<a href="javascript:;"><img src="{!! theme()->image(options('img_zwimg')) !!}" data-src="${randApps[i].img_url}" data-uri="${randApps[i].link}" alt="${randApps[i].title || '应用下载'}" /> <p>${randApps[i].title}</p></a>`
                    );
                    viewed++;
                } else {
                    break;
                }
            }
            if (count > viewed) {
                randApps = shuffle(unsortedApps);
                for (let i = 0; i < randApps.length; i++) {
                    if (count > viewed) {
                        $('.adspop .application-list').append(
                            `<a href="javascript:;"><img src="{!! theme()->image(options('img_zwimg')) !!}" data-src="${randApps[i].img_url}" data-uri="${randApps[i].link}" alt="${randApps[i].title || '应用下载'}" /> <p>${randApps[i].title}</p></a>`
                        );
                        viewed++;
                    } else {
                        break;
                    }
                }
            }

            jmImgs('.adspop .application-list a img').then(function () {
                $('.adspop').show();
            });
        }

        $(body).delegate('.adspop .application-popup', 'click', function () {
            $('.adspop').remove();
        });
        render(json[key]);
    })
</script>