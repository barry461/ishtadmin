function layerOpenSelector(selector, area, option) {
    layer.open($.extend({
        "type": 1, //弹出层为页面层
        "content": $(selector).html(),//弹出层的页面层 html内容
        "area": area || ["700px", "700px"], //弹出成层区域多大
        "title": false, //标题
        "closeBtn": false,//显示关闭按钮
        "shadeClose": true,//点击遮罩层关闭
        "anim": 3, //动画类型
        "offset": 'rt',//打开位置 rt = 右上
        "full": false//是否全屏
    }, option || {}));
}


function layerOpenHtml(html, area, option) {

    if (area instanceof Object && !(area instanceof Array )){
        option = area
        area = undefined
    }

    layer.open($.extend({
        "type": 1, //弹出层为页面层
        "content": html,//弹出层的页面层 html内容
        "area": area || ["700px", "700px"], //弹出成层区域多大
        "title": false, //标题
        "closeBtn": false,//显示关闭按钮
        "shadeClose": true,//点击遮罩层关闭
        "anim": 3, //动画类型
        "offset": 'rt',//打开位置 rt = 右上
        "full": false//是否全屏
    }, option || {}));
}


function layerOpenIframeDialog(url) {
    var options = {
        "type": 2,
        "title": false,
        "closeBtn": false,
        "shadeClose": true,
        "content": url,
        "anim": 3,
        "offset": 'rt',
        "full": false
    }
    layer.open(options);
}