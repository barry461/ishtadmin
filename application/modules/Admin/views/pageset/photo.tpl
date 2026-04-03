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
        <legend>网站图片配置</legend>
        <div class="layui-field-box">
    
   <form class="layui-form" action=""  id="myForm" lay-filter="userForm" name="setOption">
    
             <!-- 网站基本信息 -->
    <fieldset class="layui-elem-field layui-field-title">
        <legend>添加主屏幕教程图</legend>
      </fieldset>

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
           <label class="layui-form-label">IOS教程图</label>
           <div class="layui-input-block">
               <table >
                   <tr>
                       {%foreach $techIos as $k=>$tv%}
                       <td>
                           <div class="layui-upload">
                               <button type="button" class="layui-btn" id="techios-upload{%$k%}">
                                   <i class="layui-icon">&#xe67c;</i>上传教程图{%$k+1%}
                               </button>&nbsp;&nbsp;
                               <div class="layui-upload-list" style="margin-top: 10px;">
                                   <img class="layui-upload-img" alt="上传教程图" id="techios-preview{%$k%}" src="{%$tv%}" style="max-width: 32px; max-height: 32px;">
                                   <p id="techios-text{%$k%}"></p>
                               </div>
                               <input type="hidden" name="img_techios[]" id="techios-input{%$k%}" value="{%$tv%}">
                           </div>
                       </td>
                       {%/foreach%}
                   </tr>
                   <tr><td colspan="5"><div class="layui-form-mid layui-word-aux">建议上传.png格式，尺寸275x238像素</div></td></tr>
                   <tr>
                       {%foreach $techIosTxt as $k=>$tv%}
                       <td>
                           <input type="text" name="txt_techios[]" value="{%$tv%}" placeholder="描述文本" maxlength="200" class="layui-input">
                       </td>
                       {%/foreach%}
                   </tr>
                   <tr>
                       {%foreach $techIosPrompt as $k=>$tv%}
                           <td>
                               <input type="text" name="prompt_techios[]" value="{%$tv%}" placeholder="提示语" maxlength="100" class="layui-input" style="margin-bottom: 5px;">
                           </td>
                       {%/foreach%}
                   </tr>
               </table>
           </div>
       </div>
       <div class="layui-form-item">
           <label class="layui-form-label">Android教程图</label>
           <div class="layui-input-block">
               <table >
                   <tr>
                       {%foreach $techAnd as $k=>$tv%}
                       <td>
                           <div class="layui-upload">
                               <button type="button" class="layui-btn" id="techand-upload{%$k%}">
                                   <i class="layui-icon">&#xe67c;</i>上传教程图{%$k+1%}
                               </button>&nbsp;&nbsp;
                               <div class="layui-upload-list" style="margin-top: 10px;">
                                   <img class="layui-upload-img" alt="上传教程图" id="techand-preview" src="{%$tv%}" style="max-width: 32px; max-height: 32px;">
                                   <p id="techand-text{%$k%}"></p>
                               </div>
                               <input type="hidden" name="img_techand[]" id="techand-input{%$k%}" value="{%$tv%}">
                           </div>
                       </td>
                       {%/foreach%}
                   </tr>
                   <tr><td colspan="5"><div class="layui-form-mid layui-word-aux">建议上传.png格式，尺寸275x238像素</div></td></tr>
                   <tr>
                       {%foreach $techAndTxt as $k=>$tv%}
                       <td>
                           <input type="text" name="txt_techand[]" value="{%$tv%}" placeholder="描述文本" maxlength="200" class="layui-input">
                       </td>
                       {%/foreach%}
                   </tr>
                   <tr>
                       {%foreach $techAndPrompt as $k=>$tv%}
                           <td>
                               <input type="text" name="prompt_techand[]" value="{%$tv%}" placeholder="提示语" maxlength="100" class="layui-input" style="margin-bottom: 5px;">
                           </td>
                       {%/foreach%}
                   </tr>
               </table>
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

      {%foreach $techIos as $k=>$tv%}
      upload.render({
          elem: '#techios-upload{%$k%}',
          url: '{%url("upload/uploadLocal")%}',
          accept: 'images',
          acceptMime: 'image/*',
          done: function(res) {
              if (res.code === 200) {
                  $('#techios-preview{%$k%}').attr('src', res.data.url);
                  $('#techios-input{%$k%}').val(res.data.url);
                  $('#techios-text{%$k%}').html('');
                  layer.msg('上传成功');
              } else {
                  $('#techios-text{%$k%}').html('上传失败：' + res.msg);
              }
          },
          error: function() {
              $('#techios-text{%$k%}').html('上传失败，请重试');
          }
      });
      {%/foreach%}

      {%foreach $techAnd as $k=>$tv%}
      upload.render({
          elem: '#techand-upload{%$k%}',
          url: '{%url("upload/uploadLocal")%}',
          accept: 'images',
          acceptMime: 'image/*',
          done: function(res) {
              if (res.code === 200) {
                  $('#techand-preview{%$k%}').attr('src', res.data.url);
                  $('#techand-input{%$k%}').val(res.data.url);
                  $('#techand-text{%$k%}').html('');
                  layer.msg('上传成功');
              } else {
                  $('#techand-text{%$k%}').html('上传失败：' + res.msg);
              }
          },
          error: function() {
              $('#techand-text{%$k%}').html('上传失败，请重试');
          }
      });
      {%/foreach%}

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
       
      $.post('{%url('set_nav')%}', changedData, function(res) {
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
  
  
  