<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/css/layui.css">
    <script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
    <link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
    <meta charset="utf-8">
    <title>编缉分类</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/css/layui.css">
    <link rel="stylesheet" href="/static/editor/css/editormd.css" />
    <script src="https://cdn.jsdelivr.net/npm/dplayer/dist/DPlayer.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/hls.js/dist/hls.min.js"></script>
    <link rel="shortcut icon" href="https://pandao.github.io/editor.md/favicon.ico" type="image/x-icon" />
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

        .layui-tab-brief>.layui-tab-title {
            background-color: #f8f8f8;
            border-bottom: 1px solid #f6f6f6;
        }

        .layui-tab-brief>.layui-tab-title .layui-this {
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


        .layui-tab-brief>.layui-tab-title {
            border-bottom: none;
        }

        .layui-tab-brief>.layui-tab-title .layui-this {
            color: #009688;
        }

        .layui-tab-brief>.layui-tab-title .layui-this:after {
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

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">名称：</label>
                        <div class="layui-input-inline">
                            <input placeholder="name" name="name"
                                   value="{%$post->name%}" class="layui-input">
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">别名：</label>
                        <div class="layui-input-inline">
                            <input placeholder="slug" name="slug"
                                   value="{%$post->slug%}" class="layui-input" >
                        </div>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">排序字段：</label>
                        <div class="layui-input-inline">
                            <select name="sort_column" data-val>
                                <option value="">默认排序</option>
                                {%html_options selected=data_get($post,'sort_column') options=$customsort_options%}
                            </select>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">seo_标题：</label>
                        <div class="layui-input-block">
                            <textarea name="seo_title" class="layui-textarea">{%$post->seo_title%}</textarea>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">seo_关键词：</label>
                        <div class="layui-input-block">
                            <textarea name="seo_keywords" class="layui-textarea">{%$post->seo_keywords%}</textarea>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">seo_描述：</label>
                        <div class="layui-input-block">
                            <textarea name="seo_description" class="layui-textarea">{%$post->seo_description%}</textarea>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-form-item">
                        <label class="layui-form-label">类别描述：</label>
                        <div class="layui-input-block">
                            <textarea name="description" class="layui-textarea">{%$post->description%}</textarea>
                        </div>
                    </div>
                </div>

                <div class="layui-form-item">
                    <div class="layui-inline">
                        <label class="layui-form-label">排序：</label>
                        <div class="layui-input-inline">
                            <input placeholder="order" name="sort_order"
                                   value="{%$post->sort_order%}" class="layui-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="layui-col-md3 right-panel">
                <div class="layui-card">
                    <div class="layui-tab layui-tab-brief" lay-filter="settings-tab">
                        <ul class="layui-tab-title">
                            <li class="layui-this">选项</li>
                        </ul>

                    </div>
                </div>
                <div class="layui-card">
                    <div class="layui-card-body" style="padding: 15px;">
                        <div class="layui-btn-container" style="display: flex; flex-direction: column; gap: 10px;">
                            <input type="hidden" name="_pk" value="{%$post->id%}">
                            <button class="layui-btn layui-btn-normal layui-btn-fluid" lay-submit
                                    lay-filter="submitPost">确定</button>
                            <button type="reset" class="layui-btn layui-btn-primary layui-btn-fluid">重置</button>
                            <button class="layui-btn layui-btn-warm layui-btn-fluid"
                                    onclick="close_iframe(false)">关闭</button>
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
    let currentCid = "{%$post->id%}";

    layui.use(['form', 'jquery', 'laydate', 'upload', 'element'], function () {
        const $ = layui.$;
        const form = layui.form;
        const upload = layui.upload;
        const element = layui.element;

        //附件监听选项卡切换
        element.on('tab(settings-tab)', function (data) {
            console.log('当前Tab索引：' + data.index);
        });

        const baseUrl = window.location.origin + '/';
        //页面初始化时执行一次
        const selectedValue = "{%$post->type%}"; //获取当前 select 的值
        handlePostTypeChange(selectedValue);


        form.on('select(postType)', function (data) {
            handlePostTypeChange(data.value);
        });




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

        form.on('submit(submitPost)', function (data) {
            const submitData = data.field;

            //表单提交
            $.ajax({
                url: "{%url('categories/edit_save')%}",
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


    });

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
        }
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


</script>

</body>

</html>