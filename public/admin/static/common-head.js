(function() {
    // ========== 缓存版本号（修改此处即可让所有资源缓存失效） ==========
    var v = '1.0.1';
    // ================================================================

    var scripts = document.getElementsByTagName('script');
    var currentScript = scripts[scripts.length - 1];
    var src = currentScript.getAttribute('src');
    var baseDir = src.substring(0, src.lastIndexOf('/'));
    var suffix = '?v=' + v;

    var css = [
        'https://cdn.jsdelivr.net/npm/element-ui@2.15.14/lib/theme-chalk/index.min.css',
        baseDir + '/sa.css' + suffix
    ];

    var js = [
        'https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/element-ui/2.13.0/index.js',
        '/static/js/httpVueLoader.js' + suffix,
        'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.2.0/crypto-js.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/axios/1.9.0/axios.min.js',
        '/static/js/layer.js' + suffix,
        baseDir + '/sa.js' + suffix
    ];

    for (var i = 0; i < css.length; i++) {
        document.write('<link rel="stylesheet" href="' + css[i] + '">');
    }
    for (var i = 0; i < js.length; i++) {
        document.write('<script src="' + js[i] + '"><\/script>');
    }
})();
