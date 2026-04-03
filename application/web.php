<?php

use website\Router;

Router::script('index.php', ['404' => 'Home/Error@x404'])->group(function () {
    Router::get('/robots.txt', "Seo@robots");
    Router::head('/robots.txt', "Seo@robots");
    Router::get('/index_now_{slug:[a-f0-9]+}.txt', "Seo@index_now");
    Router::head('/index_now_{slug:[a-f0-9]+}.txt', "Seo@index_now");

    // Sitemap 路由
    Router::get('/sitemap.xml', "Seo@sitemap_index");
    Router::head('/sitemap.xml', "Seo@sitemap_index");

    Router::get('/sitemap/sitemap-home.xml', "Seo@sitemap_home");
    Router::head('/sitemap/sitemap-home.xml', "Seo@sitemap_home");

    Router::get('/sitemap/sitemap-category.xml', "Seo@sitemap_category");
    Router::head('/sitemap/sitemap-category.xml', "Seo@sitemap_category");

    Router::get('/sitemap/sitemap-archives-{page:\d+}.xml', "Seo@sitemap_archives");
    Router::head('/sitemap/sitemap-archives-{page:\d+}.xml', "Seo@sitemap_archives");

    Router::any(['get', 'head'], '/', 'Home/Home@index')->name('home');

    Router::any(['get', 'head'], '/test', 'Home/Home@test');

    Router::any(['get', 'head'], '/page/{page:\d}/', 'Home/Home@index')->name('home.page');
    Router::any(['post', 'head'],'/notify/attachment_callback',"Notify@attachment_callback")->name("attachment_callback");


    //文件上传
    Router::post('/clusterutils/file', 'Clusterutils@file')->name('cluster_file');
    Router::head('/clusterutils/file', 'Clusterutils@file')->name('cluster_file');

    //缓存同步接口
    Router::post('/clusterutils/yac', 'Clusterutils@yac')->name('cluster_yac');
    Router::head('/clusterutils/yac', 'Clusterutils@yac')->name('cluster_yac');

    //配置同步接口
    Router::post('/clusterutils/config', 'Clusterutils@config')->name('cluster_config');
    Router::head('/clusterutils/config', 'Clusterutils@config')->name('cluster_config');

    Router::module('Home',function (){
        /**
         * 分类相关
         */
        Router::get('/category/{slug:\w}/' , 'Categories@category')->name('category');//分类下的文章列表
        Router::head('/category/{slug:\w}/' , 'Categories@category')->name('category');//分类下的文章列表

        Router::get('/category/{slug:\w}/{page:\d}/' , 'Categories@category')->name('category.page');//分类下文章列表分页
        Router::head('/category/{slug:\w}/{page:\d}/' , 'Categories@category')->name('category.page');//分类下文章列表分页
        /**
         * 文章相关
         */
        Router::get('/archives/{id:\d}/' , 'Archives@index')->name('detail')->name('archive');//文章详情
        Router::head('/archives/{id:\d}/' , 'Archives@index')->name('detail')->name('archive');//文章详情

        // 后台文章编辑前台预览（未保存草稿预览）
        Router::get('/preview/article' , 'Preview@index')->name('preview.article');
        Router::head('/preview/article' , 'Preview@index')->name('preview.article');

        Router::get('/archives.html' , 'Archives@history')->name('history');//往期福利
        Router::head('/archives.html' , 'Archives@history')->name('history');//往期福利

        Router::get('/archives/page/{page:\d}/' , 'Archives@history')->name('history.page');//往期福利分页
        Router::head('/archives/page/{page:\d}/' , 'Archives@history')->name('history.page');//往期福利分页

        Router::get('/danmaku/{cid:\d}.json', "Archives@danmaku")->name('post.danmaku');
        Router::head('/danmaku/{cid:\d}.json', "Archives@danmaku")->name('post.danmaku');
        Router::get('/danmaku/{cid:\d}.jsonv3', "Archives@danmaku")->name('post.danmaku');
        Router::head('/danmaku/{cid:\d}.jsonv3', "Archives@danmaku")->name('post.danmaku');
         /**
         * 评论相关
         */
        Router::get('/commentList/{cid:\d}','Comments@comment')->name('comment');//文章评论列表
        Router::head('/commentList/{cid:\d}','Comments@comment')->name('comment');//文章评论列表

        Router::get('/commentList/{cid:\d}/page/{page:\d}','Comments@comment')->name('comment.page');//评论分页
        Router::head('/commentList/{cid:\d}/page/{page:\d}','Comments@comment')->name('comment.page');//评论分页

        Router::get('/commentList/{cid:\d}/replies/{parentId:\d}','Comments@commentReplies')->name('comment.replies');//某条评论的全部回复（二级评论）
        Router::head('/commentList/{cid:\d}/replies/{parentId:\d}','Comments@commentReplies')->name('comment.replies');

        Router::get('/cmt/{cid:\d}/respond-post-{respond_cid:\d}/page/{page:\d}/{limit:\d}/','Comments@oldCommentPage')->name('old.comment.page');//旧评论分页路径
        Router::head('/cmt/{cid:\d}/respond-post-{respond_cid:\d}/page/{page:\d}/{limit:\d}/','Comments@oldCommentPage')->name('old.comment.page');//旧评论分页路径

        Router::post('/archives/{cid}/comment','Comments@create_comment')->name('comment.create'); //提交评论
         /**
         * 搜索相关
         */
        Router::get('/search/{keyword}' , 'Searchs@search')->name('search');//搜索
        Router::get('/search/{keyword}/{page:\d}/' , 'Searchs@search')->name('search.page');//搜索分页

        /**
         * 作者相关
         */
         Router::get('/author/{id:\d}/' , 'Authors@authors')->name('authors');//作者详情
         Router::head('/author/{id:\d}/' , 'Authors@authors')->name('authors');//作者详情

         Router::get('/author/{id:\d}/{page:\d}/' , 'Authors@authors')->name('author.page');//分页
         Router::head('/author/{id:\d}/{page:\d}/' , 'Authors@authors')->name('author.page');//分页

        /**
         * 标签相关
         */

        Router::get('/tag/{tag}/' , 'Tag@detail')->name('tag.detail');//标签详情
        Router::head('/tag/{tag}/' , 'Tag@detail')->name('tag.detail');//标签详情

        Router::get('/tags.html' , 'Tag@list')->name('tag.list');//所有标签列表
        Router::head('/tags.html' , 'Tag@list')->name('tag.list');//所有标签列表

        Router::get('/tags/{page:\d}/' , 'Tag@list')->name('tag.page');//标签列表分页
        Router::head('/tags/{page:\d}/' , 'Tag@list')->name('tag.page');//标签列表分页

        Router::get('/tag/{tag}/{page:\d}/' , 'Tag@detail')->name('tag_detail.page');//标签详情分页
        Router::head('/tag/{tag}/{page:\d}/' , 'Tag@detail')->name('tag_detail.page');//标签详情分页
         /**
         * 单页相关
         */
        Router::get('/{slug:\w}.html','Pages@slug')->name('slug');//单页
        Router::head('/{slug:\w}.html','Pages@slug')->name('slug');//单页

        /**
         * 其他
         */
        Router::get('/feed', "Feeds@feed")->name('feed');
        Router::head('/feed', "Feeds@feed")->name('feed');
        Router::get('/feed/archives/{cid:\d}/', "Feeds@rssComments")->name('rss_comments.feed');
        Router::head('/feed/archives/{cid:\d}/', "Feeds@rssComments")->name('rss_comments.feed');
        Router::get('/feed/rss/archives/{cid:\d}/', "Feeds@commentFeed")->name('aricve.rss_comments.feed');
        Router::head('/feed/rss/archives/{cid:\d}/', "Feeds@commentFeed")->name('aricve.rss_comments.feed');
        Router::get('/feed/atom/archives/{cid:\d}/', "Feeds@atom")->name('atom.feed');
        Router::head('/feed/atom/archives/{cid:\d}/', "Feeds@atom")->name('atom.feed');
        
        /**
         * 跳转中转页
         */
        Router::get('/Urlredirect', "Urlredirect@index")->name('Urlredirect');
        Router::head('/Urlredirect', "Urlredirect@index")->name('Urlredirect');
    });
});