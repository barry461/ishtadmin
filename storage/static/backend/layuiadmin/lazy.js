layui.define(["laytpl", "jquery"], function (exports) {
    "use strict";
    var laytpl = layui.laytpl,
        $ = layui.jquery,
        _options = {},
        _selector = null,
        _data = {},
        _layer = layer,
        _timerId = null;

    let zz = function (selector, data) {
        _selector = selector;
        _data = data || {}
        _options = {
            "type": 1, //弹出层为页面层
            "area": ["700px", false], //弹出成层区域多大
            "title": false, //标题
            "closeBtn": false,//显示关闭按钮
            "shadeClose": true,//点击遮罩层关闭
            "anim": 3, //动画类型
            "offset": 'rt',//打开位置 rt = 右上
            "full": false,//是否全屏
            "success": function () {
            }
        }
        _timerId = setTimeout(zz.prototype.run, 100);
    }

    zz.prototype = {
        stop: function () {
            if (_timerId) {
                clearTimeout(_timerId);
                _timerId = null;
            }
        },
        title: function (title) {
            _options['title'] = title || false
            return this;
        },
        area: function (area) {
            _options['area'] = area || ["700px", false]
            return this;
        },
        width: function (width) {
            _options['area'][0] = (typeof (width) == "number") ? width + 'px' : width
            return this;
        },
        data: function (data) {
            _data = data;
            return this;
        },
        iframe: function (url) {
            _options['type'] = 2;
            return this.content(url)
        },
        offset: function (offset) {
            _options['offset'] = offset;
            return this;
        },
        content: function (content) {
            _options['content'] = content;
            return this;
        },
        dialog: function (buts, okCb) {
            if (buts instanceof Function || (typeof (buts) == "undefined")) {
                _options["btn"] = ["确定", "取消"]
                if ((typeof (buts) == "undefined")){
                    _options['yes'] = function (id) {
                        _layer.close(id);
                    }
                }else{
                    _options['yes'] = buts
                }

            } else {
                _options["btn"] = buts
                _options['yes'] = okCb
            }
            return this
        },
        success: function (cb) {
            _options["success"] = cb;
            return this
        },
        selector: function (selector) {
            _selector = selector
            return this
        },
        laytpl: function (cb) {
            let that = this;
            if (_options['area'][1] === false) {
                _options['area'][1] = (document.body.clientHeight - 40)+ 'px';
            }
            laytpl($(_selector).html())
                .render(_data, function (html) {
                    if (typeof (_options['content']) == "undefined") {
                        _options['content'] = html;
                    }
                    that.start(cb)
                })
        },
        submit: function (cb) {
            _options['yes'] = function () {

            }
            return this;
        },
        start: function (cb) {
            let success = _options['success'];
            _options['success'] = function () {
                success.apply(this, arguments);
                if (cb instanceof Function) {
                    cb.apply(this, arguments);
                }
            }
            let that = this;
            if (_options['area'][1] === false) {
                _options['area'][1] = (document.body.clientHeight - 40)+ 'px';
            }
            _layer.open(_options);
            this.stop();
        },
        layer : function (layerTmp) {
            _layer = layerTmp;
            return this;
        }
    };
    zz.prototype.run = zz.prototype.start;

    var lazy = function (selector, data) {
        if ("string" != typeof selector) {
            return console.error("Template not found")
        } else {
            return new zz(selector, data)
        }
    };
    lazy.var = 'v1.1'
    exports('lazy', lazy);
});

