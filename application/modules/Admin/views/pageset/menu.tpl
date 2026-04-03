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
    
    /* 针对特别长的标签，进一步增加宽度 */
    .layui-form-item.long-label .layui-form-label {
      width: 160px !important;
      /* margin-left = 标签宽度(160px) + 左padding(15px) + 右padding(10px) = 185px */
    }
    .layui-form-item.long-label .layui-input-block {
      margin-left: 185px !important;
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
        <legend>导航设置</legend>
        <div class="layui-field-box">
            <form class="layui-form" action=""  id="myForm" lay-filter="userForm" name="setOption">
      <div class="layui-form-item">
        <label class="layui-form-label">导航限制数量</label>
            <div class="layui-input-inline">
                <input type="number" name="maxNavbarMenuNum" value="{%$maxNavbarMenuNum|default:5%}" class="layui-input" lay-verify="number">
            </div>
            <div class="layui-form-mid layui-word-aux">此项为头部显示最多的分类导航的最大数量</div>
      </div>
      <div class="layui-form-item">
          <label class="layui-form-label">顶部图标导航</label>
          <div class="layui-input-block">
            
              <textarea name="headNav" class="layui-textarea" >{%$headNav%}</textarea>
          </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">底部导航图标</label>
        <div class="layui-input-block">
          
          <textarea name="footMenu"  class="layui-textarea">{%$footMenu%}</textarea>
        </div>
      </div>
       
      <div class="layui-form-item">
        <label class="layui-form-label">底部导航</label>
        <div class="layui-input-block">
          <textarea name="footLink" class="layui-textarea">{%$footLink%}</textarea>
        </div>
      </div>
       <div class="layui-form-item">
        <label class="layui-form-label">底部联系导航</label>
        <div class="layui-input-block">
          <textarea name="contactLink" class="layui-textarea">{%$contactLink%}</textarea>
        </div>
      </div>
      <div class="layui-form-item long-label">
        <label class="layui-form-label">页脚法律声明导航</label>
        <div class="layui-input-block">
          <textarea name="legalLinks" class="layui-textarea">{%$legalLinks%}</textarea>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">友情推荐链接</label>
        <div class="layui-input-block">
          <textarea name="friendLinks" class="layui-textarea" placeholder='[{"name":"链接名称","link":"链接地址","target":"_blank","rel":"sponsored"}]'>{%$friendLinks%}</textarea>
          <div class="usage-tips">
            <i class="layui-icon layui-icon-tips"></i>
            友情推荐链接，JSON格式。支持字段：name(名称)、link(链接)、target(打开方式，默认_blank)、rel(rel属性，如sponsored)
          </div>
        </div>
      </div>
      <div class="layui-form-item">
        <label class="layui-form-label">底部描述</label>
        <div class="layui-input-block">
          <textarea name="footDesc" class="layui-textarea" placeholder="请输入网站底部描述信息">{%$footDesc%}</textarea>
          <div class="usage-tips">
            <i class="layui-icon layui-icon-tips"></i>
            用于设置网站底部的描述信息，支持HTML标签
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
    </div>
    </form>
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
       
      $.post('{%url('set_nav')%}', changedData, function(res) {
          console.log(res);
        if (res.code === 0){
          Util.msgOk(res.msg); 
        }else {
          Util.msgErr(res.msg);
        }

      });
      
      return false;
      
    });
   
  });
  </script>
  
  
  