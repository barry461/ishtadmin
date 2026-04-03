/*!
 * video link dialog plugin for Editor.md
 *
 * @file        video-link-dialog.js
 * @author      pandao
 * @version     1.2.1
 * @updateTime  2015-06-09
 * {@link       https://github.com/pandao/editor.md}
 * @license     MIT
 */

(function () {

    var factory = function (exports) {

        var pluginName = "video-linkx-dialog";

        exports.fn.videolinkxDialog = function () {

            var _this = this;
            var cm = this.cm;
            var lang = this.lang;
            var editor = this.editor;
            var settings = this.settings;
            var cursor = cm.getCursor();
            var selection = cm.getSelection();
            var dialogLang = lang.dialog.videolinkx;
            var classPrefix = this.classPrefix;
            var dialogName = classPrefix + pluginName, dialog;

            cm.focus();

            if (editor.find("." + dialogName).length < 1) {
                var dialogHTML = "<div class=\"" + classPrefix + "form\">" +
                    "<label>" + dialogLang.cover + "</label>" +
                    "<input type=\"text\" data-cover />" +
                    "<br/>" +
                    "<label>" + dialogLang.coverWidth + "</label>" +
                    "<input type=\"text\" data-cover-width />" +
                    "<br/>" +
                    "<label>" + dialogLang.coverHeight + "</label>" +
                    "<input type=\"text\" data-cover-height />" +
                    "<br/>" +
                    "<label>" + dialogLang.duration + "</label>" +
                    "<input type=\"text\" data-duration />" +
                    "<br/>" +
                    "<label>" + dialogLang.m3u8 + "</label>" +
                    "<input type=\"text\" value=\"https://video.iwanna.tv/\" data-m3u8 />" +
                    "<br/>" +
                    "<label>" + dialogLang.mp4 + "</label>" +
                    "<input type=\"text\" data-mp4 />" +
                    "<br/>" +
                    "</div>";

                dialog = this.createDialog({
                    name: dialogName,
                    title: dialogLang.title,
                    width: 380,
                    height: 388,
                    content: dialogHTML,
                    mask: settings.dialogShowMask,
                    drag: settings.dialogDraggable,
                    lockScreen: settings.dialogLockScreen,
                    maskStyle: {
                        opacity: settings.dialogMaskOpacity,
                        backgroundColor: settings.dialogMaskBgColor
                    },
                    buttons: {
                        enter: [lang.buttons.enter, function () {
                            var coverT = this.find("[data-cover]").val();
                            var coverWT = this.find("[data-cover-width]").val();
                            var coverHT = this.find("[data-cover-height]").val();
                            var durationT = this.find("[data-duration]").val();
                            var m3u8T = this.find("[data-m3u8]").val();
                            var mp4T = this.find("[data-mp4]").val();
                            console.log(coverT)

                            // m3u8 需要处理链接
                            let m3u8 = '';
                            try {
                                m3u8T = new URL(m3u8T)
                                if (!m3u8T.pathname) {
                                    alert(dialogLang.m3u8Empty);
                                    return false;
                                }
                                // 只有m3u8不能为空 其他都可以为空
                                if (m3u8T.pathname.substring(0, 2) === '//') {
                                    m3u8 = '/' + m3u8T.pathname.substring(2)
                                } else {
                                    m3u8 = m3u8T.pathname
                                }
                                if (!m3u8.endsWith('.m3u8')) {
                                    alert(dialogLang.m3u8NotFormat);
                                    return false;
                                }
                            } catch (e) {
                                alert(dialogLang.m3u8NotValid);
                                return false;
                            }

                            // 只有m3u8不能为空 其他都可以为空
                            if (m3u8 === '') {
                                alert(dialogLang.m3u8Empty);
                                return false;
                            }
                            dialogLang.debug && console.log('m3u8链接：' + m3u8)

                            let mp4 = ''
                            try {
                                mp4T = new URL(mp4T)
                            } catch (e) {
                                mp4T = undefined
                            }
                            if (mp4T) {
                                if (!mp4T.pathname) {
                                    alert(dialogLang.mp4Empty);
                                    return false;
                                }
                                if (mp4T.pathname.substring(0, 2) === '//') {
                                    mp4 = '/' + mp4T.pathname.substring(2)
                                } else {
                                    mp4 = mp4T.pathname
                                }
                                // 后缀格式必须为mp4
                                if (!mp4.endsWith('.mp4')) {
                                    alert(dialogLang.mp4NotFormat);
                                    return false;
                                }
                                // 只有m3u8不能为空 其他都可以为空
                                if (mp4 === '') {
                                    alert(dialogLang.mp4Empty);
                                    return false;
                                }
                            }
                            dialogLang.debug && console.log('mp4链接：' + mp4)

                            let cover = ''
                            try {
                                coverT = new URL(coverT)
                            } catch (e) {
                                coverT = undefined
                            }
                            if (coverT) {
                                if (coverT.pathname.substring(0, 2) === '//') {
                                    cover = '/' + coverT.pathname.substring(2)
                                } else {
                                    cover = coverT.pathname
                                }
                                // 只有m3u8不能为空 其他都可以为空
                                if (cover === '') {
                                    alert(dialogLang.coverEmpty);
                                    return false;
                                }
                                // 后缀格式必须为mp4
                                if (!cover.endsWith('.jpg') && !cover.endsWith('.png') && !cover.endsWith('.jpeg') && !cover.endsWith('.gif') && !cover.endsWith('.png')) {
                                    alert(dialogLang.coverNotFormat);
                                    return false;
                                }
                            }
                            dialogLang.debug && console.log('封面链接：' + cover)

                            // 判断宽度
                            let coverWidth = 0;
                            if (coverWT !== '') {
                                coverWidth = parseInt(coverWT);
                                if (coverWidth === 0) {
                                    alert(dialogLang.coverWidthNotValid);
                                    return false;
                                }
                            }
                            dialogLang.debug && console.log('封面宽度：' + coverWidth)

                            // 判断高度
                            let coverHeight = 0;
                            if (coverHT !== '') {
                                coverHeight = parseInt(coverHT);
                                if (coverHeight === 0) {
                                    alert(dialogLang.coverHeightNotValid);
                                    return false;
                                }
                            }
                            dialogLang.debug && console.log('封面高度：' + coverHeight)

                            // 判断时长
                            let duration = 0;
                            if (durationT !== '') {
                                duration = parseInt(durationT);
                                if (duration === 0) {
                                    alert(dialogLang.durationNotValid);
                                    return false;
                                }
                            }
                            dialogLang.debug && console.log('封面高度：' + duration)

                            // let json = JSON.stringify({
                            //     'id': 0,
                            //     'w': coverWidth,
                            //     'h': coverHeight,
                            //     'd': duration,
                            //     'c': cover ? '{{img-cdn}}' + cover : '',
                            //     'mp4': mp4 ? '{{mp4-cdn}}' + mp4 : '',
                            //     'm3u8': m3u8,
                            // })
                            // dialogLang.debug && console.log('视频数据：' + json)

                            // let ele = '!player(' + json + ')' + "\n"

                            cover = cover ? '{{img-cdn}}' + cover : '';
                            mp4 = mp4 ? '{{mp4-cdn}}' + mp4 : '';
                            m3u8 = m3u8 ? '{{m3u8-cdn}}' + m3u8 : mp4;
                            let ele = '[dplayer url="' + m3u8 + '" pic="' + cover + '" /]';

                            cm.replaceSelection(ele);

                            if (selection === "") {
                                cm.setCursor(cursor.line, cursor.ch + 1);
                            }

                            this.hide().lockScreen(false).hideMask();

                            return false;
                        }],
                        cancel: [lang.buttons.cancel, function () {
                            this.hide().lockScreen(false).hideMask();

                            return false;
                        }]
                    }
                });
            }

            dialog = editor.find("." + dialogName);
            dialog.find("[data-cover]").val('');
            dialog.find("[data-cover-width]").val('');
            dialog.find("[data-cover-height]").val('');
            dialog.find("[data-duration]").val('');
            dialog.find("[data-m3u8]").val('https://video.iwanna.tv/');
            dialog.find("[data-mp4]").val('');

            this.dialogShowMask(dialog);
            this.dialogLockScreen();
            dialog.show();
        };

    };

    // CommonJS/Node.js
    if (typeof require === "function" && typeof exports === "object" && typeof module === "object") {
        module.exports = factory;
    } else if (typeof define === "function")  // AMD/CMD/Sea.js
    {
        if (define.amd) { // for Require.js

            define(["editormd"], function (editormd) {
                factory(editormd);
            });

        } else { // for Sea.js
            define(function (require) {
                var editormd = require("./../../editormd");
                factory(editormd);
            });
        }
    } else {
        factory(window.editormd);
    }

})();
