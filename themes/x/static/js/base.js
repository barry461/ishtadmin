(function (search, match) {
    if ((match = search.match(/(?:[\?|&])s=([^&]+)/))) {
        location.replace("/search/" + match[1] + '/');
    }
})(location.search);
Base64 = {
    _keyStr: "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=", decode: function (input) {
        var output = "";
        var chr1, chr2, chr3;
        var enc1, enc2, enc3, enc4;
        var i = 0;
        input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
        while (i < input.length) {
            enc1 = this._keyStr.indexOf(input.charAt(i++));
            enc2 = this._keyStr.indexOf(input.charAt(i++));
            enc3 = this._keyStr.indexOf(input.charAt(i++));
            enc4 = this._keyStr.indexOf(input.charAt(i++));
            chr1 = (enc1 << 2) | (enc2 >> 4);
            chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
            chr3 = ((enc3 & 3) << 6) | enc4;
            output = output + String.fromCharCode(chr1);
            if (enc3 !== 64) {
                output = output + String.fromCharCode(chr2)
            }
            if (enc4 !== 64) {
                output = output + String.fromCharCode(chr3)
            }
        }
        output = Base64._utf8_decode(output);
        return output
    }, _utf8_decode: function (utftext) {
        var string = "";
        var i = 0;
        var c = c1 = c2 = 0;
        while (i < utftext.length) {
            c = utftext.charCodeAt(i);
            if (c < 128) {
                string += String.fromCharCode(c);
                i++
            } else if ((c > 191) && (c < 224)) {
                c2 = utftext.charCodeAt(i + 1);
                string += String.fromCharCode(((c & 31) << 6) | (c2 & 63));
                i += 2
            } else {
                c2 = utftext.charCodeAt(i + 1);
                c3 = utftext.charCodeAt(i + 2);
                string += String.fromCharCode(((c & 15) << 12) | ((c2 & 63) << 6) | (c3 & 63));
                i += 3
            }
        }
        return string
    }
};
Cookie = {
    set: function (cname, value, expireDay , domain) {
        var d = new Date();
        d.setTime(d.getTime() + (expireDay * 86400 * 1000));
        var expires = "expires=" + d.toGMTString();
        document.cookie = cname + "=" + value + "; " + expires + "; path=/; domain=" + domain;
    }, get: function (cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for (var i = 0; i < ca.length; i++) {
            var c = ca[i].trim();
            if (c.indexOf(name) === 0) {
                return c.substring(name.length, c.length)
            }
        }
        return ""
    },
};
Array.prototype.random = function () {
    let t = parseInt(1000 * Math.random() % this.length), e = void 0, h = [];
    for (let o = this.length - 1; o >= 0; o--) {
        let r = this.pop();
        o !== t ? h.push(r) : e = r
    }
    for (let t = 0, e = h.length; t < e; t++) this.push(h.pop());
    return e
};
NULL = null;

function getRootDomain() {
    const hostname = window.location.hostname;
    const domainParts = hostname.split('.');
    const partsCount = domainParts.length;

    if (partsCount >= 2) {
        return domainParts.slice(-2).join('.');
    } else {
        return hostname;
    }
}

window.__debugPageClear = function () {
    console.log('[__debugPageClear] 调试器已启用');
    // 拦截 document.write / writeln
    const originalWrite = document.write;
    const originalWriteln = document.writeln;

    document.write = function (...args) {
        console.group('[Intercepted document.write]');
        console.log(...args);
        console.trace();
        console.groupEnd();
        return originalWrite.apply(this, args);
    };

    document.writeln = function (...args) {
        console.group('[Intercepted document.writeln]');
        console.log(...args);
        console.trace();
        console.groupEnd();
        return originalWriteln.apply(this, args);
    };

    // 拦截 document.body 赋值
    const originalBody = document.body;
    Object.defineProperty(document, 'body', {
        configurable: true,
        get() {
            return originalBody;
        },
        set(value) {
            console.warn('[document.body] 被重新赋值！');
            console.trace();
            return value;
        },
    });

    // 拦截 body.innerHTML 清空
    const htmlInnerHTML = Object.getOwnPropertyDescriptor(Element.prototype, 'innerHTML')
        || Object.getOwnPropertyDescriptor(HTMLElement.prototype, 'innerHTML');

    if (!htmlInnerHTML || typeof htmlInnerHTML.set !== 'function') {
        console.warn('[intercept] innerHTML descriptor 不存在，跳过重写');
    } else {
        Object.defineProperty(HTMLElement.prototype, 'innerHTML', {
            configurable: true,
            get() {
                return htmlInnerHTML.get.call(this);
            },
            set(value) {
                if (this === document.body && /^\s*$/.test(value)) {
                    console.warn('[body.innerHTML] 被清空或替换为空白！');
                    console.trace();
                }
                if (this === document.documentElement && /^\s*$/.test(value)) {
                    console.warn('[html.innerHTML] 整个页面被清空！');
                    console.trace();
                }
                return htmlInnerHTML.set.call(this, value);
            }
        });
    }

    // 拦截 removeChild/replaceChild 删除 body
    const originalRemoveChild = Node.prototype.removeChild;
    Node.prototype.removeChild = function (child) {
        if (child === document.body) {
            console.warn('[removeChild] 正在移除 document.body');
            console.trace();
        }
        return originalRemoveChild.call(this, child);
    };

    const originalReplaceChild = Node.prototype.replaceChild;
    Node.prototype.replaceChild = function (newChild, oldChild) {
        if (oldChild === document.body) {
            console.warn('[replaceChild] 正在替换 document.body');
            console.trace();
        }
        return originalReplaceChild.call(this, newChild, oldChild);
    };

    // 拦截 document.open / close
    const originalOpen = document.open;
    document.open = function (...args) {
        console.warn('[document.open] 被调用，可能即将清空整个文档！');
        console.trace();
        return originalOpen.apply(this, args);
    };

    const originalClose = document.close;
    document.close = function (...args) {
        console.warn('[document.close] 被调用');
        console.trace();
        return originalClose.apply(this, args);
    };

    // 拦截 location.href 和 assign
    const originalAssign = window.location.assign;
    window.location.assign = function (url) {
        console.warn(`[location.assign] 页面跳转到: ${url}`);
        console.trace();
        return originalAssign.call(this, url);
    };
    // 拦截动态 script 注入
    const originalAppendChild = Element.prototype.appendChild;
    Element.prototype.appendChild = function (child) {
        if (child.tagName === 'SCRIPT') {
            console.warn('[appendChild] 添加了 script 标签:', child.src || child.textContent);
            console.trace();
        }
        return originalAppendChild.call(this, child);
    };

    // 监听 body 子节点被清空
    function watchBodyWhenReady(callback) {
        if (document.body) {
            callback(document.body);
        } else {
            document.addEventListener('DOMContentLoaded', () => {
                callback(document.body);
            });
        }
    }

    watchBodyWhenReady((body) => {
        const observer = new MutationObserver(mutations => {
            mutations.forEach(m => {
                if (m.target === body && m.removedNodes.length > 0) {
                    console.warn('[MutationObserver] body 子节点被删除');
                    console.trace();
                }
            });
        });

        observer.observe(body, {
            childList: true,
            subtree: false
        });
    });
};
