/**
 * 网站追踪系统
 * 提供页面浏览、用户行为、广告等事件的追踪能力
 */

(function(window, document) {
    'use strict';

    // MD5 哈希函数（用于生成 event_id）
    var md5 = (function() {
        function md5cycle(x, k) {
            var a = x[0], b = x[1], c = x[2], d = x[3];
            a = ff(a, b, c, d, k[0], 7, -680876936);
            d = ff(d, a, b, c, k[1], 12, -389564586);
            c = ff(c, d, a, b, k[2], 17, 606105819);
            b = ff(b, c, d, a, k[3], 22, -1044525330);
            a = ff(a, b, c, d, k[4], 7, -176418897);
            d = ff(d, a, b, c, k[5], 12, 1200080426);
            c = ff(c, d, a, b, k[6], 17, -1473231341);
            b = ff(b, c, d, a, k[7], 22, -45705983);
            a = ff(a, b, c, d, k[8], 7, 1770035416);
            d = ff(d, a, b, c, k[9], 12, -1958414417);
            c = ff(c, d, a, b, k[10], 17, -42063);
            b = ff(b, c, d, a, k[11], 22, -1990404162);
            a = ff(a, b, c, d, k[12], 7, 1804603682);
            d = ff(d, a, b, c, k[13], 12, -40341101);
            c = ff(c, d, a, b, k[14], 17, -1502002290);
            b = ff(b, c, d, a, k[15], 22, 1236535329);
            a = gg(a, b, c, d, k[1], 5, -165796510);
            d = gg(d, a, b, c, k[6], 9, -1069501632);
            c = gg(c, d, a, b, k[11], 14, 643717713);
            b = gg(b, c, d, a, k[0], 20, -373897302);
            a = gg(a, b, c, d, k[5], 5, -701558691);
            d = gg(d, a, b, c, k[10], 9, 38016083);
            c = gg(c, d, a, b, k[15], 14, -660478335);
            b = gg(b, c, d, a, k[4], 20, -405537848);
            a = gg(a, b, c, d, k[9], 5, 568446438);
            d = gg(d, a, b, c, k[14], 9, -1019803690);
            c = gg(c, d, a, b, k[3], 14, -187363961);
            b = gg(b, c, d, a, k[8], 20, 1163531501);
            a = gg(a, b, c, d, k[13], 5, -1444681467);
            d = gg(d, a, b, c, k[2], 9, -51403784);
            c = gg(c, d, a, b, k[7], 14, 1735328473);
            b = gg(b, c, d, a, k[12], 20, -1926607734);
            a = hh(a, b, c, d, k[5], 4, -378558);
            d = hh(d, a, b, c, k[8], 11, -2022574463);
            c = hh(c, d, a, b, k[11], 16, 1839030562);
            b = hh(b, c, d, a, k[14], 23, -35309556);
            a = hh(a, b, c, d, k[1], 4, -1530992060);
            d = hh(d, a, b, c, k[4], 11, 1272893353);
            c = hh(c, d, a, b, k[7], 16, -155497632);
            b = hh(b, c, d, a, k[10], 23, -1094730640);
            a = hh(a, b, c, d, k[13], 4, 681279174);
            d = hh(d, a, b, c, k[0], 11, -358537222);
            c = hh(c, d, a, b, k[3], 16, -722521979);
            b = hh(b, c, d, a, k[6], 23, 76029189);
            a = hh(a, b, c, d, k[9], 4, -640364487);
            d = hh(d, a, b, c, k[12], 11, -421815835);
            c = hh(c, d, a, b, k[15], 16, 530742520);
            b = hh(b, c, d, a, k[2], 23, -995338651);
            a = ii(a, b, c, d, k[0], 6, -198630844);
            d = ii(d, a, b, c, k[7], 10, 1126891415);
            c = ii(c, d, a, b, k[14], 15, -1416354905);
            b = ii(b, c, d, a, k[5], 21, -57434055);
            a = ii(a, b, c, d, k[12], 6, 1700485571);
            d = ii(d, a, b, c, k[3], 10, -1894986606);
            c = ii(c, d, a, b, k[10], 15, -1051523);
            b = ii(b, c, d, a, k[1], 21, -2054922799);
            a = ii(a, b, c, d, k[8], 6, 1873313359);
            d = ii(d, a, b, c, k[15], 10, -30611744);
            c = ii(c, d, a, b, k[6], 15, -1560198380);
            b = ii(b, c, d, a, k[13], 21, 1309151649);
            a = ii(a, b, c, d, k[4], 6, -145523070);
            d = ii(d, a, b, c, k[11], 10, -1120210379);
            c = ii(c, d, a, b, k[2], 15, 718787259);
            b = ii(b, c, d, a, k[9], 21, -343485551);
            x[0] = add32(a, x[0]);
            x[1] = add32(b, x[1]);
            x[2] = add32(c, x[2]);
            x[3] = add32(d, x[3]);
        }
        function cmn(q, a, b, x, s, t) {
            a = add32(add32(a, q), add32(x, t));
            return add32((a << s) | (a >>> (32 - s)), b);
        }
        function ff(a, b, c, d, x, s, t) {
            return cmn((b & c) | ((~b) & d), a, b, x, s, t);
        }
        function gg(a, b, c, d, x, s, t) {
            return cmn((b & d) | (c & (~d)), a, b, x, s, t);
        }
        function hh(a, b, c, d, x, s, t) {
            return cmn(b ^ c ^ d, a, b, x, s, t);
        }
        function ii(a, b, c, d, x, s, t) {
            return cmn(c ^ (b | (~d)), a, b, x, s, t);
        }
        function md51(s) {
            var n = s.length, state = [1732584193, -271733879, -1732584194, 271733878], i;
            for (i = 64; i <= s.length; i += 64) {
                md5cycle(state, md5blk(s.substring(i - 64, i)));
            }
            s = s.substring(i - 64);
            var tail = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            for (i = 0; i < s.length; i++)
                tail[i >> 2] |= s.charCodeAt(i) << ((i % 4) << 3);
            tail[i >> 2] |= 0x80 << ((i % 4) << 3);
            if (i > 55) {
                md5cycle(state, tail);
                for (i = 0; i < 16; i++) tail[i] = 0;
            }
            tail[14] = n * 8;
            md5cycle(state, tail);
            return state;
        }
        function md5blk(s) {
            var md5blks = [], i;
            for (i = 0; i < 64; i += 4) {
                md5blks[i >> 2] = s.charCodeAt(i) + (s.charCodeAt(i + 1) << 8) + (s.charCodeAt(i + 2) << 16) + (s.charCodeAt(i + 3) << 24);
            }
            return md5blks;
        }
        var hex_chr = '0123456789abcdef'.split('');
        function rhex(n) {
            var s = '', j = 0;
            for (; j < 4; j++)
                s += hex_chr[(n >> (j * 8 + 4)) & 0x0F] + hex_chr[(n >> (j * 8)) & 0x0F];
            return s;
        }
        function hex(x) {
            for (var i = 0; i < x.length; i++)
                x[i] = rhex(x[i]);
            return x.join('');
        }
        function add32(a, b) {
            return (a + b) & 0xFFFFFFFF;
        }
        return function(s) {
            return hex(md51(s));
        };
    })();

    // 生成 event_id（根据所有字段值计算 MD5）
    var generateEventId = function(data) {
        var values = [];
        var keys = Object.keys(data).sort(); // 排序确保一致性
        for (var i = 0; i < keys.length; i++) {
            var key = keys[i];
            if (key !== 'event_id') { // 排除 event_id 本身
                var value = data[key];
                if (value !== null && value !== undefined) {
                    values.push(String(value));
                }
            }
        }
        return md5(values.join(''));
    };

    // 异步加载追踪 SDK
    (function loadTrackingSDK() {
        var sdkUrl = "/themes/x/static/js/web-sdk.js";
        var trackerFunctionName = "tracker";
        
        // 创建追踪函数的临时桩，用于在 SDK 加载前收集调用
        window[trackerFunctionName] = window[trackerFunctionName] || function createTrackerStub() {
            var callArguments = Array.prototype.slice.call(arguments);
            
            return new Promise(function(resolve, reject) {
                // 将调用存入队列，等待 SDK 加载后处理
                var queue = window[trackerFunctionName].q = window[trackerFunctionName].q || [];
                queue.push({
                    args: callArguments,
                    resolve: resolve,
                    reject: reject
                });
            });
        };
        
        // 记录脚本加载时间戳
        window[trackerFunctionName].l = 1 * new Date();
        
        // 动态创建并插入 script 标签
        var scriptElement = document.createElement("script");
        var firstScriptTag = document.getElementsByTagName("script")[0];
        
        scriptElement.async = 1;  // 异步加载
        scriptElement.src = sdkUrl;
        
        // 将新脚本插入到第一个 script 标签之前
        firstScriptTag.parentNode.insertBefore(scriptElement, firstScriptTag);
    })();

    // 初始化 SDK（等待配置注入）
    window.initTrackingSDK = function(config) {
        var initTimer = setInterval(function () {
            if (window.WebSDK) {
                clearInterval(initTimer);
                
                if (typeof window.WebSDK.init === "function") {
                    window.WebSDK.init(config);
                }
                
                var queuedCalls = (window.tracker && window.tracker.q) || [];
                window.tracker = function () {
                    return window.WebSDK.apply(null, arguments);
                };
                window.tracker.q = [];
                
                queuedCalls.forEach(function (call) {
                    window.tracker.apply(null, call.args).then(call.resolve).catch(call.reject);
                });
                queuedCalls.length = 0;
            }
        }, 30);
    };

    // 广告展示批量上报器
    window.initAdImpressionBatcher = function(config) {
        var batchConfig = {
            endpoint: config.endpoint || 'https://api.shuifeng.cc/api/eventTracking/batchReport.json',
            appId: config.appId,
            channel: config.channel || 'test',
            batchSize: config.batchSize || 10,
            flushInterval: config.flushInterval || 5000
        };
        
        var storage = {
            get: function(key) { return localStorage.getItem(key) || ''; },
            set: function(key, val) { localStorage.setItem(key, val); }
        };
        
        var generateUUID = function() {
            return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random() * 16 | 0;
                var v = c == 'x' ? r : (r & 0x3 | 0x8);
                return v.toString(16);
            });
        };
        
        var deviceId = storage.get('bp_did') || (function() {
            var id = generateUUID();
            storage.set('bp_did', id);
            return id;
        })();
        var userId = storage.get('bp_uid') || '0';
        var sessionId = sessionStorage.getItem('bp_sid') || (function() {
            var id = generateUUID();
            sessionStorage.setItem('bp_sid', id);
            return id;
        })();

        var queue = [];
        
        var flush = function() {
            if (queue.length === 0) return;
            
            var batch = queue.splice(0, 50);
            fetch(batchConfig.endpoint, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(batch)
            }).catch(function(err) {
                console.error('Batch tracking error:', err);
            });
        };
        
        setInterval(flush, batchConfig.flushInterval);
        window.addEventListener('beforeunload', flush);

        window.adImpressionBatcher = {
            track: function(data) {
                var eventData = {
                    app_id: batchConfig.appId,
                    channel: batchConfig.channel,
                    client_ts: Math.floor(Date.now() / 1000),
                    device: 'android',
                    deviceBrand: 'web',
                    deviceModel: navigator.platform,
                    device_id: deviceId,
                    sid: sessionId,
                    uid: userId,
                    user_agent: navigator.userAgent,
                    event: data.event
                };
                
                var payload = {};
                var commonKeys = Object.keys(eventData);
                for (var key in data) {
                    if (!commonKeys.includes(key) && key !== 'event') {
                        payload[key] = data[key];
                    }
                }
                
                eventData.payload = payload;
                
                // 生成 event_id（基于所有字段的 MD5）
                eventData.event_id = generateEventId(eventData);
                
                queue.push(eventData);
                
                if (queue.length >= batchConfig.batchSize) {
                    flush();
                }
            }
        };
    };

    // 事件追踪初始化
    document.addEventListener('DOMContentLoaded', function() {
        var track = function() {
            return window.tracker.apply(window, arguments);
        };
        
        // 后端注入的追踪数据
        var backendTracking = window.TRACKING_DATA || null;
        
        if (backendTracking) {
            track(backendTracking).catch(function(err) {
                console.error('Tracking error:', err);
            });
        }

        // 页面浏览追踪
        track({
            event: "app_page_view",
            page_key: window.location.pathname,
            page_name: document.title
        }).catch(function(err) {
            console.error('Tracking error:', err);
        });

        // 视频播放器追踪
        var trackedVideos = new WeakSet();
        setInterval(function() {
            if (typeof window.dPlayers !== 'undefined' && Array.isArray(window.dPlayers)) {
                window.dPlayers.forEach(function(dp) {
                    if (dp.video && !trackedVideos.has(dp.video)) {
                        trackedVideos.add(dp.video);
                        
                        var lastPlayTime = 0;
                        var isPlaying = false;
                        
                        // Helper to build payload
                        var getPayload = function(behaviorKey, behaviorName) {
                            var common = (typeof backendTracking !== 'undefined' && backendTracking) ? backendTracking : {};
                            var segDur = (isPlaying && lastPlayTime > 0) ? Math.round((Date.now() - lastPlayTime) / 1000) : 0;
                            
                            return {
                                event: 'video_event',
                                video_id: common.page_key || window.location.pathname,
                                video_title: common.page_name || document.title,
                                video_type_id: common.video_type_id || '',
                                video_type_name: common.video_type_name || '',
                                video_tag_key: common.video_tag_key || '',
                                video_tag_name: common.video_tag_name || '',
                                video_duration: Math.round(dp.video.duration || 0),
                                play_duration: segDur,
                                play_progress: dp.video.duration ? Math.round((dp.video.currentTime / dp.video.duration) * 100) : 0,
                                video_behavior_key: behaviorKey,
                                video_behavior_name: behaviorName
                            };
                        };
                        
                        // 监听播放事件
                        dp.on('play', function() {
                            lastPlayTime = Date.now();
                            isPlaying = true;
                            track(getPayload('video_play', '播放')).catch(function(e) {
                                console.error(e);
                            });
                        });
                        
                        // 监听暂停事件
                        dp.on('pause', function() {
                            if (isPlaying) {
                                track(getPayload('video_pause', '暂停')).catch(function(e) {
                                    console.error(e);
                                });
                                isPlaying = false;
                            }
                        });
                        
                        // 监听播放完成事件
                        dp.on('ended', function() {
                            track(getPayload('video_complete', '播放完成')).catch(function(e) {
                                console.error(e);
                            });
                            isPlaying = false;
                        });
                        
                        // 视频展示追踪
                        track(getPayload('video_view', '展示')).catch(function(e) {
                            console.error(e);
                        });
                    }
                });
            }
        }, 2000);
    });

    // Ajax 拦截 (后端埋点数据)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ajaxSuccess(function(event, xhr, settings) {
            try {
                if (xhr.responseJSON && xhr.responseJSON.tracking) {
                    var trackingData = xhr.responseJSON.tracking;
                    var trackingEvents = Array.isArray(trackingData) ? trackingData : [trackingData];
                    trackingEvents.forEach(function(eventData) {
                        window.tracker(eventData).catch(function(err) {
                            console.error(err);
                        });
                    });
                }
            } catch (e) {
                console.error('Tracking Error:', e);
            }
        });

        // 导航点击追踪
        jQuery(document).on('click', '[data-type="navigation"]', function() {
            var name = jQuery(this).attr('data-type-name') || jQuery(this).text().trim();
            window.tracker({
                event: "navigation",
                navigation_key: "navigation_" + (name ? name : "unknown"),
                navigation_name: name
            }).catch(function(err) {
                console.error('Tracking error:', err);
            });
        });

        // 广告点击追踪
        jQuery(document).on('click', '[data-type="ad_click"]', function() {
            var el = this;
            var backendTracking = window.TRACKING_DATA || {};
            var pageKey = (backendTracking && backendTracking.page_key) ? backendTracking.page_key : window.location.pathname;
            var pageName = (backendTracking && backendTracking.page_name) ? backendTracking.page_name : document.title;
            
            var payload = {
                event: "ad_click",
                page_key: pageKey,
                page_name: pageName,
                ad_slot_key: el.dataset.slotKey || '',
                ad_slot_name: el.dataset.slotName || '',
                ad_id: el.dataset.adId || '',
                creative_id: el.dataset.creativeId || '',
                ad_type: el.dataset.adType || 'banner',
                ad_idx: el.dataset.adIdx || 0
            };
            
            window.tracker(payload).catch(function(err) {
                console.error(err);
            });
        });

        // 关键词点击追踪
        jQuery(document).on('click', '[data-type="keyword_click"]', function(e) {
            var $this = jQuery(this);
            var url = $this.attr('href');
            var target = $this.attr('target');
            
            var payload = {
                event: "keyword_click",
                keyword: $this.attr('data-keyword') || '',
                click_item_id: $this.attr('data-id') || '',
                click_item_type_key: $this.attr('data-type-key') || '',
                click_item_type_name: $this.attr('data-type-name') || '',
                click_position: parseInt($this.attr('data-position') || 0)
            };
            
            // 如果是链接跳转，拦截等待上报
            if (url && url !== '#' && !url.startsWith('javascript') && (!target || target === '_self') && !e.metaKey && !e.ctrlKey) {
                e.preventDefault();
                var navigated = false;
                var navigate = function() {
                    if (!navigated) {
                        navigated = true;
                        window.location.href = url;
                    }
                };
                
                window.tracker(payload)
                    .then(function() {})
                    .catch(function(err) { console.error(err); })
                    .finally(function() { navigate(); });
                    
                // 500ms 超时强制跳转
                setTimeout(navigate, 500);
            } else {
                window.tracker(payload).catch(function(err) {
                    console.error(err);
                });
            }
        });
    }

    // 广告展示监听
    if ('IntersectionObserver' in window) {
        var adObserver = new IntersectionObserver(function(entries, observer) {
            entries.forEach(function(entry) {
                if (entry.isIntersecting) {
                    var el = entry.target;
                    if (el.dataset.impressed) return;
                    el.dataset.impressed = "true";

                    var backendTracking = window.TRACKING_DATA || {};
                    var payload = {
                        event: "ad_impression",
                        page_key: (backendTracking && backendTracking.page_key) ? backendTracking.page_key : window.location.pathname,
                        page_name: (backendTracking && backendTracking.page_name) ? backendTracking.page_name : document.title,
                        ad_slot_key: el.dataset.slotKey || '',
                        ad_slot_name: el.dataset.slotName || '',
                        ad_id: el.dataset.adId || '',
                        ad_type: el.dataset.adType || 'banner',
                        ad_idx: el.dataset.adIdx || 0
                    };
                    
                    window.adImpressionBatcher.track(payload);
                    observer.unobserve(el);
                }
            });
        }, { threshold: 0.1 });

        // 定时检测新加载的广告
        setInterval(function() {
            document.querySelectorAll('[data-track-impression="true"]:not([data-impressed])').forEach(function(el) {
                if (!el.dataset.observing) {
                    el.dataset.observing = "true";
                    adObserver.observe(el);
                }
            });
        }, 1500);
    }

    // 页面点击追踪
    document.addEventListener('click', function(e) {
        var screenWidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var screenHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        
        var pageX = Math.round(e.pageX);
        var pageY = Math.round(e.pageY);
        var clientX = Math.round(e.clientX);
        var clientY = Math.round(e.clientY);

        var payload = {
            event: 'page_click',
            page_key: window.location.pathname,
            page_name: document.title,
            click_page_x: pageX,
            click_page_y: pageY,
            click_x_percent: Math.round((clientX / screenWidth) * 100),
            click_y_percent: Math.round((clientY / screenHeight) * 100),
            screen_width: screenWidth,
            screen_height: screenHeight
        };

        if (typeof window.tracker === 'function') {
            window.tracker(payload).catch(function(err){});
        }
    }, true);

})(window, document);
