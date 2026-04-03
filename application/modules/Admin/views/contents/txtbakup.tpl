<!DOCTYPE html>
<html>
<head>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/css/layui.css">
<script src="https://unpkg.com/@wangeditor/editor@latest/dist/index.js"></script>
<link href="https://unpkg.com/@wangeditor/editor@latest/dist/css/style.css" rel="stylesheet">
<meta charset="utf-8">
<title>修改文章</title>
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
    .main-layout { margin-top: 20px; }
    .left-panel { padding-right: 20px; }
    .right-panel .layui-card { margin-bottom: 20px; }
    .editor-area { height: 300px; }
  </style>
  <style>
  /* ...existing code... */
  .tag-list { display: flex; flex-wrap: wrap; gap: 5px; }
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
  .tag-item .close:hover { color: #FF5722; }
</style>
<style>
    /* ...existing code... */
    
    /* 封面图上传样式 */
    .layui-upload-drag {
        position: relative;
        padding: 20px;
        border: 1px dashed #e2e2e2;
        background-color: #fff;
        text-align: center;
        cursor: pointer;
        margin-bottom: 20px;
    }
    
    .layui-upload-drag:hover {
        border-color: #1E9FFF;
    }
    
    #coverPreview {
        margin-top: 10px;
        cursor: pointer;
    }
    
    #coverPreview:hover::after {
        content: '点击移除';
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: rgba(0,0,0,0.6);
        color: #fff;
        padding: 5px 10px;
        border-radius: 3px;
    }
     /* 封面图预览样式 */
    #coverPreview {
        position: relative;
        margin-top: 10px;
        text-align: center;
    }
    
    #coverPreview img {
        max-width: 100%;
        max-height: 200px;
        border-radius: 4px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    #coverPreview.layui-hide {
        display: none !important;
    }

    /* 优化上传区域样式 */
    .layui-upload-drag {
        min-height: 100px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
</style>
</head>
<body>

<div class="layui-container main-layout">
  <form class="layui-form" lay-filter="postForm">

    <div class="layui-row">
      <!-- 左侧：标题、正文、自定义字段 -->
      <div class="layui-col-md9 left-panel">

        <!-- 标题 -->
        <div class="layui-form-item">
          <input type="text" name="title" value="{%$post->title %}" required lay-verify="required" placeholder="请输入标题" autocomplete="off" class="layui-input layui-bg-gray" style="font-size: 18px;">
          <input type="hidden" name="cid" value="{%$post->cid%}">
        </div>
        <!-- 新增封面图上传区域 -->
        <div class="layui-form-item">
          <div class="layui-upload-drag" id="coverUpload">
            <i class="layui-icon layui-icon-upload"></i>
            <p>点击上传封面图，或将图片拖拽到此处</p>
            <div class="layui-hide" id="coverPreview">
              <img src="{%$post->fieldValue('banner')%}" alt="封面预览" style="max-width: 100%; max-height: 200px;">
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

     <!-- 替换原有的自定义字段模块 -->
    <div class="layui-card">
            <div class="layui-card-header" style="cursor: pointer;" id="custom-fields-toggle">
                自定义字段
                <i class="layui-icon layui-icon-right" style="float: right;"></i>
            </div>
            <div class="layui-card-body" id="custom-fields-content" style="display: none;">
            <div id="custom-fields"></div>
            <div class="layui-row layui-col-space10">
              <div class="layui-col-md5">
                <input type="text" name="fields[name][]" placeholder="跳转到哪里" class="layui-input">
              </div>
              <div class="layui-col-md7">
                <input type="text" name="fields[value][]" placeholder="字段值" class="layui-input">
              </div>
           </div>

            <div id="custom-fields"></div>
            <div class="layui-row layui-col-space10">
              <div class="layui-col-md5">
                <input type="text" name="fields[name][]" placeholder="列表是否启用热搜" class="layui-input">
              </div>
              <div class="layui-col-md7">
                <input type="text" name="fields[value][]" placeholder="字段值" class="layui-input">
              </div>
           </div>
               
          <button type="button" class="layui-btn layui-btn-sm layui-btn-primary" id="add-field">
              <i class="layui-icon">&#xe654;</i> 添加字段
          </button>
                
            </div>
          </div>
      </div>

      <div class="layui-col-md3 right-panel">
          <div class="layui-card">
            <div class="layui-card-header">文章类型与作者</div>
            <div class="layui-card-body">
              <select name="post_type" lay-filter="postType">
                <option value="post">文章</option>
                <option value="page">单页</option>
              </select>
              <select name="author">
                {%foreach $authorlist as $author%}
                <option value="{%$author['uid']%}" >{%$author['screenName']%}</option>  
                {%/foreach%}
            </select>
             
              <div id="pageUrlConfig" style="display: none; margin-top: 10px;">
                <div class="layui-form-item">
                  <input type="text" name="page_base_url" class="layui-input" readonly style="margin-bottom: 5px;" value="">
                  <input type="text" name="page_slug" class="layui-input" placeholder="自定义链接后缀">
                </div>
              </div>
            </div>
        </div>
         

      
        <div class="layui-card">
          <div class="layui-card-header">分类</div>
          <div class="layui-card-body">
            {%foreach $categoryList as $category%}  
            <input type="checkbox" name="category[]" title="{%$category.name%}" value="{%$category.mid%}"
            {%foreach $post->category as $postCategory%}
            {%if $postCategory.mid == $category.mid%} 
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
            <input type="text" value="{%$post->created%}"  name="publish_date" id="publish_date" placeholder="请选择发布日期" class="layui-input" readonly>
          </div>
        </div>

      
        <div class="layui-card">
          <div class="layui-card-header">标签</div>
          <div class="layui-card-body">
            <div class="tag-input-container">
              <div class="tag-list" style="margin-bottom: 10px;">
                {%foreach $post->tags as $tag%}
                <span class="tag-item">{%$tag.name%}<span class="close">×</span></span>
                {%/foreach%}
              </div>
              <input type="text" id="tagInput" placeholder="输入标签后按回车" class="layui-input">
              <input type="hidden" name="tags" id="tagsValue">
            </div>
          </div>
        </div>

     
        <div class="layui-card">
          <div class="layui-card-header">状态</div>
          <div class="layui-card-body">
            <select name="status">
              <option value="publish" >发布</option>
              <option value="draft" selected="selected">待审核</option>
              <option value="private">下架</option>
            </select>
          </div>
        </div>



      
    <div class="layui-card">
    <div class="layui-card-body" style="padding: 15px;">
        <div class="layui-btn-container" style="display: flex; flex-direction: column; gap: 10px;">
            <button class="layui-btn layui-btn-normal layui-btn-fluid" lay-submit lay-filter="submitPost">发布</button>
            <button type="reset" class="layui-btn layui-btn-primary layui-btn-fluid">重置</button>
            <button  class="layui-btn layui-btn-warm layui-btn-fluid" onclick="close_iframe(false)">关闭</button>
        </div>
    </div>
</div>
  
  

      </div>
    </div>

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/layui@2.9.8/dist/layui.js"></script>

<script>
    layui.use(['form','jquery','laydate', 'upload'], function () {
      const $ = layui.$;
      const form = layui.form;
      const upload = layui.upload;

    $('#custom-fields-toggle').on('click', function() {
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


    upload.render({
        elem: '#coverUpload',
        url: "{%url('upload/upload')%}", 
        accept: 'image',
        acceptMime: 'image/*',
        before: function(obj) {
      
            obj.preview(function(index, file, result) {
                $('#coverPreview').removeClass('layui-hide')
                    .find('img').attr('src', result);
            });
        },
        done: function(res) {
            if(res.code === 200) {
                $('#coverPreview').removeClass('layui-hide')
                    .find('img').attr('src', res.data.src);
                $('#coverImageInput').val(res.data.url);
                layer.msg('上传成功');
            } else {
                $('#coverPreview').addClass('layui-hide');
                layer.msg('上传失败：' + res.msg);
            }
        },
        error: function() {
            $('#coverPreview').addClass('layui-hide');
            layer.msg('上传失败，请重试');
        }
    });
    
   
    $('#coverPreview').on('click', function(e){
        if(e.target.tagName === 'IMG'){
            layer.confirm('是否移除封面图？', function(index){
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
    $('input[name="page_base_url"]').val(baseUrl);
    
   
    form.on('select(postType)', function(data){
      const pageUrlConfig = $('#pageUrlConfig');
      if(data.value === 'page') {
        pageUrlConfig.slideDown();
      
        pageUrlConfig.closest('.layui-card').next('.layui-card').hide();
      } else {
        pageUrlConfig.slideUp();
        
        pageUrlConfig.closest('.layui-card').next('.layui-card').show();
      }
    });
    
    $('input[name="page_slug"]').on('input', function(){
      const slug = $(this).val().trim();
      const baseUrl = window.location.origin + '/';
      $('input[name="page_base_url"]').val(baseUrl + (slug || ''));
    });
    const laydate = layui.laydate;
    
    laydate.render({
      elem: '#publish_date',
      type: 'datetime',
      value: new Date()
    });

    
    const tagInput = $('#tagInput');
    const tagList = $('.tag-list');
    const tagsValue = $('#tagsValue');
    let tags = [];

    function updateTagsValue() {
      tagsValue.val(tags.join(','));
    }

    function addTag(value) {
      value = value.trim();
      if (value && !tags.includes(value)) {
        tags.push(value);
        const tagElement = $(`
          <span class="tag-item">
            ${value}
            <span class="close">×</span>
          </span>
        `);
        tagList.append(tagElement);
        updateTagsValue();
      }
      tagInput.val('');
    }

    tagList.on('click', '.close', function() {
      const tag = $(this).parent().text().slice(0, -1);
      tags = tags.filter(t => t !== tag);
      $(this).parent().remove();
      updateTagsValue();
    });

    tagInput.on('keydown', function(e) {
      if (e.keyCode === 13) {
        e.preventDefault();
        addTag($(this).val());
      }
    });

    tagInput.on('blur', function() {
      if ($(this).val().trim()) {
        addTag($(this).val());
      }
    });



    $(document).on('click', '.remove-field', function () {
      $(this).closest('.field-item').remove();
    });


   
    form.on('submit(submitPost)', function(data) {
       
        const content = testEditor.getMarkdown();
        
     
        const customFields = [];
        $('#custom-fields .field-item').each(function() {
            const name = $(this).find('input[name="fields[name][]"]').val();
            const value = $(this).find('input[name="fields[value][]"]').val();
            if (name && value) {
                customFields.push({ name, value });
            }
        });

       
        const categories = [];
        $('input[name="category[]"]:checked').each(function() {
            categories.push($(this).val());
        });

       
        const tags = [];
        $('.tag-list .tag-item').each(function() {
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

      
        const submitData = {
            title: data.field.title,
            cid: data.field.cid,
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
            cover_image: data.field.cover_image
        };

        // 表单提交
        $.ajax({
            url: "{%url('contents/txt_save')%}", 
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(submitData),
            success: function(res) {
                if (res.code === 0) {
                    layer.msg('保存成功', {
                        icon: 1,
                        time: 1000
                    }, function() {
                        // 成功后关闭页面并刷新父页面
                        close_iframe(true);
                    });
                } else {
                    layer.msg('保存失败：' + res.msg, {
                        icon: 2,
                        time: 2000
                    });
                }
            },
            error: function() {
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
        title: function(value) {
            if (!value) {
                return '标题不能为空';
            }
            if (value.length > 100) {
                return '标题不能超过100个字符';
            }
        },
        content: function() {
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




    function afterChangeInit() {
      $(".dplayer").each(function (item, prop) {
          let jsonStr = $(prop).attr("config");
          let config = JSON.parse(jsonStr);
          config['container'] = prop
          new DPlayer(config);
      });
    }
       function close_iframe(is_reload){
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
 $(function () {
        editormd.urls.atLinkBase = "https://github.com/";

        testEditor = editormd("test-editormd", {
             width: "100%",
            height: "calc(100vh - 300px)", // 调整编辑器高度
            minHeight: "600px",            // 设置最小高度
            toc: true,
            htmlDecode: true,
            emoji: true,
            breaks: true,
            watch: false,
            todoList: true,
            imageUpload     : true, 
            imageFormats    : ["jpg", "jpeg", "gif", "png", "bmp", "webp"],
            imageUploadURL  : "/upload/image", 
            path: '/static/editor/lib/',
            toolbarIcons: function () {
                return ["undo", "redo", "bold", "del", "italic", "quote", 
                "ucwords", "uppercase", "lowercase", "h1", "h2", "h3", "h4",
                 "h5", "h6", "list-ul", "list-ol", "hr", "link", "reference-link","image","videolinkx", "code", "table", "datetime",
                   "emoji", "html-entities", "pagebreak", "goto-line", "watch", "clear", "search",'preview']
            },
            toolbarIconsClass: {
                videolinkx: "fa fa-video-camera", // 插入视频链接、
            },
            toolbarIconTexts: {
                videolinkx: "插入视频链接",
            },
            toolbarHandlers: {
                // 插入视频链接
                videolinkx: function () {
                    this.executePlugin("videolinkxDialog", "video-linkx-dialog/video-linkx-dialog");
                },
            },
            prevHandler: function (cmValue) {
                cmValue = this.parserDomain(cmValue)
                cmValue = this.parserVideo(cmValue)
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
                    let attrs = v.replace(/"/g,'').split(" ")

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

                    let ext = obj['url'].substr(-4)
                    let video = undefined
                    if(ext === '.mp4'){
                        video = {
                            "url": obj['url'],
                            "pic": "",
                            "type": "mp4",
                            "thumbnails": null
                        }
                    }else{
                        video = {
                            "url": obj['url'],
                            "pic": obj['pic'],
                            "type": "hls",
                            "thumbnails": null
                        }
                    }
                    // 视频使用dplayer
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
                    })

                    return '<div class="dplayer" config=\'' + playerConfig + '\'></div>';
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
      
</script>

</body>
</html>
