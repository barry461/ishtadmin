/*!
 * Link dialog plugin for Editor.md
 *
 * @file        link-dialog.js
 * @author      pandao
 * @version     1.2.1
 * @updateTime  2015-06-09
 * {@link       https://github.com/pandao/editor.md}
 * @license     MIT
 */

(function () {

    var factory = function (exports) {

        var pluginName = "adx-dialog";

        exports.fn.adxDialog = function () {

            var _this = this;
            var cm = this.cm;
            var editor = this.editor;
            var settings = this.settings;
            var selection = cm.getSelection();
            var lang = this.lang;
            var dialogLang = lang.dialog.adx;
            var classPrefix = this.classPrefix;
            var dialogName = classPrefix + pluginName, dialog;

            cm.focus();

            if (editor.find("." + dialogName).length > 0) {
                dialog = editor.find("." + dialogName);
                dialog.find("[data-link]").val(selection);

                this.dialogShowMask(dialog);
                this.dialogLockScreen();
                dialog.show();
            } else {
                let select = '<div><select style="height: 33.5px;width: 265px" data-link>'
                for (let i in dialogLang.links) {
                    select += '<option value="' + i + '">' + dialogLang.links[i]['name'] + '-' +dialogLang.links[i]['link'] + '</option>'
                }
                select += '</select></div>'

                var dialogHTML = "<div class=\"" + classPrefix + "form\">" +
                    "<label>" + dialogLang.link + "</label>" +
                    select +
                    "</div>";

                dialog = this.createDialog({
                    title: dialogLang.title,
                    width: 380,
                    height: 211,
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
                            let link = this.find("[data-link]").val();
                            dialogLang.debug && console.log('LINK:' + link)

                            if (link <= 0) {
                                alert(dialogLang.linkEmpty);
                                return false;
                            }

                            if (dialogLang.links[link] === undefined) {
                                alert(dialogLang.linkNotValid);
                                return false;
                            }

                            dialogLang.debug && console.log('LINK ID:' + link)

                            let ele = "!ad(" + link + ")";
                            cm.replaceSelection(ele);

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
