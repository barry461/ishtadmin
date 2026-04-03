{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>
    /* 不换行 */
    .layui-form-label {
      white-space: nowrap;
    }

    /* 表单输入区域可自适应 */
    .layui-input-block {
      min-width: 0; /* 防止超出 */
    }

.layui-textarea {
    min-height: 300px;
    height: auto;
    line-height: 20px;
    padding: 26px 10px;
    line-height: 1.8;
    resize: vertical;
    font-family: Consolas, monospace;
    font-size: 14px;
    line-height: 1.8;
    padding: 10px;
    border-radius: 8px;
    border: 1px solid #e2e2e2;
    background-color: #f9f9f9;
    transition: all 0.3s;
}

    /* 自适应容器内部 */
    body {
      padding: 20px;
    }
  </style>
<div class="layui-fluid">
    <fieldset class="layui-elem-field">
        <legend>网站设置</legend>
        <div class="layui-field-box">
    
   <form class="layui-form" action=""  id="myForm" lay-filter="userForm" name="setOption">
    
             <!-- 网站基本信息 -->
    <fieldset class="layui-elem-field layui-field-title">
        <legend>网站基本信息</legend>
      </fieldset>
      <div class="layui-form-item">
        <label class="layui-form-label">网站名称</label>
        <div class="layui-input-block">
          <input type="text" name="title"  placeholder="title" class="layui-input" value="{%options('title')%}">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">网站地址</label>
        <div class="layui-input-block">
          <input type="text" name="siteUrl"  placeholder="请输入网站域名（如：https://example.com）" class="layui-input" value="{%options('siteUrl')%}">
        </div>
      </div>
       <div class="layui-form-item">
           <label class="layui-form-label">网站首页描述</label>
           <div class="layui-input-block">
               <input type="text" name="siteDes"  placeholder="SEO首页描述" class="layui-input" maxlength="50" value="{%options('siteDes')%}">
           </div>
       </div>
      <style>
      .layui-upload-img {
          border: 1px solid #eee;
          padding: 5px;
          border-radius: 4px;
          background: #fff;
          margin-bottom: 10px;
      }

      #logo-text {
          color: #FF5722;
      }
      </style>
      <div class="layui-form-item">
          <label class="layui-form-label">网站logo</label>
          <div class="layui-input-block">
              <div class="layui-upload">
                  <button type="button" class="layui-btn" id="logo-upload">
                      <i class="layui-icon">&#xe67c;</i>上传图片
                  </button>
                  <div class="layui-upload-list" style="margin-top: 10px;">
                      <img class="layui-upload-img" id="logo-preview" style="max-width: 200px; max-height: 100px;">
                      <p id="logo-text"></p>
                  </div>
                  <input type="hidden" name="logo_url" id="logo-input" value="{%options('logo_url')%}">
              </div>
          </div>
      </div>
      <div class="layui-form-item">
          <label class="layui-form-label">网站图标</label>
          <div class="layui-input-block"> 
              <div class="layui-upload">
                  <button type="button" class="layui-btn" id="favicon-upload">
                      <i class="layui-icon">&#xe67c;</i>上传favicon
                  </button>
                  <div class="layui-upload-list" style="margin-top: 10px;">
                      <img class="layui-upload-img" id="favicon-preview" style="max-width: 32px; max-height: 32px;">
                      <p id="favicon-text"></p>
                  </div>
                  <input type="hidden" name="favicon_ico" id="favicon-input" value="{%options('favicon_ico')%}">
                  <div class="layui-form-mid layui-word-aux">建议上传.ico格式，尺寸32x32像素</div>
              </div>
          </div>
      </div>
       <div class="layui-form-item">
           <label class="layui-form-label">用户的头像图</label>
           <div class="layui-input-block">
               <div class="layui-upload">
                   <button type="button" class="layui-btn" id="avatar-upload">
                       <i class="layui-icon">&#xe67c;</i>上传头像图
                   </button>
                   <div class="layui-upload-list" style="margin-top: 10px;">
                       <img class="layui-upload-img" id="avatar-preview" style="max-width: 32px; max-height: 32px;">
                       <p id="avatar-text"></p>
                   </div>
                   <input type="hidden" name="user_avatar" id="avatar-input" value="{%options('user_avatar')%}">
                   <div class="layui-form-mid layui-word-aux">建议上传.png格式圆头像图，尺寸60x60像素</div>
               </div>
           </div>
       </div>
       <div class="layui-form-item">
           <label class="layui-form-label">图片占位图</label>
           <div class="layui-input-block">
               <div class="layui-upload">
                   <button type="button" class="layui-btn" id="zwimg-upload">
                       <i class="layui-icon">&#xe67c;</i>上传占位图图
                   </button>
                   <div class="layui-upload-list" style="margin-top: 10px;">
                       <img class="layui-upload-img" id="zwimg-preview" style="max-width: 32px; max-height: 32px;">
                       <p id="zwimg-text"></p>
                   </div>
                   <input type="hidden" name="img_zwimg" id="zwimg-input" value="{%options('img_zwimg')%}">
                   <div class="layui-form-mid layui-word-aux">建议上传.png格式，尺寸400x400像素</div>
               </div>
           </div>
       </div>
       <div class="layui-form-item">
           <label class="layui-form-label">广告占位图</label>
           <div class="layui-input-block">
               <div class="layui-upload">
                   <button type="button" class="layui-btn" id="zwad-upload">
                       <i class="layui-icon">&#xe67c;</i>上传占位图图
                   </button>
                   <div class="layui-upload-list" style="margin-top: 10px;">
                       <img class="layui-upload-img" id="zwad-preview" style="max-width: 32px; max-height: 32px;">
                       <p id="zwad-text"></p>
                   </div>
                   <input type="hidden" name="img_zwad" id="zwad-input" value="{%options('img_zwad')%}">
                   <div class="layui-form-mid layui-word-aux">建议上传.png格式，尺寸950x110像素</div>
               </div>
           </div>
       </div>
       <div class="layui-form-item">
        <label class="layui-form-label">应用弹窗数量</label>
        <div class="layui-input-block">
          <input type="text" name="appCenterPopSize"  placeholder="开屏应用弹窗" class="layui-input" value="{%options('appCenterPopSize')%}">
        </div>
      </div>
  
    
      <!-- SEO基本设置 -->
      <fieldset class="layui-elem-field layui-field-title">
        <legend>SEO基本设置</legend>
      </fieldset>
      <div class="layui-form-item">
        <label class="layui-form-label">站点描述</label>
        <div class="layui-input-block">
            <textarea name="description" class="layui-textarea">{%options('description')%}</textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">站点关键词</label>
        <div class="layui-input-block">
            <textarea name="keywords" class="layui-textarea">{%options('keywords')%}</textarea>
        </div>
      </div>
       <div class="layui-form-item">
           <label class="layui-form-label">谷歌分析账号</label>
           <div class="layui-input-block">
               <input type="text" name="google_properties"  placeholder="google" class="layui-input" value="{%options('google_properties')%}">
           </div>
       </div>
      <div class="layui-form-item">
        <label class="layui-form-label">谷歌分析</label>
        <div class="layui-input-block">
            <textarea name="google" class="layui-textarea">{%options('google')%}</textarea>
        </div>
      </div>

       <div class="layui-form-item">
           <label class="layui-form-label">robots.txt</label>
           <div class="layui-input-block">
               <textarea name="robots" class="layui-textarea" placeholder="可使用变量 当前用户访问域名: ${host} ">{%options('robots')%}</textarea>
           </div>
       </div>
       <div class="layui-form-item">
           <label class="layui-form-label">Twitter账号</label>
           <div class="layui-input-block">
               <input type="text" name="twitter_site" placeholder="例如: @your_handle" class="layui-input" value="{%options('twitter_site')%}">
               <div class="layui-form-mid layui-word-aux">填写X平台（Twitter）账号，用于Twitter Cards。暂时没有可留空</div>
           </div>
       </div>
  
      <!-- 中转落地页基本设置 -->
      <fieldset class="layui-elem-field layui-field-title">
        <legend>中转页SEO基本设置</legend>
      </fieldset>
      <div class="layui-form-item">
        <label class="layui-form-label">中转页名称</label>
        <div class="layui-input-block">
          <input type="text" name="zz_title"  placeholder="description" class="layui-input" value="{%options('zz_title')%}">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">中转页地址</label>
        <div class="layui-input-block">
          <input type="text" name="zz_siteUrl" placeholder="description" class="layui-input" value="{%options('zz_siteUrl')%}">
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">中转页描述</label>
        <div class="layui-input-block">
            <textarea name="zz_description" class="layui-textarea">{%options('zz_description')%}</textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">中转页关键词</label>
        <div class="layui-input-block">
            <textarea name="zz_keywords" class="layui-textarea">{%options('zz_keywords')%}</textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">谷歌分析</label>
        <div class="layui-input-block">
            <textarea name="zz_statistical" class="layui-textarea">{%options('zz_statistical')%}</textarea>
        </div>
      </div>
  <fieldset class="layui-elem-field layui-field-title">
        <legend>中转基本信息</legend>
      </fieldset>
        <div class="layui-form-item">
        <label class="layui-form-label">中转名称</label>
        <div class="layui-input-block">
          <input type="text" name="zz_title"  placeholder="请输入网站域名（如：https://example.com）" class="layui-input" value="{%options('zz_title')%}">
        </div>
      </div>
        <div class="layui-form-item">
        <label class="layui-form-label">中转域名</label>
        <div class="layui-input-block">
          <input type="text" name="zz_siteUrl"  placeholder="" class="layui-input" value="{%options('zz_siteUrl')%}">
        </div>
      </div>
       <div class="layui-form-item">
        <label class="layui-form-label">中转logo</label>
        <div class="layui-input-block">
          <div class="layui-upload">
            <button type="button" class="layui-btn" id="zz-logo-upload">
              <i class="layui-icon">&#xe67c;</i>上传中转logo
            </button>
            <div class="layui-upload-list" style="margin-top: 10px;">
              <img class="layui-upload-img" id="zz-logo-preview" style="max-width: 200px; max-height: 100px;">
              <p id="zz-logo-text"></p>
            </div>
            <input type="hidden" name="zz_logo" id="zz-logo-input" value="{%options('zz_logo')%}">
          </div>
        </div>
      </div>
      
       <div class="layui-form-item">
        <label class="layui-form-label">页面邮箱</label>
        <div class="layui-input-block">
          <input type="text" name="zz_email"  placeholder="" class="layui-input" value="{%options('zz_email')%}">
        </div>
      </div>
       <div class="layui-form-item">
           <label class="layui-form-label">正式直连1</label>
           <div class="layui-input-block">
               <input type="text" name="zz_line"  placeholder="" class="layui-input" value="{%options('zz_line')%}">
               <div class="layui-form-mid layui-word-aux">多个域名用英文逗号分隔(,)</div>
           </div>
       </div>
       <div class="layui-form-item">
        <label class="layui-form-label">备用直连2</label>
        <div class="layui-input-block">
          <input type="text" name="zz_backup_line"  placeholder="" class="layui-input" value="{%options('zz_backup_line')%}">
            <div class="layui-form-mid layui-word-aux">多个域名用英文逗号分隔(,)</div>
        </div>
      </div>
       <div class="layui-form-item">
           <label class="layui-form-label">中转页底部链接</label>
           <div class="layui-input-block">
               <textarea name="zz_bottom_link" class="layui-textarea">{%options('zz_bottom_link')%}</textarea>
           </div>
       </div>
      <!-- 提交按钮 -->
      <div class="layui-form-item">
        <div class="layui-input-block">
          <button class="layui-btn" lay-submit lay-filter="submitForm">提交修改</button>
          <button type="reset" class="layui-btn layui-btn-primary">重置</button>
        </div>
      </div>
  
          </form>
    
        </div>
      </fieldset>
