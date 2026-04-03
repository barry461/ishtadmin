<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/css/layui.css">
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <meta charset="utf-8">
    <title>添加文章</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/css/layui.css">
    <link rel="stylesheet" href="/static/editor/css/editormd.css"/>
    <script src="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js/dist/hls.min.js"></script>
    <link rel="shortcut icon" href="https://pandao.github.io/editor.md/favicon.ico" type="image/x-icon"/>
    <script src="/static/editor/js/jquery.min.js"></script>
    <script src="/static/editor/js/jquery.md5.js"></script>
    <script src="/static/editor/editormd.js?v=1"></script>
    <script src="/static/editor/swiper-bundle.min.js"></script>
    <script src="/static/js/plugins/layer/layer.min.js"></script>
    <script src="/static/backend/util.js"></script>
    <link rel="stylesheet" href="/static/editor/swiper-bundle.min.css">

    <style>
        .main-layout {
            margin-top: 20px;
        }

        .left-panel {
            padding-right: 20px;
        }

        .right-panel .layui-card {
            margin-bottom: 20px;
        }

        .editor-area {
            height: 300px;
        }

        .tag-list {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .tag-item {
            display: inline-flex;
            align-items: center;
            background: #e6e6e6;
            padding: 2px 8px;
            border-radius: 3px;
            margin-right: 5px;
            margin-bottom: 5px;
        }

        .tag-item .close {
            margin-left: 5px;
            cursor: pointer;
            color: #666;
        }

        .tag-item .close:hover {
            color: #FF5722;
        }

        .layui-tab-brief > .layui-tab-title {
            background-color: #f8f8f8;
            border-bottom: 1px solid #f6f6f6;
        }

        .layui-tab-brief > .layui-tab-title .layui-this {
            color: #009688;
        }

        .layui-tab-content {
            padding: 15px;
        }


        .attachment-list {
            max-height: 500px;
            overflow-y: auto;
            margin-top: 15px;
        }

        .attachment-list::-webkit-scrollbar {
            width: 4px;
        }

        .attachment-list::-webkit-scrollbar-thumb {
            background: #ccc;
            border-radius: 2px;
        }


        .right-panel .layui-card {
            margin-bottom: 15px;
        }

        .right-panel .layui-card-body {
            padding: 15px;
        }


        .layui-tab-brief > .layui-tab-title {
            border-bottom: none;
        }

        .layui-tab-brief > .layui-tab-title .layui-this {
            color: #009688;
        }

        .layui-tab-brief > .layui-tab-title .layui-this:after {
            border-bottom: 2px solid #009688;
        }


        .attachment-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .attachment-list img {
            border-radius: 2px;
        }

        .attachment-list .layui-table {
            margin: 0;
        }

        .attachment-list .layui-table td {
            padding: 5px 10px;
        }

        .cover-upload-area {
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .upload-box {
            flex: 0 0 200px;
        }

        .preview-box {
            flex: 1;
            min-height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f8f8;
            border: 1px dashed #ddd;
            border-radius: 4px;
            position: relative;
        }

        .preview-box img {
            max-width: 100%;
            max-height: 200px;
            object-fit: contain;
        }

        .layui-upload-drag {
            width: 200px !important;
            height: 200px !important;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .preview-box .remove-cover {
            position: absolute;
            top: 5px;
            right: 5px;
            background: rgba(0, 0, 0, 0.5);
            color: #fff;
            width: 24px;
            height: 24px;
            line-height: 24px;
            text-align: center;
            border-radius: 50%;
            cursor: pointer;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .preview-box:hover .remove-cover {
            opacity: 1;
        }
    </style>
</head>

<body>

<div class="layui-container main-layout">
    <form class="layui-form" lay-filter="postForm">

        <div class="layui-row">

            <div class="layui-col-md9 left-panel">


                <div class="layui-form-item" style="position: relative;">
                    <input type="text" name="title" value="{%$post->title%}" required lay-verify="required"
                           placeholder="请输入标题" autocomplete="off" class="layui-input layui-bg-gray"
                            {%if $post->ads_field==1%}
                           readonly
                            {%/if%}
                           style="font-size: 18px;">
                    <input type="hidden" name="cid" value="{%$post->cid%}">
                    <!-- 自动保存状态显示 -->
                    <div id="auto-save-status"
                         style="position: absolute; top: 50%; right: 10px; transform: translateY(-50%); background: rgba(0,0,0,0.7); color: white; padding: 6px 10px; border-radius: 3px; font-size: 11px; z-index: 9999; display: none;">
                        <i class="layui-icon layui-icon-ok"></i> <span id="auto-save-text">已保存</span>
                    </div>
                </div>

                <!-- 文章链接显示 -->
                <div class="layui-form-item" id="article-link-display">
                    <label class="layui-form-label" style="width: 80px; color: #666;">文章链接</label>
                    <div class="layui-input-block">
                        <div class="layui-input"
                             style="background-color: #f8f8f8; color: #999; cursor: pointer; border: 1px solid #e6e6e6; position: relative;">
                            <span id="article-link-text" onclick="copyArticleLink()" title="点击复制链接">{%trim(options('siteUrl'),"/")%}/archives/{%$post->cid%}</span>
                            <i class="layui-icon layui-icon-file" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); color: #1E9FFF;" onclick="copyArticleLink()"></i>
{%*                            <span id="article-id-placeholder">{%$post->cid%}</span>*%}
                        </div>
                    </div>
                </div>

                <!-- 将原有的封面上传部分替换为以下内容 -->
                <div class="layui-form-item">
                    <div class="cover-upload-area">
                        <div class="upload-box">
                            <div class="layui-upload-drag" id="coverUpload">
                                <i class="layui-icon layui-icon-upload"></i>
                                <p>点击上传封面图</p>
                                <p>或将图片拖拽到此处</p>
                            </div>
                        </div>
                        <div class="layui-upload-drag" id="coverPreview">
                            <img src="{%$post->fieldValue('banner')%}" alt="封面预览">
                            <div class="remove-cover" onclick="removeCover()">
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="cover_image" id="coverImageInput">
                </div>
                <!-- 正文 -->
                <div class="layui-form-item layui-form-text">
                    <div id="test-editormd">
                        <textarea style="display:none;" name="content" id="editor-content">{%$post_txt%}</textarea>
                    </div>
                </div>


                <div class="layui-card">
                    <div class="layui-card-header" style="cursor: pointer;" id="custom-fields-toggle">
                        自定义字段
                        <i class="layui-icon layui-icon-right" style="float: right;"></i>
                    </div>
                    <div class="layui-card-body" id="custom-fields-content" style="display: none;">
                        {%foreach $post->fields as $field%}
                            <div id="custom-fields"></div>
                            <div class="layui-row layui-col-space10">
                                <div class="layui-col-md5">
                                    <input type="text" name="{%$field.name%}" value="{%$field.name%}"
                                            {%if $post->ads_field==1%}
                                            readonly
                                            {%/if%}
                                           placeholder="跳转到哪里" class="layui-input">
                                </div>
                                <div class="layui-col-md7">
                                    <input type="text" name="{%$field.str_value%}" id="{%$field.name%}"
                                            {%if $post->ads_field==1%}
                                           readonly
                                            {%/if%}
                                           value="{%$field.str_value%}" placeholder="字段值" class="layui-input">
                                </div>
                            </div>
                        {%/foreach%}

                        {%if $post->ads_field==1%}
                        <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="add-field">
                            <i class="layui-icon">&#xe654;</i> 添加字段
                        </button>
                        {%/if%}
                    </div>
                </div>
            </div>
            <div class="layui-col-md3 right-panel">
                <div class="layui-card">
                    <div class="layui-tab layui-tab-brief" lay-filter="settings-tab">
                        <ul class="layui-tab-title">
                            <li class="layui-this">选项</li>
                            <!-- <li>附件</li> -->
                        </ul>
                        <div class="layui-tab-content">

                            <div class="layui-tab-item layui-show">

                                <div class="layui-card">
                                    <div class="layui-card-header">文章类型与作者</div>
                                    <div class="layui-card-body">
                                        <select name="post_type" lay-filter="postType">
                                            <option value="post" {%if $post->type == 'post' %} selected {%/if%}>文章
                                            </option>
                                            <option value="page" {%if $post->type == 'page' %} selected {%/if%}>单页
                                            </option>
                                        </select>
                                        <select name="author">
                                            {%foreach $authorlist as $author%}
                                                <option value="{%$author['uid']%}" {%if $post->authorId ==
                                                $author['uid'] %} selected {%/if%}
                                                >{%$author['screenName']%}</option>
                                            {%/foreach%}
                                        </select>
                                        <div id="pageUrlConfig" style="display: none; margin-top: 10px;">
                                            <div class="layui-form-item">
                                                <input type="text" name="page_base_url" class="layui-input" readonly
                                                       style="margin-bottom: 5px;" value="">
                                                <input type="text" name="page_slug" class="layui-input"
                                                       placeholder="自定义链接后缀" value="{%$post->slug%}">
                                            </div>
                                        </div>
                                    </div>
                                </div>


                                <div class="layui-card">
                                    <div class="layui-card-header">分类</div>
                                    <div class="layui-card-body">
                                        {%foreach $categoryList as $category%}
                                            <input type="checkbox" name="category[]" title="{%$category.name%}"
                                                   value="{%$category.id%}" {%foreach $post->categories as $postCategory%}
                                                {%if $postCategory.id == $category.id%}
                                                    checked
                                                {%/if%}
                                            {%/foreach%}
                                            >
                                        {%/foreach%}
                                    </div>
                                </div>


                                <div class="layui-card">
                                    <div class="layui-card-header">发布日期</div>
                                    <div class="layui-card-body">
                                        <input type="text" name="publish_date" id="publish_date" class="layui-input"
                                               readonly>
                                    </div>
                                </div>
                                <div class="layui-card">
                                    <div class="layui-card-header">标签</div>
                                    <div class="layui-card-body">
                                        <div class="tag-input-container">
                                            <div class="tag-list" style="margin-bottom: 10px;">
                                                {%foreach $post->tags as $tag%}
                                                    <span class="tag-item">{%$tag.name%}<span
                                                                class="close">×</span></span>
                                                {%/foreach%}
                                            </div>
                                            <div style="display: flex; gap: 10px; align-items: center;">
                                                <input type="text" id="tagInput" placeholder="输入标签后按回车"
                                                       class="layui-input" style="flex: 1;">
                                                <button type="button" id="clearAllTags" class="layui-btn layui-btn-sm layui-btn-danger">一键清空</button>
                                            </div>
                                            <input type="hidden" name="tags" id="tagsValue">
                                        </div>
                                    </div>
                                </div>


                                <div class="layui-card">
                                    <div class="layui-card-header">状态</div>
                                    <div class="layui-card-body">
                                        <select name="status" lay-verify="required" {%if $post->ads_field==1%}
                                                disabled
                                                {%/if%}>
                                            {%foreach \ContentsModel::STATUS as $key => $value%}
                                                <option value="{%$key%}"
                                                        {%if isset($post) && $post->status == $key%}selected{%/if%}>
                                                    {%$value%}
                                                </option>
                                            {%/foreach%}
                                        </select>
                                    </div>
                                </div>
                                <div class="layui-card">
                                    <div class="layui-card-header">高级设置</div>
                                    <div class="layui-card-body">
                                        <input type="checkbox" name="allowComment" title="允许评论" value="1" {%if
                                                $post.allowComment%} checked {%/if%}>
                                        <input type="checkbox" name="allowPing" title="允许被引用" value="1" {%if
                                                $post.allowPing%} checked {%/if%}>
                                        <input type="checkbox" name="allowFeed" title="允许在聚合中出现" value="1" {%if
                                                $post.allowFeed%} checked {%/if%}>
                                        <input type="checkbox" name="hotSearch" title="是否热搜" value="1" {%if
                                                $post.hotSearch%} checked {%/if%}>
                                    </div>
                                </div>
                            </div>


                            <!-- <div class="layui-tab-item">
                                <div class="layui-card">
                                    <div class="layui-card-header">上传附件</div>
                                    <div class="layui-card-body">



                                        <div class="layui-form-item">
                                            <button type="button" class="layui-btn" id="uploadImage">
                                                <i class="layui-icon">&#xe64a;</i>上传图片
                                            </button>

                                        </div>

                                        <div class="attachment-list">
                                            <table class="layui-table" lay-skin="line">
                                                <colgroup>
                                                    <col width="60">
                                                    <col>
                                                    <col width="80">
                                                </colgroup>
                                                <thead>
                                                    <tr>
                                                        <th>预览</th>
                                                        <th>大小</th>
                                                        <th>操作</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="attachmentList"></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
                <div class="layui-card">
                    <div class="layui-card-body" style="padding: 15px;">
                        <div class="layui-btn-container" style="display: flex; flex-direction: column; gap: 10px;">
                            <button class="layui-btn layui-btn-normal layui-btn-fluid" lay-submit
                                    lay-filter="submitPost">确定
                            </button>
                            <button type="reset" class="layui-btn layui-btn-primary layui-btn-fluid">重置</button>
                            <button class="layui-btn layui-btn-warm layui-btn-fluid"
                                    onclick="close_iframe(false)">关闭
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


</div>
</div>

</form>
</div>


<script src="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/layui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
    // 自动保存相关变量（全局）
    let autoSaveTimer = null;
    let lastSavedContent = '';
    let lastSavedTitle = '';
    let isAutoSaving = false;
    let currentCid = "{%$post->cid%}";

    layui.use(['form', 'jquery', 'laydate', 'upload', 'element'], function () {
        const $ = layui.$;
        const form = layui.form;
        const upload = layui.upload;
        const element = layui.element;

        // 自动保存函数
        function autoSave() {
            if (isAutoSaving) return;

            const title = $('input[name="title"]').val().trim();
            const content = testEditor ? testEditor.getMarkdown() : '';

            // 如果标题和内容都为空，不进行自动保存
            if (!title && !content) return;

            // 如果内容没有变化，不进行自动保存
            if (title === lastSavedTitle && content === lastSavedContent) return;

            isAutoSaving = true;

            // 收集表单数据
            const customFields = [];
            $('.field-item').each(function () {
                const name = $(this).find('input[name="fields[name][]"]').val();
                const value = $(this).find('input[name="fields[value][]"]').val();
                if (name) {
                    customFields.push({name, value});
                }
            });

            $('#custom-fields-content .layui-row').each(function () {
                const name = $(this).find('input[type="text"]:first').val();
                const value = $(this).find('input[type="text"]:last').val();
                if (name) {
                    customFields.push({name, value});
                }
            });

            const categories = [];
            $('input[name="category[]"]:checked').each(function () {
                categories.push($(this).val());
            });

            const tags = [];
            $('.tag-list .tag-item').each(function () {
                const tagText = $(this).clone()
                    .children()
                    .remove()
                    .end()
                    .text()
                    .trim();
                if (tagText) {
                    tags.push(tagText);
                }
            });

            const allowComment = $('input[name="allowComment"]:checked').val();
            const allowPing = $('input[name="allowPing"]:checked').val();
            const allowFeed = $('input[name="allowFeed"]:checked').val();
            const hotSearch = $('input[name="hotSearch"]:checked').val();
            console.log(hotSearch)
            // 将热搜状态添加到自定义字段中
            if (hotSearch) {
                customFields.push({name: 'hotSearch', value: '1'});
            } else {
                customFields.push({name: 'hotSearch', value: '0'});
            }

            const submitData = {
                title: title,
                cid: currentCid,
                content: content,
                custom_fields: customFields,
                post_type: $('select[name="post_type"]').val(),
                author: $('select[name="author"]').val(),
                page_slug: $('input[name="page_slug"]').val() || '',
                page_url: $('input[name="page_base_url"]').val() || '',
                categories: categories,
                publish_date: $('input[name="publish_date"]').val(),
                tags: tags.join(','),
                status: $('select[name="status"]').val(),
                cover_image: $('input[name="cover_image"]').val(),
                allowComment: allowComment,
                allowPing: allowPing,
                allowFeed: allowFeed
            };

            // 发送自动保存请求
            $.ajax({
                url: "{%url('contents/auto_save')%}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(submitData),
                success: function (res) {
                    if (res.code === 0) {
                        // 更新保存状态
                        lastSavedTitle = title;
                        lastSavedContent = content;

                        // 自动保存返回cid时，更新当前cid
                        if (res.data && res.data.cid) {
                            currentCid = res.data.cid;
                            $('input[name="cid"]').val(currentCid);
                        }

                        // 显示自动保存成功提示
                        showAutoSaveStatus('自动保存成功 - ' + (res.data.saved_at || new Date().toLocaleTimeString()), 'success');
                    } else {
                        showAutoSaveStatus('自动保存失败：' + res.msg, 'error');
                    }
                },
                error: function () {
                    showAutoSaveStatus('自动保存失败：网络错误', 'error');
                },
                complete: function () {
                    isAutoSaving = false;
                }
            });
        }

        // 显示自动保存状态
        function showAutoSaveStatus(message, type) {
            const $status = $('#auto-save-status');
            const $text = $('#auto-save-text');
            const $icon = $status.find('.layui-icon');

            // 简化显示文本
            if (type === 'success') {
                $text.text('已保存');
                $icon.removeClass('layui-icon-close').addClass('layui-icon-ok');
                $status.css('background', 'rgba(0,150,0,0.8)');
            } else {
                $text.text('保存失败');
                $icon.removeClass('layui-icon-ok').addClass('layui-icon-close');
                $status.css('background', 'rgba(255,0,0,0.8)');
            }

            $status.fadeIn();

            // 2秒后自动隐藏
            setTimeout(function () {
                $status.fadeOut();
            }, 2000);
        }

        // 设置自动保存定时器（全局）
        // 设置自动保存定时器
        function setupAutoSave() {
            // 清除之前的定时器
            if (autoSaveTimer) {
                clearInterval(autoSaveTimer);
            }

            // 每30秒自动保存一次
            autoSaveTimer = setInterval(autoSave, 30000);
        }

        // 监听内容变化
        function setupContentChangeListener() {
            // 监听标题变化
            $('input[name="title"]').on('input', function () {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, 2000); // 标题变化后2秒自动保存
            });

            // 监听编辑器内容变化
            if (testEditor) {
                testEditor.cm.on('change', function () {
                    clearTimeout(autoSaveTimer);
                    autoSaveTimer = setTimeout(autoSave, 2000); // 内容变化后2秒自动保存
                });
            }

            // 监听其他表单元素变化
            $('select, input[type="checkbox"], input[type="text"]').on('change', function () {
                clearTimeout(autoSaveTimer);
                autoSaveTimer = setTimeout(autoSave, 3000); // 其他变化后3秒自动保存
            });
        }

        $('#custom-fields-toggle').on('click', function () {
            const content = $('#custom-fields-content');
            const icon = $(this).find('.layui-icon');
            const upload = layui.upload;
            if (content.is(':visible')) {
                content.slideUp();
                icon.removeClass('layui-icon-down').addClass('layui-icon-right');
            } else {
                content.slideDown();
                icon.removeClass('layui-icon-right').addClass('layui-icon-down');
            }
        });

        const existingBanner = '{%$post->fieldValue("banner")%}';
        if (existingBanner) {
            $('#coverPreview').removeClass('layui-hide');
            $('#coverPreview').find('img').attr('src', existingBanner);
            $('#coverImageInput').val(existingBanner);
        }

        //附件监听选项卡切换
        element.on('tab(settings-tab)', function (data) {
            console.log('当前Tab索引：' + data.index);
        });


        upload.render({
            elem: '#coverUpload',
            url: "{%url('upload/upload')%}",
            accept: 'image',
            acceptMime: 'image/*',
            before: function (obj) {

                obj.preview(function (index, file, result) {
                    $('#coverPreview').removeClass('layui-hide')
                        .find('img').attr('src', result);
                });
            },
            done: function (res) {
                if (res.code === 200) {
                    $('#coverPreview').removeClass('layui-hide')
                        .find('img').attr('src', res.data.src);
                    $('#coverImageInput').val(res.data.url);
                    $("#banner").val(res.data.url);
                    layer.msg('上传成功');
                } else {
                    $('#coverPreview').addClass('layui-hide');
                    layer.msg('上传失败：' + res.msg);
                }
            },
            error: function () {
                $('#coverPreview').addClass('layui-hide');
                layer.msg('上传失败，请重试');
            }
        });


        $('#coverPreview').on('click', function (e) {
            if (e.target.tagName === 'IMG') {
                layer.confirm('是否移除封面图？', function (index) {
                    $('#coverPreview').addClass('layui-hide').find('img').attr('src', '');
                    $('#coverImageInput').val('');
                    layer.close(index);
                });
            }
        });

        $('#add-field').on('click', function () {
            const newField = `
                    <div class="field-item" style="position: relative; margin-bottom: 15px;">
                    <button type="button" class="layui-btn layui-btn-xs layui-btn-danger remove-field"
                        style="position: absolute; top: -8px; right: -8px; z-index: 10;">×</button>
            
                    <div class="layui-row layui-col-space10">
                        <div class="layui-col-md5">
                        <input type="text" name="fields[name][]" placeholder="字段名" class="layui-input">
                        </div>
                        <div class="layui-col-md7">
                        <input type="text" name="fields[value][]" placeholder="字段值" class="layui-input">
                        </div>
                    </div>
                    </div>
                `;
            $('#custom-fields').append(newField);
        });


        const baseUrl = window.location.origin + '/';
        //页面初始化时执行一次
        const selectedValue = "{%$post->type%}"; //获取当前 select 的值
        handlePostTypeChange(selectedValue);


        form.on('select(postType)', function (data) {
            handlePostTypeChange(data.value);
        });

        $('input[name="page_slug"]').on('input', updatePageBaseUrl);
        updatePageBaseUrl();
        const laydate = layui.laydate;

        laydate.render({
            elem: '#publish_date',
            type: 'datetime',
            value: "{%$post->created%}"
        });


        const tagInput = $('#tagInput');
        const tagList = $('.tag-list');
        const tagsValue = $('#tagsValue');
        let tags = [];

        // 初始化：将服务器渲染的标签同步到 tags 数组
        tagList.find('.tag-item').each(function() {
            const tagText = $(this).clone()
                .children()
                .remove()
                .end()
                .text()
                .trim();
            if (tagText && !tags.includes(tagText)) {
                tags.push(tagText);
            }
        });
        updateTagsValue();

        function updateTagsValue() {
            tagsValue.val(tags.join(','));
        }

        function addTag(value) {
            // 支持用 # 和 , 批量添加：如 "#美女#吃瓜" 或 "美女,吃瓜"
            const tagPattern = /^[\u4e00-\u9fa5a-zA-Z0-9\-]+$/;
            const normalized = value.replace(/，/g, ','); // 统一中文逗号
            const candidates = (normalized || '').split(/[#,\s]+/).map(v => v.trim()).filter(Boolean);
            if (!candidates.length) {
                tagInput.val('');
                return;
            }
            for (const candidate of candidates) {
                if (!tagPattern.test(candidate)) {
                    layer.msg('标签只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格', {icon: 2});
                    tagInput.val('');
                    return;
                }
            }
            candidates.forEach((candidate) => {
                if (!tags.includes(candidate)) {
                    tags.push(candidate);
                    const tagElement = $(`
          <span class="tag-item">
            ${candidate}
            <span class="close">×</span>
          </span>
        `);
                    tagList.append(tagElement);
                }
            });
            updateTagsValue();
            tagInput.val('');
        }

        function updatePageBaseUrl() {

            const slugInput = $('input[name="page_slug"]');
            const slug = slugInput.val().trim();
            $('input[name="page_base_url"]').val("{%options('siteUrl')%}/" + (slug || ''));
        }


        function handlePostTypeChange(value) {

            const pageUrlConfig = $('#pageUrlConfig');
            console.log(value);
            if (value === 'page') {
                pageUrlConfig.slideDown();
                pageUrlConfig.closest('.layui-card').next('.layui-card').hide();
            } else {
                pageUrlConfig.slideUp();
                pageUrlConfig.closest('.layui-card').next('.layui-card').show();
            }
        }

        //添加到附件列表
        function addToAttachmentList(file) {
            const html = `
            <tr>
                <td>
                    <img src="${file.type === 'video' ? '/static/images/video.png' : file.src}" 
                        style="width:40px;height:40px;object-fit:cover;">
                </td>
                <td>${file.height} * ${file.width}</td>
                <td>
                    <button type="button" class="layui-btn layui-btn-xs" 
                            onclick="insertToEditor('${file.type}', '${file.url}')">
                        插入
                    </button>
                </td>
            </tr>
        `;
            $('#attachmentList').prepend(html);
        }


        tagList.on('click', '.close', function () {
            const tag = $(this).parent().text().slice(0, -1);
            tags = tags.filter(t => t !== tag);
            $(this).parent().remove();
            updateTagsValue();
        });

        tagInput.on('keydown', function (e) {
            if (e.keyCode === 13) {
                e.preventDefault();
                addTag($(this).val());
            }
        });

        tagInput.on('blur', function () {
            if ($(this).val().trim()) {
                addTag($(this).val());
            }
        });

        // 一键删除所有标签
        $('#clearAllTags').on('click', function () {
            const currentTagCount = tagList.find('.tag-item').length;
            if (tags.length === 0 && currentTagCount === 0) {
                layer.msg('当前没有标签', {icon: 0});
                return;
            }
            layer.confirm('确定要删除所有标签吗？', {icon: 3, title: '提示'}, function (index) {
                // 清空 tags 数组
                tags = [];
                // 清空 DOM 中的所有标签
                tagList.empty();
                // 更新隐藏字段为空字符串
                tagsValue.val('');
                layer.close(index);
                layer.msg('已清空所有标签', {icon: 1});
            });
        });


        $(document).on('click', '.remove-field', function () {
            $(this).closest('.field-item').remove();
        });


        form.on('submit(submitPost)', function (data) {

            const content = testEditor.getMarkdown();


            const customFields = [];
            $('.field-item').each(function () {
                const name = $(this).find('input[name="fields[name][]"]').val();
                const value = $(this).find('input[name="fields[value][]"]').val();
                if (name) {
                    customFields.push({name, value});
                }
            });

            $('#custom-fields-content .layui-row').each(function () {
                const name = $(this).find('input[type="text"]:first').val();
                const value = $(this).find('input[type="text"]:last').val();
                if (name) {
                    customFields.push({name, value});
                }
            });


            const categories = [];
            $('input[name="category[]"]:checked').each(function () {
                categories.push($(this).val());
            });


            const tags = [];
            $('.tag-list .tag-item').each(function () {
                const tagText = $(this).clone()
                    .children()
                    .remove()
                    .end()
                    .text()
                    .trim();

                if (tagText) {
                    tags.push(tagText);
                }
            });

            const allowComment = $('input[name="allowComment"]:checked').val();
            const allowPing = $('input[name="allowPing"]:checked').val();
            const allowFeed = $('input[name="allowFeed"]:checked').val();
            const hotSearch = $('input[name="hotSearch"]:checked').val();

            // 将热搜状态添加到自定义字段中
            if (hotSearch) {
                customFields.push({name: 'hotSearch', value: '1'});
            } else {
                customFields.push({name: 'hotSearch', value: '0'});
            }

            const submitData = {
                title: data.field.title,
                cid: currentCid || data.field.cid,
                content: content,
                custom_fields: customFields,
                post_type: data.field.post_type,
                author: data.field.author,
                page_slug: data.field.page_slug || '',
                page_url: data.field.page_base_url || '',
                categories: categories,
                publish_date: data.field.publish_date,
                tags: tags.join(','),
                status: data.field.status,
                cover_image: data.field.cover_image,
                allowComment: allowComment,
                allowPing: allowPing,
                allowFeed: allowFeed
            };

            //表单提交
            $.ajax({
                url: "{%url('contents/txt_save')%}",
                type: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(submitData),
                success: function (res) {
                    if (res.code === 0) {
                        layer.msg('保存成功', {
                            icon: 1,
                            time: 1000
                        }, function () {
                            //成功后关闭页面并刷新父页面
                            close_iframe(true);
                        });
                    } else {
                        layer.msg('保存失败：' + res.msg, {
                            icon: 2,
                            time: 2000
                        });
                    }
                },
                error: function () {
                    layer.msg('网络错误，请重试', {
                        icon: 2,
                        time: 2000
                    });
                }
            });

            // 阻止表单默认提交
            return false;
        });


        form.verify({
            title: function (value) {
                if (!value) {
                    return '标题不能为空';
                }
                if (value.length > 100) {
                    return '标题不能超过100个字符';
                }
            },
            content: function () {
                if (!testEditor.getMarkdown()) {
                    return '正文内容不能为空';
                }
            }
        });


        function initFormData(postData) {
            if (postData) {
                form.val('postForm', {
                    title: postData.title,
                    post_type: postData.post_type,
                    status: postData.status,
                    page_slug: postData.page_slug,
                    publish_date: postData.publish_date
                });


                testEditor.setMarkdown(postData.content);


                if (postData.tags) {
                    postData.tags.split(',').forEach(tag => {
                        if (tag) addTag(tag);
                    });
                }


                if (postData.categories) {
                    postData.categories.forEach(cid => {
                        $(`input[name="category[]"][value="${cid}"]`).prop('checked', true);
                    });
                    form.render('checkbox');
                }
            }
        }

        // 初始化自动保存功能
        setTimeout(function () {
            setupAutoSave();
            setupContentChangeListener();

            // 初始化已保存的内容状态
            lastSavedTitle = $('input[name="title"]').val().trim();
            lastSavedContent = testEditor ? testEditor.getMarkdown() : '';

            // 页面离开前提醒保存
            window.addEventListener('beforeunload', function (e) {
                const title = $('input[name="title"]').val().trim();
                const content = testEditor ? testEditor.getMarkdown() : '';

                // 如果有内容但未保存，显示提醒
                if ((title || content) && (title !== lastSavedTitle || content !== lastSavedContent)) {
                    e.preventDefault();
                    e.returnValue = '您有未保存的内容，确定要离开吗？';
                    return '您有未保存的内容，确定要离开吗？';
                }
            });
        }, 1000);

    });

    function afterChangeInit() {
        $(".dplayer").each(function () {
            const $player = $(this);

            if (!$player.data('initialized')) {
                try {
                    const config = JSON.parse($player.attr("config"));
                    config.container = this;

                    const player = new DPlayer(config);

                    $player.data('initialized', true);


                    player.on('play', () => {

                        $(".dplayer").each(function () {
                            const otherId = $(this).attr('id');
                            if (otherId !== $player.attr('id')) {
                                const otherPlayer = this.dpPlayer;
                                if (otherPlayer && !otherPlayer.paused) {
                                    otherPlayer.pause();
                                }
                            }
                        });
                    });


                    this.dpPlayer = player;

                } catch (error) {
                    console.error('播放器初始化失败:', error);
                }
            }
        });
    }

    function close_iframe(is_reload) {
        let index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.window.reload_test(is_reload)
        parent.layer.close(index);
    }


    // 初始化已有数据
    function initFormData(postData) {
        if (postData) {
            form.val('postForm', {
                title: postData.title,
                post_type: postData.post_type,
                status: postData.status,
                page_slug: postData.page_slug,
                publish_date: postData.publish_date
            });

            // 设置编辑器内容
            testEditor.setMarkdown(postData.content);

            // 设置标签
            if (postData.tags) {
                postData.tags.split(',').forEach(tag => {
                    if (tag) addTag(tag);
                });
            }

            // 设置分类
            if (postData.categories) {
                postData.categories.forEach(cid => {
                    $(`input[name="category[]"][value="${cid}"]`).prop('checked', true);
                });
                form.render('checkbox');
            }

            // 设置热搜状态
            if (postData.hotSearch && postData.hotSearch == '1') {
                $('input[name="hotSearch"]').prop('checked', true);
            }

            // 重新渲染表单
            form.render();
        }
    }


    function insertSelectedVideos() {
        const existingVideos = getExistingVideos(testEditor.getMarkdown());
        let selectedVideos = [];
        let insertedCount = 0;

        $('input[name="video"]:checked').each(function () {
            const checkbox = this;
            const videoUrl = checkbox.value;
            const videoName = $(checkbox).data('name');
            const pic = $(checkbox).data('cover') || '';

            if (!existingVideos.includes(videoUrl)) {
                const videoMarkdown = `[dplayer url="${videoUrl}" pic="${pic}"/]\n`;
                testEditor.cm.replaceSelection(videoMarkdown);
                selectedVideos.push(videoName);
                insertedCount++;
            }
            console.log(videoUrl, videoName, pic);
        });


        if (selectedVideos.length === 0) {
            layer.msg('请选择要插入的视频', {icon: 2});
            return;
        }

        if (insertedCount === 0) {
            layer.msg('所选视频都已插入过', {icon: 0});
        } else {
            layer.msg(`成功插入 ${insertedCount} 个新视频`, {icon: 1});
        }

        layer.closeAll();
    }


    function handleDblClick(event) {
        const td = event.currentTarget;
        const tr = td.closest('tr');
        if (!tr) return;

        const checkbox = tr.querySelector('input[name="video"]');
        const url = checkbox.value;
        const name = checkbox.getAttribute('data-name');
        const pic = checkbox.getAttribute('data-cover') || '';

        if (url && name) {
            const videoMarkdown = `[dplayer url="${url}" pic="${pic}" /]\n`;
            testEditor.cm.replaceSelection(videoMarkdown);
            layer.closeAll();
            layer.msg(`成功插入视频`);
        }
    }


    function uploadAttachment() {

        const uploadHtml = `
                <div class="layui-form" style="padding: 20px;">
                    <div class="layui-form-item">
                        <label class="layui-form-label">视频文件</label>
                        <div class="layui-input-block">
                            <button type="button" class="layui-btn" id="upload-video">
                                <i class="layui-icon">&#xe67c;</i>选择视频
                            </button>
                            <div class="layui-progress layui-hide" lay-filter="upload-progress">
                                <div class="layui-progress-bar" lay-percent="0%"></div>
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">封面图片</label>
                        <div class="layui-input-block">
                            <button type="button" class="layui-btn" id="upload-cover">
                                <i class="layui-icon">&#xe64a;</i>选择封面
                            </button>
                            <div class="layui-inline layui-word-aux" id="cover-preview" style="display:none;">
                                <img src="" style="max-height:100px;max-width:200px;">
                            </div>
                        </div>
                    </div>
                    <div class="layui-form-item">
                        <label class="layui-form-label">视频名称</label>
                        <div class="layui-input-block">
                            <input type="text" name="video_name" class="layui-input" required>
                        </div>
                    </div>
                    <input type="hidden" name="mp4_url">
                    <input type="hidden" name="cover_url">
                    <div class="layui-form-item">
                        <div class="layui-input-block">
                            <button type="button" class="layui-btn" id="submit-transcode" style="display:none;">
                                <i class="layui-icon">&#xe609;</i>提交
                            </button>
                        </div>
                    </div>
                </div>
            `;

        layer.open({
            type: 1,
            title: '上传视频',
            area: ['500px', '300px'],
            content: uploadHtml,
            success: function (layero) {
                layui.use(['upload', 'element'], function () {
                    const upload = layui.upload;
                    const element = layui.element;

                    // 获取R2上传URL
                    async function getR2uploadUrl() {
                        try {
                            const res = await fetch('{%url("attachment/getr2uploadurl")%}');
                            return await res.json();
                        } catch (e) {
                            return {status: 0, msg: '接口请求失败'};
                        }
                    }


                    async function r2fileUploadMp4(file, onProgress) {
                        let retryCount = 0;
                        const maxRetries = 3;
                        let res;

                        while (retryCount < maxRetries) {
                            res = await getR2uploadUrl();
                            if (res && res.code === 0) break;
                            retryCount++;
                            await new Promise(resolve => setTimeout(resolve, 1000));
                        }

                        if (!res || res.code !== 0) {
                            return {code: -1, msg: '获取上传链接失败'};
                        }

                        const {uploadUrl, UploadName, publicUrl} = res.data;
                        const formData = new FormData();
                        formData.append('video', file, UploadName);

                        try {
                            const response = await axios.put(uploadUrl, formData.get('video'), {
                                headers: {'Content-Type': 'video/mp4'},
                                onUploadProgress: onProgress ? function (progressEvent) {
                                    const progress = Math.round((progressEvent.loaded * 100) / (progressEvent.total || 1));
                                    onProgress(progress);
                                } : undefined
                            });

                            return response.status === 200
                                ? {code: 1, msg: publicUrl}
                                : {code: -1, msg: '上传失败'};
                        } catch (e) {
                            return {code: -1, msg: e.message || '上传异常'};
                        }
                    }


                    async function saveVideoInfo(videoData) {
                        try {
                            const response = await fetch('{%url("attachment/upload_mv")%}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                },
                                body: JSON.stringify({
                                    ...videoData,
                                    cover_url: layero.find('input[name="cover_url"]').val()
                                })
                            });
                            return await response.json();
                        } catch (e) {
                            return {code: -1, msg: '保存失败'};
                        }
                    }


                    upload.render({
                        elem: '#upload-video',
                        auto: false,
                        accept: 'video',
                        choose: async function (obj) {
                            const files = obj.pushFile();
                            const fileKey = Object.keys(files)[0];
                            const file = files[fileKey];


                            const $progress = layero.find('.layui-progress');
                            $progress.removeClass('layui-hide');
                            element.progress('upload-progress', '0%');


                            const videoNameInput = layero.find('input[name="video_name"]');
                            if (!videoNameInput.val().trim()) {
                                const fileName = file.name.replace(/\.[^/.]+$/, "");
                                videoNameInput.val(fileName);
                            }


                            const uploadResult = await r2fileUploadMp4(file, function (percent) {
                                element.progress('upload-progress', percent + '%');
                            });

                            if (uploadResult.code !== 1) {
                                layer.msg(uploadResult.msg);
                                return;
                            }


                            layero.find('input[name="mp4_url"]').val(uploadResult.msg);


                            layero.find('#submit-transcode').show();

                            $progress.addClass('layui-hide');
                            layer.msg('视频上传成功，请上传封面');
                        }
                    });

                    upload.render({
                        elem: '#upload-cover',
                        url: '{%url("upload/upload")%}',
                        accept: 'images',
                        acceptMime: 'image/*',
                        done: function (res) {
                            if (res.code === 200) {
                                layer.msg('封面上传成功');
                                layero.find('input[name="cover_url"]').val(res.data.url);
                                layero.find('#cover-preview')
                                    .show()
                                    .find('img')
                                    .attr('src', res.data.src);
                            } else {
                                layer.msg('封面上传失败：' + res.msg);
                            }
                        }
                    });


                    layero.find('#submit-transcode').on('click', async function () {
                        const videoName = layero.find('input[name="video_name"]').val();
                        const mp4Url = layero.find('input[name="mp4_url"]').val();
                        const coverUrl = layero.find('input[name="cover_url"]').val();
                        const cid = "{%$post->cid%}";

                        if (!videoName.trim()) {
                            layer.msg('请输入视频名称');
                            return;
                        }

                        if (!mp4Url) {
                            layer.msg('请先上传视频');
                            return;
                        }

                        const loadingIndex = layer.load(1, {
                            shade: [0.1, '#fff']
                        });

                        const saveResult = await saveVideoInfo({
                            name: videoName,
                            mp4_url: mp4Url,
                            cover_url: coverUrl,
                            cid: cid
                        });

                        layer.close(loadingIndex);

                        if (saveResult.code === 0) {
                            layer.msg('上传提交成功', {
                                icon: 1,
                                time: 1000
                            }, function () {

                                const uploadLayerIndex = layer.index;

                                layer.close(uploadLayerIndex);

                                const $videoDialog = $('#video-insert-dialog');
                                if ($videoDialog.length) {
                                    loadVideoList($videoDialog);
                                }
                            });
                        } else {
                            layer.msg(saveResult.msg || '上传提交失败');
                        }
                    });
                });
            }
        });
    }


    function insertToEditor(type, url) {
        if (type === 'video') {
            testEditor.cm.replaceSelection(`[dplayer url="${url}" pic="" /]\n`);
        } else {
            testEditor.cm.replaceSelection(`![]({{img-cdn}}${url})\n`);
        }
        layer.msg('插入成功');
    }

    function loadVideoList(container) {

        const cid = "{%$post_id%}";

        const existingVideos = getExistingVideos(testEditor.getMarkdown());

        $.ajax({
            url: "{%url('contents/mv_list')%}?cid=" + cid,
            method: 'GET',
            success: function (videos) {
                if (videos.status !== 1) {
                    layer.msg('获取视频列表失败: ' + videos.message, {icon: 2});
                    return;
                }

                let tableHtml = '';
                videos.data.list.forEach(function (video) {
                    // 检查视频是否已在编辑器中存在
                    const videoUrl = `${video.mp4_url}`;
                    const isChecked = existingVideos.includes(videoUrl) ? 'checked' : '';

                    tableHtml += `
                    <tr>
                        <td>
                          <input type="checkbox" name="video" 
                            lay-skin="primary" 
                            value="${videoUrl}" 
                            data-name="${video.name}"
                            data-cover="${video.cover || ''}" 
                            lay-filter="videoSelect"
                            ${isChecked}>
                        </td>
                        <td ondblclick="handleDblClick(event)">${video.name}</td>
                        <td>${video.created_at || ''}</td>
                    </tr>`;
                });

                container.find('tbody').html(tableHtml);


                layui.form.render();


                const total = container.find('input[name="video"]').length;
                const checked = container.find('input[name="video"]:checked').length;
                container.find('input[lay-filter="checkAll"]').prop('checked', total === checked);
                layui.form.render('checkbox');

                layer.msg('刷新成功', {icon: 1});
            }
        });
    }

    function getExistingVideos(content) {
        const regex = /\[dplayer\s+url="([^"]+)"/g;
        const urls = [];
        let match;

        while ((match = regex.exec(content)) !== null) {
            urls.push(match[1]);
        }

        return urls;
    }

    function getExistingImages(content) {
        const regex = /!\[.*?\]\(\{\{img-cdn\}\}([^)]+)\)/g;
        const urls = [];
        let match;

        while ((match = regex.exec(content)) !== null) {
            urls.push('{{img-cdn}}' + match[1]);
        }
        return urls;
    }

    function loadImageList($dialog) {
        const existingImages = getExistingImages(testEditor.getMarkdown());

        $.ajax({
            url: '{%url("contents/images_list")%}',
            method: 'GET',
            success: function (response) {
                if (response.status !== 1) {
                    layer.msg('获取图片列表失败: ' + response.message, {icon: 2});
                    return;
                }

                let tableHtml = '';
                response.data.list.forEach(function (image) {
                    console.log(image);
                    const imageUrl = `{{img-cdn}}${image.image_url}`;
                    const imageSrc = `${image.image_src}`;
                    const isChecked = existingImages.includes(imageUrl) ? 'checked' : '';
                    tableHtml += `
                    <tr>
                        <td>
                            <input type="checkbox" name="image" 
                                lay-skin="primary" 
                                value="${image.image_url}" 
                                data-name="${image.name}"
                                lay-filter="imageSelect"
                                ${isChecked}>
                        </td>
                        <td><img src="${imageSrc}" style="max-height:50px;max-width:100px;" onclick="previewImage('${imageUrl}')"></td>
                        <td ondblclick="handleImageDblClick(event)">${image.name}</td>
                        <td>${image.created_at || ''}</td>
                    </tr>`;
                });

                $dialog.find('tbody').html(tableHtml);
                layui.form.render();
                layer.msg('刷新成功', {icon: 1});
            }
        });
    }

    function uploadImage() {
        const uploadHtml = `
            <div class="layui-form" style="padding: 20px;">
                <div class="layui-form-item">
                    <label class="layui-form-label">图片文件</label>
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn" id="upload-image">
                            <i class="layui-icon">&#xe67c;</i>选择图片
                        </button>
                        <div class="layui-inline layui-word-aux" id="image-preview" style="display:none;">
                            <img src="" style="max-height:100px;max-width:200px;">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">图片名称</label>
                    <div class="layui-input-block">
                        <input type="text" name="image_name" class="layui-input" required>
                    </div>
                </div>
                <input type="hidden" name="image_url">
                 <input type="hidden" name="image_src">
                  <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button type="button" class="layui-btn" id="submit-image">
                            <i class="layui-icon">&#xe609;</i>提交
                        </button>
                    </div>
                </div>
            </div>`;

        layer.open({
            type: 1,
            title: '上传图片',
            area: ['500px', '300px'],
            content: uploadHtml,
            success: function (layero) {
                layui.use(['upload'], function () {
                    const upload = layui.upload;

                    upload.render({
                        elem: '#upload-image',
                        url: '{%url("upload/upload")%}',
                        accept: 'images',
                        done: function (res) {
                            if (res.code === 200) {
                                layer.msg('上传成功');
                                layero.find('#image-preview')
                                    .show()
                                    .find('img')
                                    .attr('src', res.data.src);
                                layero.find('input[name="image_url"]').val(res.data.url);
                                layero.find('input[name="image_src"]').val(res.data.src);

                                async function saveImageInfo(imageData) {
                                    try {
                                        const response = await fetch('{%url("attachment/upload_image")%}', {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                            },
                                            body: JSON.stringify({
                                                ...imageData
                                            })
                                        });
                                        return await response.json();
                                    } catch (e) {
                                        return {code: -1, msg: '保存失败'};
                                    }
                                }

                                layero.find('#submit-image').on('click', async function () {
                                    const imageName = layero.find('input[name="image_name"]').val();
                                    const imageUrl = layero.find('input[name="image_url"]').val();
                                    const imageSrc = layero.find('input[name="image_src"]').val();

                                    if (!imageName.trim()) {
                                        layer.msg('请输入图片名称');
                                        return;
                                    }

                                    if (!imageUrl) {
                                        layer.msg('请先上传图片');
                                        return;
                                    }

                                    const loadingIndex = layer.load(1, {
                                        shade: [0.1, '#fff']
                                    });

                                    const saveResult = await saveImageInfo({
                                        name: imageName,
                                        image_url: imageUrl,
                                        image_src: imageSrc,
                                    });

                                    layer.close(loadingIndex);

                                    if (saveResult.code === 0) {
                                        layer.msg('上传提交成功', {
                                            icon: 1,
                                            time: 1000
                                        }, function () {

                                            const uploadLayerIndex = layer.index;

                                            layer.close(uploadLayerIndex);

                                            const $videoDialog = $('#video-insert-dialog');
                                            if ($videoDialog.length) {
                                                loadVideoList($videoDialog);
                                            }
                                        });
                                    } else {
                                        layer.msg(saveResult.msg || '上传提交失败');
                                    }
                                });
                            } else {
                                layer.msg('上传失败：' + res.msg);
                            }
                        }
                    });
                });
            }
        });
    }

    function insertSelectedImages() {
        let selectedImages = [];
        let insertedCount = 0;

        $('input[name="image"]:checked').each(function () {
            const imageUrl = $(this).val();
            const imageName = $(this).data('name');

            const imageMarkdown = `![${imageName}]({{img-cdn}}${imageUrl})\n`;
            testEditor.cm.replaceSelection(imageMarkdown);
            selectedImages.push(imageName);
            insertedCount++;
        });

        if (selectedImages.length === 0) {
            layer.msg('请选择要插入的图片', {icon: 2});
            return;
        }

        layer.msg(`成功插入 ${insertedCount} 张图片`, {icon: 1});
        layer.closeAll();
    }

    function handleImageDblClick(event) {
        const td = event.currentTarget;
        const tr = td.closest('tr');
        if (!tr) return;

        const checkbox = tr.querySelector('input[name="image"]');
        const url = checkbox.value;
        const name = checkbox.getAttribute('data-name');

        if (url && name) {
            const imageMarkdown = `![${name}]({{img-cdn}}${url})\n`;
            testEditor.cm.replaceSelection(imageMarkdown);
            layer.closeAll();
            layer.msg('成功插入图片');
        }
    }

    function previewImage(url) {
        layer.photos({
            photos: {
                title: '预览',
                data: [{
                    src: url
                }]
            },
            anim: 5
        });
    }

    $(function () {

        editormd.urls.atLinkBase = "https://github.com/";

        testEditor = editormd("test-editormd", {
            width: "100%",
            height: "calc(100vh - 300px)",
            minHeight: "600px",
            toc: true,
            htmlDecode: true,
            emoji: true,
            breaks: true,
            watch: false,
            todoList: true,
            imageUpload: true,
            imageFormats: ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
            imageUploadURL: '{%url("upload/upload")%}',
            path: '/static/editor/lib/',
            toolbarIcons: function () {
                return ["undo", "redo", "bold", "del", "italic", "quote",
                    "ucwords", "uppercase", "lowercase", "h1", "h2", "h3", "h4",
                    "h5", "h6", "list-ul", "list-ol", "hr", "link", "reference-link", "code", "table", "datetime",
                    "emoji", "html-entities", "pagebreak", "goto-line", "clear", "search", "imagelinkx", "multiple_img", "videolinkx", 'preview', 'insertVideoManual', 'insertImageManual']
            },
            toolbarIconsClass: {
                imagelinkx: "",
                multiple_img: "",
                videolinkx: "",
                preview: "",
                insertVideoManual: "",
                insertImageManual: ""
            },
            toolbarIconTexts: {
                imagelinkx: "插入图片",
                multiple_img: "批量上传图片",
                videolinkx: "插入视频",
                preview: "预览文章",
                insertVideoManual: "手动插入视频",
                insertImageManual: "手动插入图片"
            },
            toolbarHandlers: {
                insertImageManual: function () {
                    layer.open({
                        type: 1,
                        title: '手动插入图片',
                        area: ['450px', '280px'],
                        content: `
                            <div style="padding:20px;">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">图片地址</label>
                                    <div class="layui-input-block">
                                        <input type="text" id="image-url-input" placeholder="必填，如：https://..." class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">图片描述</label>
                                    <div class="layui-input-block">
                                        <input type="text" id="image-alt-input" placeholder="可选，图片的alt属性" class="layui-input">
                                    </div>
                                </div>
                                <div style="text-align:right; padding-top:10px;">
                                    <button class="layui-btn layui-btn-sm layui-btn-primary" id="cancel-insert-image">取消</button>
                                    <button class="layui-btn layui-btn-sm" id="confirm-insert-image">确认插入</button>
                                </div>
                            </div>
                            `,
                        success: function (layero, index) {
                            document.getElementById('cancel-insert-image').onclick = function () {
                                layer.close(index);
                            };

                            document.getElementById('confirm-insert-image').onclick = function () {
                                const imageUrl = document.getElementById('image-url-input').value.trim();
                                const imageAlt = document.getElementById('image-alt-input').value.trim();

                                if (!imageUrl || !/^https?:\/\//.test(imageUrl)) {
                                    layer.msg('请输入合法的图片地址', {icon: 2});
                                    return;
                                }

                                const markdown = imageAlt ? `![${imageAlt}](${imageUrl})` : `![](${imageUrl})`;
                                testEditor.cm.replaceSelection(markdown);
                                layer.msg('已插入图片', {icon: 1});
                                layer.close(index);
                            };
                        }
                    });
                },
                insertVideoManual: function () {
                    layer.open({
                        type: 1,
                        title: '插入视频',
                        area: ['420px', '260px'],
                        content: `
                            <div style="padding:20px;">
                                <div class="layui-form-item">
                                    <label class="layui-form-label">视频地址</label>
                                    <div class="layui-input-block">
                                        <input type="text" id="video-url-input" placeholder="必填，如：https://..." class="layui-input">
                                    </div>
                                </div>
                                <div class="layui-form-item">
                                    <label class="layui-form-label">封面地址</label>
                                    <div class="layui-input-block">
                                        <input type="text" id="cover-url-input" placeholder="可选，如：https://..." class="layui-input">
                                    </div>
                                </div>
                                <div style="text-align:right; padding-top:10px;">
                                    <button class="layui-btn layui-btn-sm layui-btn-primary" id="cancel-insert-video">取消</button>
                                    <button class="layui-btn layui-btn-sm" id="confirm-insert-video">确认插入</button>
                                </div>
                            </div>
                            `,
                        success: function (layero, index) {
                            document.getElementById('cancel-insert-video').onclick = function () {
                                layer.close(index);
                            };

                            document.getElementById('confirm-insert-video').onclick = function () {
                                const videoUrl = document.getElementById('video-url-input').value.trim();
                                const coverUrl = document.getElementById('cover-url-input').value.trim();

                                if (!videoUrl || !/^https?:\/\//.test(videoUrl)) {
                                    layer.msg('请输入合法的视频地址', {icon: 2});
                                    return;
                                }

                                const markdown = `[dplayer url="${videoUrl}" pic="${coverUrl}" /]\n`;
                                testEditor.cm.replaceSelection(markdown);
                                layer.msg('已插入视频', {icon: 1});
                                layer.close(index);
                            };
                        }
                    });
                },
                multiple_img: function () {
                    const dialogHtml = `
                        <div class="layui-form" style="padding: 20px;">
                            <div class="image-preview-container" style="margin-bottom: 15px;">
                                <div class="preview-list" style="display: grid; grid-template-columns: repeat(auto-fill, 120px); gap: 10px;"></div>
                            </div>
                            
                            <div class="layui-progress-container"></div>
                            
                            <div class="layui-form-item">
                                <button type="button" class="layui-btn" id="selectImages">
                                    <i class="layui-icon">&#xe67c;</i>选择图片
                                </button>
                                <button type="button" class="layui-btn layui-btn-normal" id="startUpload" style="display:none">
                                    <i class="layui-icon">&#xe67c;</i>开始上传
                                </button>
                            </div>
                        </div>`;

                    //打开上传对话框
                    layer.open({
                        type: 1,
                        title: '批量上传图片',
                        area: ['800px', '600px'],
                        content: dialogHtml,
                        success: function (layero) {
                            const $dialog = $(layero);
                            const $previewList = $dialog.find('.preview-list');
                            const $progressContainer = $dialog.find('.layui-progress-container');
                            let selectedFiles = [];


                            const fileInput = $('<input type="file" multiple accept="image/*" style="display:none">');


                            $dialog.find('#selectImages').click(function () {
                                fileInput.click();
                            });

                            fileInput.on('change', function (e) {
                                const newFiles = Array.from(e.target.files);

                                selectedFiles = [...selectedFiles, ...newFiles];

                                $previewList.empty();
                                $progressContainer.empty();

                                selectedFiles.forEach((file, index) => {
                                    const reader = new FileReader();
                                    reader.onload = function (e) {
                                        const $preview = $(`
                                                <div class="preview-item" style="position:relative;">
                                                    <img src="${e.target.result}" style="width:120px;height:120px;object-fit:cover;">
                                                    <div class="progress-text" style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.5);color:white;text-align:center;padding:2px;">
                                                        等待上传
                                                    </div>
                                                    <div class="insert-btn" style="position:absolute;bottom:0;left:0;right:0;background:rgba(0,0,0,0.7);color:white;text-align:center;padding:4px;cursor:pointer;display:none;">
                                                        点击插入
                                                    </div>
                                                    <div class="remove-btn" style="position:absolute;top:-8px;right:-8px;width:20px;height:20px;background:rgba(0,0,0,0.5);color:white;border-radius:50%;text-align:center;line-height:20px;cursor:pointer;">
                                                        ×
                                                    </div>
                                                </div>
                                            `);

                                        $preview.find('.remove-btn').click(function () {
                                            selectedFiles.splice(index, 1);
                                            $preview.remove();
                                            $progressContainer.find(`.progress-item[data-index="${index}"]`).remove();

                                            if (selectedFiles.length === 0) {
                                                $dialog.find('#startUpload').hide();
                                            }
                                        });

                                        $previewList.append($preview);
                                    };
                                    reader.readAsDataURL(file);

                                    $progressContainer.append(`
                                            <div class="progress-item" data-index="${index}" style="margin-bottom:10px;">
                                                <div class="layui-progress" lay-filter="progress-${index}">
                                                    <div class="layui-progress-bar" lay-percent="0%"></div>
                                                </div>
                                                <div class="filename" style="font-size:12px;color:#666;">${file.name}</div>
                                            </div>
                                        `);
                                });

                                if (selectedFiles.length > 0) {
                                    $dialog.find('#startUpload').show();
                                }

                                layui.element.render('progress');
                            });


                            $dialog.find('#startUpload').click(async function () {
                                if (!selectedFiles.length) {
                                    layer.msg('请先选择图片');
                                    return;
                                }

                                const $startBtn = $(this);
                                $startBtn.prop('disabled', true).text('上传中...');

                                try {
                                    for (let i = 0; i < selectedFiles.length; i++) {
                                        const file = selectedFiles[i];
                                        const formData = new FormData();
                                        formData.append('file', file);

                                        const $preview = $previewList.find('.preview-item').eq(i);
                                        const $progressText = $preview.find('.progress-text');
                                        $progressText.show();

                                        const result = await $.ajax({
                                            url: "{%url('upload/upload')%}",
                                            type: 'POST',
                                            data: formData,
                                            processData: false,
                                            contentType: false,
                                            xhr: function () {
                                                const xhr = new window.XMLHttpRequest();
                                                xhr.upload.addEventListener("progress", function (evt) {
                                                    if (evt.lengthComputable) {
                                                        const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                                                        layui.element.progress(`progress-${i}`, percentComplete + '%');
                                                        $progressText.text(percentComplete + '%');

                                                        if (percentComplete === 100) {
                                                            $progressText.hide();
                                                            $preview.find('.insert-btn').show();
                                                        }
                                                    }
                                                }, false);
                                                return xhr;
                                            }
                                        });

                                        if (result.code === 200) {
                                            $progressText.hide();

                                            const $insertBtn = $preview.find('.insert-btn').show();

                                            $insertBtn.on('click', function () {
                                                const imageMarkdown = `![${file.name}]({{img-cdn}}${result.data.url})\n`;
                                                testEditor.cm.replaceSelection(imageMarkdown);
                                                layer.msg('图片已插入');
                                            });

                                            await $.ajax({
                                                url: '{%url("attachment/upload_image")%}',
                                                type: 'POST',
                                                contentType: 'application/json',
                                                data: JSON.stringify({
                                                    name: file.name,
                                                    image_url: result.data.url,
                                                    image_src: result.data.src
                                                })
                                            });
                                        }
                                    }

                                    layer.msg(`成功上传 ${selectedFiles.length} 张图片`, {
                                        icon: 1,
                                        time: 2000
                                    });

                                } catch (error) {
                                    layer.msg('上传失败: ' + error.message, {
                                        icon: 2,
                                        time: 2000
                                    });
                                } finally {
                                    $startBtn.prop('disabled', false).text('开始上传');
                                }
                            });
                        }
                    });
                },
                imagelinkx: function () {
                    const _thisEditor = this;

                    function renderImageDialog(imageList) {
                        const existingImages = getExistingImages(testEditor.getMarkdown());
                        let html = `
                            <div id="image-insert-dialog" style="padding:15px;">
                                <div class="layui-form">
                                    <div class="layui-btn-group" style="margin-bottom: 10px;">
                                        <button type="button" class="layui-btn layui-btn-sm" id="refreshImageList">
                                            <i class="layui-icon layui-icon-refresh"></i> 刷新列表
                                        </button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" onclick="uploadImage()">
                                            <i class="layui-icon layui-icon-upload"></i> 上传图片
                                        </button>
                                        <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" onclick="insertSelectedImages()">
                                            <i class="layui-icon layui-icon-ok"></i> 插入选中图片
                                        </button>
                                    </div>
                                    <table class="layui-table" lay-size="sm">
                                        <colgroup>
                                            <col width="50">
                                            <col width="120">
                                            <col>
                                            <col width="160">
                                        </colgroup>
                                        <thead>
                                            <tr>
                                                <th><input type="checkbox" lay-filter="checkAllImages" lay-skin="primary"></th>
                                                <th>预览</th>
                                                <th>图片名称</th>
                                                <th>上传时间</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                        imageList.forEach(function (image) {
                            const imageUrl = `{{img-cdn}}${image.image_url}`;
                            const imageSrc = `${image.image_src}`;
                            const isChecked = existingImages.includes(imageUrl) ? 'checked' : '';
                            html += `
                                    <tr>
                                        <td>
                                            <input type="checkbox" name="image" 
                                                lay-skin="primary" 
                                                value="${image.image_url}" 
                                                data-name="${image.name}"
                                                lay-filter="imageSelect"
                                                ${isChecked}>
                                        </td>
                                        <td><img src="${imageSrc}" style="max-height:50px;max-width:100px;" onclick="previewImage('${imageUrl}')"></td>
                                        <td ondblclick="handleImageDblClick(event)">${image.name}</td>
                                        <td>${image.created_at || ''}</td>
                                    </tr>`;
                        });

                        html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>`;

                        layer.open({
                            type: 1,
                            title: '选择图片',
                            area: ['800px', '500px'],
                            content: html,
                            success: function (layero, index) {
                                const $dialog = $(layero).find('#image-insert-dialog');

                                layui.use(['form'], function () {
                                    const form = layui.form;
                                    form.render();

                                    const updateCheckAll = function () {
                                        const total = $dialog.find('input[name="image"]').length;
                                        const checked = $dialog.find('input[name="image"]:checked').length;
                                        $dialog.find('input[lay-filter="checkAllImages"]').prop('checked', total === checked);
                                        form.render('checkbox');
                                    };

                                    updateCheckAll();

                                    form.on('checkbox(checkAllImages)', function (data) {
                                        const checked = data.elem.checked;
                                        $dialog.find('input[name="image"]').prop('checked', checked);
                                        form.render('checkbox');
                                    });

                                    form.on('checkbox(imageSelect)', function (data) {
                                        updateCheckAll();
                                    });

                                    $dialog.find('#refreshImageList').on('click', function () {
                                        loadImageList($dialog);
                                    });
                                });
                            }
                        });
                    }

                    $.ajax({
                        url: '{%url("contents/images_list")%}',
                        method: 'GET',
                        success: function (response) {
                            if (response.status !== 1) {
                                layer.msg('获取图片列表失败: ' + response.message, {icon: 2});
                                return;
                            }
                            renderImageDialog(response.data.list);
                        }
                    });
                },
                videolinkx: function () {
                    const _thisEditor = this;


                    function renderVideoDialog(videoList) {

                        const existingVideos = getExistingVideos(testEditor.getMarkdown());

                        let html = `
                        <div id="video-insert-dialog" style="padding:15px;">
                            <div class="layui-form">
                                <div class="layui-btn-group" style="margin-bottom: 10px;">
                                    <button type="button" class="layui-btn layui-btn-sm" id="refreshVideoList">
                                        <i class="layui-icon layui-icon-refresh"></i> 刷新列表
                                    </button>
                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-normal" onclick="uploadAttachment()">
                                        <i class="layui-icon layui-icon-upload"></i> 上传视频
                                    </button>
                                    <button type="button" class="layui-btn layui-btn-sm layui-btn-warm" onclick="insertSelectedVideos()">
                                        <i class="layui-icon layui-icon-ok"></i> 插入选中视频
                                    </button>
                                </div>
                                <table class="layui-table" lay-size="sm">
                                    <colgroup>
                                        <col width="50">
                                        <col>
                                        <col width="50">
                                    </colgroup>
                                    <thead>
                                        <tr>
                                            <th><input type="checkbox" lay-filter="checkAll" lay-skin="primary"></th>
                                            <th>视频名称</th>
                                            <th>上传时间</th>
                                        </tr>
                                    </thead>
                                    <tbody>`;

                        videoList.forEach(function (video) {

                            const videoUrl = `${video.mp4_url}`;
                            const isChecked = existingVideos.includes(videoUrl) ? 'checked' : '';

                            html += `
                                    <tr>
                                        <td>
                                             <input type="checkbox" name="video" 
                                            lay-skin="primary" 
                                            value="${videoUrl}" 
                                            data-name="${video.name}"
                                            data-cover="${video.cover || ''}"
                                            lay-filter="videoSelect"
                                            ${isChecked}>
                                        </td>
                                        <td ondblclick="handleDblClick(event)">${video.name}</td>
                                        <td>${video.created_at || ''}</td>
                                    </tr>`;
                        });

                        html += `
                                    </tbody>
                                </table>
                                <div class="layui-form-item" style="margin-top: 15px;">
                                      <button type="button" class="layui-btn" onclick="insertSelectedVideos()">
                        <i class="layui-icon layui-icon-ok"></i> 插入选中视频
                    </button>
                    <button type="button" class="layui-btn layui-btn-normal" onclick="uploadAttachment()">
                        <i class="layui-icon layui-icon-upload"></i> 上传视频
                    </button>
                                </div>
                            </div>
                        </div>`;

                        layer.open({
                            type: 1,
                            title: '选择视频',
                            area: ['700px', '500px'],
                            content: html,
                            success: function (layero, index) {
                                const $dialog = $(layero).find('#video-insert-dialog');

                                layui.use(['form'], function () {
                                    const form = layui.form;
                                    form.render();


                                    const updateCheckAll = function () {
                                        const total = $dialog.find('input[name="video"]').length;
                                        const checked = $dialog.find('input[name="video"]:checked').length;
                                        $dialog.find('input[lay-filter="checkAll"]').prop('checked', total === checked);
                                        form.render('checkbox');
                                    };


                                    updateCheckAll();


                                    form.on('checkbox(checkAll)', function (data) {
                                        const checked = data.elem.checked;
                                        $dialog.find('input[name="video"]').prop('checked', checked);
                                        form.render('checkbox');
                                    });


                                    form.on('checkbox(videoSelect)', function (data) {
                                        updateCheckAll();
                                    });


                                    $dialog.find('#refreshVideoList').on('click', function () {
                                        loadVideoList($dialog);
                                    });
                                });
                            }
                        });
                    }

                    const cid = "{%$post_id%}";
                    $.ajax({
                        url: "{%url('contents/mv_list')%}?cid=" + cid,
                        method: 'GET',
                        success: function (videos) {
                            if (videos.status !== 1) {
                                layer.msg('获取视频列表失败: ' + videos.message, {icon: 2});
                                return;
                            }
                            renderVideoDialog(videos.data.list);
                        }
                    });
                }
            },

            prevHandler: function (cmValue) {


                cmValue = this.parserDomain(cmValue)
                cmValue = this.parserVideo(cmValue)
                //  console.log(cmValue);
                // console.log(cmValue)

                return cmValue
            },
            parserDomain: function (cmValue) {
                cmValue = cmValue.replace(/\{\{img\-cdn\}\}/g, "{%BASE_IMG_URL%}")
                cmValue = cmValue.replace(/\{\{mp4\-cdn\}\}/g, "{%$mp4_domain%}")
                cmValue = cmValue.replace(/\{\{m3u8\-cdn\}\}/g, "{%$m3u8_domain%}")

                return cmValue
            },
            parserVideo: function (cmValue) {
                let fn = function parserVideox(v) {
                    let attrs = v.replace(/"/g, '').split(" ")
                    let dplayer_attrs = {};
                    for (let i in attrs) {
                        let line = attrs[i].trim()
                        let attr = line.split("=")
                        if (attr.length === 2) {
                            dplayer_attrs[attr[0].trim()] = attr[1].trim();
                        }
                    }
                    return dplayer_attrs;
                }

                $reg2 = /\[dplayer\s+(.+)\/]/g;
                return cmValue.replace($reg2, (item, prop) => {
                    let obj = fn(prop)
                    let video = undefined

                    let ext = obj['url'].substr(-4)
                    if (ext === '.mp4') {
                        video = {
                            "url": obj['url'],
                            "pic": "",
                            "type": "mp4",
                            "thumbnails": null
                        }
                    } else {
                        video = {
                            "url": obj['url'],
                            "pic": obj['pic'] || "",
                            "type": "hls",
                            "thumbnails": null
                        }
                    }

                    const playerId = 'dplayer-' + Math.random().toString(36).substr(2, 9);

                    // 构建播放器配置
                    let playerConfig = JSON.stringify({
                        "live": false,
                        "autoplay": false,
                        "theme": "#FADFA3",
                        "loop": false,
                        "screenshot": false,
                        "hotkey": true,
                        "preload": "metadata",
                        "lang": "zh-cn",
                        "logo": null,
                        "volume": 0.6,
                        "mutex": true,
                        "video": video
                    });

                    // 添加唯一 ID 和事件处理
                    return `<div id="${playerId}" class="dplayer" config='${playerConfig}' onclick="event.preventDefault();"></div>`;
                });
            },
            onchange: function () {
                afterChangeInit()
            },
            lang: {
                toolbar: {
                    videox: "插入视频",
                    imagex: "插入图片",
                    imagey: "插入多图片",
                    videolinkx: "插入视频链接",
                },
                dialog: {
                    videolinkx: {
                        debug: false,
                        title: '插入视频链接',
                        cover: '封面',
                        coverWidth: '宽度',
                        coverHeight: '高度',
                        duration: '时长',
                        m3u8: 'm3u8链接',
                        mp4: 'mp4链接',
                        m3u8NotValid: '错误: m3u8链接不合法',
                        m3u8Empty: '错误: m3u8链接未正确识别',
                        m3u8NotFormat: '错误: m3u8链接不是m3u8为后缀的链接',
                        mp4Empty: '错误: mp4链接未正确识别',
                        mp4NotFormat: '错误: mp4链接不是mp4为后缀的链接',
                        coverEmpty: '错误: 封面链接未正确识别',
                        coverNotFormat: '错误: 封面链接只能是JPG|PNG|JPEG|GIF为后缀的链接',
                        coverWidthNotValid: '错误: 封面宽度不合法',
                        coverHeightNotValid: '错误: 封面高度不合法',
                        durationNotValid: '错误: 视频时长不合法',
                    }
                },
            },

            onload: function () {
                afterChangeInit()

                $("#videox").bind("change", function () {
                    let formData = new FormData();
                    let fileData = $(this).prop("files")[0];
                    if (!fileData) {
                        return;
                    }
                    formData.append("file", fileData);
                    $.ajax({
                        url: "{%url('upload/uploadMp4')%}",
                        type: 'POST',
                        async: false,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            let ele = '[dplayer url="' + '{{mp4-cdn}}' + '/' + data.data.url + '" pic="" /]';
                            testEditor.cm.replaceSelection(ele);
                        }
                    })
                });

                $("#imagex").bind("change", function () {
                    let formData = new FormData();
                    let fileData = $(this).prop("files")[0];
                    if (!fileData) {
                        return;
                    }
                    formData.append("file", fileData);
                    $.ajax({
                        url: "{%url('upload/upload')%}",
                        type: 'POST',
                        async: false,
                        data: formData,
                        cache: false,
                        contentType: false,
                        processData: false,
                        success: function (data) {
                            let ele = '![' + data.data.width + 'X' + data.data.height + ']({{img-cdn}}' + data.data.url + ')' + "\n"
                            testEditor.cm.replaceSelection(ele);
                        }
                    })
                });
                $("#imagey").bind("change", function () {
                    let files = $(this).prop("files");
                    if (!files.length) {
                        return;
                    }
                    for (let i = 0; i < files.length; i++) {
                        let formData = new FormData();
                        let fileData = files[i];
                        formData.append("file", fileData);
                        $.ajax({
                            url: "{%url('upload/upload')%}",
                            type: 'POST',
                            async: false,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (data) {
                                let ele = '![' + data.data.width + 'X' + data.data.height + ']({{img-cdn}}' + data.data.url + ')' + "\n"
                                testEditor.cm.replaceSelection(ele);
                            }
                        })
                    }
                });
            }

        });

    });

    // 复制文章链接函数
    function copyArticleLink() {
        const canShow = {%if $post->status == "publish"%}true{%else%}false{%/if%};
        const linkText = document.getElementById('article-link-text').textContent;
        
        // 创建临时输入框
        const tempInput = document.createElement('input');
        tempInput.value = linkText;
        document.body.appendChild(tempInput);
        
        // 选择并复制
        tempInput.select();
        tempInput.setSelectionRange(0, 99999); // 兼容移动设备
        
        try {
            const successful = document.execCommand('copy');
            if (!canShow) {
                layer.msg('此文章未发布暂不能在前台打开', {icon: 2});
                return
            }
            if (successful) {
                layer.msg('文章链接已复制到剪贴板', {icon: 1});
            } else {
                layer.msg('复制失败，请手动复制', {icon: 2});
            }
        } catch (err) {
            // 使用现代 API
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(linkText).then(function() {
                    layer.msg('文章链接已复制到剪贴板', {icon: 1});
                }).catch(function() {
                    layer.msg('复制失败，请手动复制', {icon: 2});
                });
            } else {
                layer.msg('复制失败，请手动复制', {icon: 2});
            }
        } finally {
            // 移除临时输入框
            document.body.removeChild(tempInput);
        }
    }

</script>

</body>

</html>