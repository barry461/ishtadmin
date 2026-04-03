{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>
    /* 不换行 - 超过6字的标签一行显示 */
    .layui-form-label {
      white-space: nowrap !important;
      width: 140px !important; /* 固定标签宽度，防止文字被遮挡 */
      padding: 9px 15px; /* 保持Layui默认padding */
      padding-right: 10px; /* 增加右侧内边距，确保文字不贴边 */
      box-sizing: content-box; /* 使用content-box，宽度不包括padding */
    }

    /* 表单输入区域可自适应 */
    .layui-input-block {
      min-width: 0; /* 防止超出 */
      /* margin-left = 标签宽度(140px) + 左padding(15px) + 右padding(10px) = 165px */
      margin-left: 165px !important;
    }

    /* 自适应容器内部 */
    body {
      padding: 20px;
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
  </style>
<div class="layui-fluid">
    <fieldset class="layui-elem-field">
        <legend>公共页面设置</legend>
        <div class="layui-field-box">

      
      <form class="layui-form" action=""  id="myForm" lay-filter="userForm" name="setOption">
      
      <!-- 1. 首页底部内容 -->
      <div class="layui-form-item">
        <label class="layui-form-label">首页底部内容</label>
        <div class="layui-input-block">
            <textarea name="footDesc" class="layui-textarea">{%$footDesc%}</textarea>
        </div>
      </div>
      
      <!-- 2. 底部版权 -->
      <div class="layui-form-item">
        <label class="layui-form-label">底部版权</label>
        <div class="layui-input-block">
            <textarea name="footer_copyright" class="layui-textarea" placeholder="请输入网站底部版权信息">{%$footerCopyright%}</textarea>
        </div>
      </div>
      
      <!-- 3. 文章详情页设置 -->
      <fieldset class="layui-elem-field layui-field-title">
        <legend>文章详情页设置</legend>
      </fieldset>
      
      <!-- 3.1 分享文案域名 -->
      <div class="layui-form-item">
        <label class="layui-form-label">分享文案域名</label>
        <div class="layui-input-block">
          <input type="text" name="share_domian" placeholder="点击分享给色友" class="layui-input" value="{%options('share_domian')%}">
        </div>
      </div>
      
      <!-- 3.3 顶部追加内容 -->
      <div class="layui-form-item">
        <label class="layui-form-label">顶部追加内容</label>
        <div class="layui-input-block">
          <textarea name="before_append" class="layui-textarea">{%options('before_append')%}</textarea>
        </div>
      </div>
      
      <!-- 3.4 底部追加内容 -->
      <div class="layui-form-item">
        <label class="layui-form-label">底部追加内容</label>
        <div class="layui-input-block">
            <textarea name="article_bottom_content" class="layui-textarea" placeholder="请输入文章详情页底部要追加的内容，支持HTML">{%$articleBottomContent%}</textarea>
        </div>
      </div>
      
      <!-- 3.5 底部公告内容 -->
      <div class="layui-form-item">
        <label class="layui-form-label">底部公告内容</label>
        <div class="layui-input-block">
            <textarea name="content_after" class="layui-textarea">{%options('content_after')%}</textarea>
        </div>
      </div>
      
      <div class="layui-form-item layui-form-text">
        <label class="layui-form-label">使用说明</label>
        <div class="layui-input-block">
          <div class="layui-text" style="color: #999; font-size: 12px; line-height: 1.6;">
            <p>• 以上内容支持HTML标签，可以添加链接、图片等</p>
            <p>• 顶部追加内容：显示在文章内容之前</p>
            <p>• 底部追加内容：显示在文章内容之后</p>
            <p>• 底部公告内容：显示在文章尾部的官方公告位置</p>
            <p>• 留空则不显示任何内容</p>
          </div>
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
  layui.use(['form'], function () {
    const form = layui.form;
    const $ = layui.$;
  
    const originalData = {};
  
    function saveInitialValues() {
      $('#myForm [name]').each(function () {
        const $el = $(this);
        const name = $el.attr('name');
        if (name) {
          let val = $el.val();
          if (val === null) val = ''; // 防止 null
          originalData[name] = val.trim(); // 去除首尾空格
        }
      });
    }
  
 
    $(document).ready(function () {
      saveInitialValues();
    });
  
   
    form.on('submit(submitForm)', function () {
      const changedData = {};
  
        $('#myForm [name]').each(function () {
          const $el = $(this);
          const name = $el.attr('name');
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
  
  
  