</div>

{%include file="fooler.tpl"%}
<script>
  layui.use(['form','upload'], function () {
    const form = layui.form;
    const $ = layui.$;
    const upload = layui.upload;
    const originalData = {};
    const logoUrl = $('#logo-input').val();
    if (logoUrl) {
     $('#logo-preview').attr('src', logoUrl);
    }
    function saveInitialValues() {
      $('#myForm [name]').each(function () {
        const $el = $(this);
        const name = $el.attr('name');
        if (name) {
          let val = $el.val();
          if (val === null) val = ''; 
          originalData[name] = val.trim(); 
        }
      });
    }
  
 
    $(document).ready(function () {
      saveInitialValues();
    });
      upload.render({
        elem: '#logo-upload',
        url: '{%url("upload/uploadLocal")%}',
        accept: 'images',
        acceptMime: 'image/*',
        done: function(res) {
          console.log(res);
            if (res.code === 200) {
                $('#logo-preview').attr('src', res.data.url);
                $('#logo-input').val(res.data.url);
                $('#logo-text').html('');
                layer.msg('上传成功');
            } else {
                $('#logo-text').html('上传失败：' + res.msg);
            }
        },
        error: function() {
            $('#logo-text').html('上传失败，请重试');
        }
    });

    const faviconUrl = $('#favicon-input').val();
    if (faviconUrl) {
        $('#favicon-preview').attr('src', faviconUrl);
    }

      const avatarUrl = $('#avatar-input').val();
      if (avatarUrl) {
          $('#avatar-preview').attr('src', avatarUrl);
      }
      const zwimgUrl = $('#zwimg-input').val();
      if (zwimgUrl) {
          $('#zwimg-preview').attr('src', zwimgUrl);
      }
      const zwadUrl = $('#zwad-input').val();
      if (zwadUrl) {
          $('#zwad-preview').attr('src', zwadUrl);
      }

    upload.render({
        elem: '#favicon-upload',
        url: '{%url("upload/uploadLocal")%}',
        accept: 'images',
        acceptMime: 'image/*',
        done: function(res) {
            if (res.code === 200) {
                $('#favicon-preview').attr('src', res.data.url);
                $('#favicon-input').val(res.data.url);
                $('#favicon-text').html('');
                layer.msg('上传成功');
            } else {
                $('#favicon-text').html('上传失败：' + res.msg);
            }
        },
        error: function() {
            $('#favicon-text').html('上传失败，请重试');
        }
    });

      upload.render({
          elem: '#avatar-upload',
          url: '{%url("upload/uploadLocal")%}',
          accept: 'images',
          acceptMime: 'image/*',
          done: function(res) {
              if (res.code === 200) {
                  $('#avatar-preview').attr('src', res.data.url);
                  $('#avatar-input').val(res.data.url);
                  $('#avatar-text').html('');
                  layer.msg('上传成功');
              } else {
                  $('#avatar-text').html('上传失败：' + res.msg);
              }
          },
          error: function() {
              $('#avatar-text').html('上传失败，请重试');
          }
      });
      upload.render({
          elem: '#zwimg-upload',
          url: '{%url("upload/uploadLocal")%}',
          accept: 'images',
          acceptMime: 'image/*',
          done: function(res) {
              if (res.code === 200) {
                  $('#zwimg-preview').attr('src', res.data.url);
                  $('#zwimg-input').val(res.data.url);
                  $('#zwimg-text').html('');
                  layer.msg('上传成功');
              } else {
                  $('#zwimg-text').html('上传失败：' + res.msg);
              }
          },
          error: function() {
              $('#zwimg-text').html('上传失败，请重试');
          }
      });
      upload.render({
          elem: '#zwad-upload',
          url: '{%url("upload/uploadLocal")%}',
          accept: 'images',
          acceptMime: 'image/*',
          done: function(res) {
              if (res.code === 200) {
                  $('#zwad-preview').attr('src', res.data.url);
                  $('#zwad-input').val(res.data.url);
                  $('#zwad-text').html('');
                  layer.msg('上传成功');
              } else {
                  $('#zwad-text').html('上传失败：' + res.msg);
              }
          },
          error: function() {
              $('#zwad-text').html('上传失败，请重试');
          }
      });

     const zzLogoUrl = $('#zz-logo-input').val();
      if (zzLogoUrl) {
        $('#zz-logo-preview').attr('src', zzLogoUrl);
      }

      upload.render({
        elem: '#zz-logo-upload',
        url: '{%url("upload/uploadLocal")%}',
        accept: 'images',
        acceptMime: 'image/*',
        done: function(res) {
          if (res.code === 200) {
            $('#zz-logo-preview').attr('src', res.data.url);
            $('#zz-logo-input').val(res.data.url);
            $('#zz-logo-text').html('');
            layer.msg('上传成功');
          } else {
            $('#zz-logo-text').html('上传失败：' + res.msg);
          }
        },
        error: function() {
          $('#zz-logo-text').html('上传失败，请重试');
        }
      });

    form.on('submit(submitForm)', function () {
      const changedData = {};
  
        $('#myForm [name]').each(function () {
          const $el = $(this);
          const name = $el.attr('name');
            if (!name || $el.attr('type') === 'file' || $el.hasClass('layui-upload-file')) return;

          let currentValue = $el.val();
          if (currentValue === null) currentValue = '';
          currentValue = currentValue.trim();
    
          const originalValue = originalData[name];
    
          if (currentValue !== originalValue) {
            changedData[name] = currentValue;
          }
        });

        if (Object.keys(changedData).length === 0) {
          Util.msgOk('未提交任何修改');
          return false; // 阻止表单默认提交
        }
       
      $.post('{%url('set_option')%}', changedData, function(res) {
          console.log(res);
        if (res.code === 0 ){

          Util.msgOk(res.msg);
          
        }else {
          Util.msgErr(res.msg);
        }

      });
      
      return false;
      
    });
   
  });
  </script>
  
  
  