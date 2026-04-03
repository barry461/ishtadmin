{%include file="header.tpl"%}
<style>
    .btn-row {
        display: flex;
        gap: 6px;
        margin-bottom: 6px;
    }
    /* 标题和分类字段样式 */
    .title-wrapper {
        display: flex;
        align-items: flex-start;
        gap: 8px;
        min-width: 0;
        max-width: 100%;
    }
    
    .title-cell {
        white-space: normal !important;
        word-wrap: break-word;
        word-break: break-all;
        flex: 1;
        min-width: 0;
        line-height: 1.4;
        text-decoration: none;
    }
    
    .title-cell:hover {
        color: #1E9FFF !important;
        text-decoration: underline;
    }
    .category-cell {
        white-space: normal !important;
        word-wrap: break-word;
        max-width: 120px;
        line-height: 1.4;
    }
    /* 操作栏样式优化 */
    .operate-toolbar {
        min-width: 260px;
        white-space: nowrap;
    }
    .operate-toolbar .btn-row {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        margin-bottom: 4px;
    }
    .operate-toolbar .layui-btn-xs {
        margin: 0;
        padding: 0 8px;
        font-size: 12px;
        white-space: nowrap;
        flex-shrink: 0;
    }
    /* 表格横向滚动支持 */
    .layui-card-body {
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
    }
    .layui-table {
        min-width: 100%;
        table-layout: auto;
    }
    .layui-table-view {
        overflow-x: auto;
    }
    /* 标题列单元格样式 - 支持换行和flex布局 */
    .layui-table td[data-field="aa"],
    .layui-table td[data-field="title"] {
        white-space: normal !important;
        word-wrap: break-word;
    }
    /* 状态标签样式 */
    .status-label {
        font-size: 12px;
        color: #FF5722;
        flex-shrink: 0;
        white-space: nowrap;
        line-height: 1.4;
        align-self: flex-start;
    }
    .status-label.waiting {
        color: #FF5722;
    }
    .status-label.draft {
        color: #999;
    }
</style>
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>.layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">cid</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[cid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">作者名字</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[author_name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="like[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]">
                                    <option value="">全部</option>
                                    {%html_options selected=data_get($get,'where.status') options=ContentsModel::STATUS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">分类</label>
                            <div class="layui-input-block">
                                <select name="category_id">
                                    <option value="">全部</option>
                                    <option value="no_category" {%if data_get($get,'category_id') == 'no_category'%}selected{%/if%}>无分类</option>
                                    {%html_options selected=data_get($get,'category_id')
                                    options=$category_options%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">首页显示</label>
                            <div class="layui-input-block">
                                <select name="where[is_home]">
                                    <option value="">全部</option>
                                    {%html_options selected=data_get($get,'where.is_home') options=ContentsModel::IS_HOME_TICP%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">热搜</label>
                            <div class="layui-input-block">
                                <select name="where[hotSearch]">
                                    <option value="">全部</option>
                                    <option value="1">是</option>
                                    <option value="0">否</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">显示浏览数</label>
                            <div class="layui-input-block">
                                <select name="orderBy[fake_view]">
                                    <option value="">无</option>
                                    <option value="desc">降序</option>
                                    <option value="asc">升序</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">真实浏览数</label>
                            <div class="layui-input-block">
                                <select name="orderBy[view]">
                                    <option value="">无</option>
                                    <option value="desc">降序</option>
                                    <option value="asc">升序</option>
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">筛选</label>
                            <div class="layui-input-block">
                                <select name="custormsort_id">
                                    <option value="">选择</option>
                                    {%html_options selected=data_get($get,'where.custormsort_id')
                                    options=$customsort_options%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">发布时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created"%}
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>


                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, limit:50, limits:[10,20,30,40,50,60,70,80,90,100], id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox', width: 50}"></th>
                            <th lay-data="{field:'cid',width: 80}">Cid</th>
                            <th lay-data="{field:'aa' ,templet:'#a5', width: 250}">标题</th>
                            <th lay-data="{field:'author_name',width: 120}">作者</th>
                            <th lay-data="{field:'category_str', width: 120, templet:'#category_template', sort: true}">分类</th>
                            <th lay-data="{field:'type',width: 160 ,templet:'#a1',sort: true}">发布时间</th>
                            {%foreach $customsort_options as $field=>$filed_title%}
                            <th lay-data="{field:'{%$field%}',width: 100, edit: 'text'}">{%$filed_title%}</th>
                            {%/foreach%}
                            <th lay-data="{field:'view',width: 90, edit: 'text'}">浏览量</th>
                            <th lay-data="{field:'title',width: 100 ,templet:'#a3'}">属性</th>
                            <th lay-data="{field:'type',width: 110 ,templet:'#a2'}">状态</th>
                            <th lay-data="{field:'modified',width: 130}">更新时间</th>
                            <th lay-data="{fixed: 'right',width: 280 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    
                    <!-- 标题模板 - 支持完整显示和自动换行 -->
                    <script type="text/html" id="a5">
                        <div class="title-wrapper">
                            <a href="{%options('siteUrl')%}archives/{{=d.cid}}/" target="_blank" class="title-cell" style="color: #467B96; cursor: pointer;">{{=d.title}}</a>
                            {{# if(d.status == 'waiting') { }}
                            <span class="status-label waiting">待审核</span>
                            {{# } else if(d.status == 'draft') { }}
                            <span class="status-label draft">草稿</span>
                            {{# } }}
                        </div>
                    </script>
                    
                    <!-- 分类模板 - 支持完整显示和自动换行 -->
                    <script type="text/html" id="category_template">
                        <div class="category-cell">{{=d.category_str}}</div>
                    </script>
                  
                   <script type="text/html" id="a3">
                        <div style="margin: 0; line-height: 1.3;">
                        {{# if(d.allowComment) { }}评论{{# } }}
                        {{# if(d.allowFeed) { }}聚合{{# } }}
                        {{# if(d.allowPing) { }}引用{{# } }}
                        </div>
                    </script>
                    <script type="text/html" id="a2">
                        <div style="margin: 0; line-height: 1.3;">
                        {{d.type_str}}<br>
                        {{d.status_str}}<br>
                        {{# if(d.is_slice == 1) { }}已切片{{# } }}
                        </div>
                    </script>
                    
                    <!-- 时间模板 - 只显示发布时间 -->
                    <script type="text/html" id="a1">
                        <div style="margin: 0; line-height: 1.3;">
                            {{# if(d.published_at) { }}
                            {{d.published_at}}
                            {{# } else { }}
                            {{d.created}}
                            {{# } }}
                        </div>
                    </script>
                    
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="cid">批量下架
                            </button> 
                            <button class="layui-btn layui-btn-sm" lay-event="updateStatus" data-pk="cid">状态批量设置</button>
                            
                            <button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="deleteArticles" data-pk="cid">批量删除</button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <div class="btn-row">
                                <button class="layui-btn layui-btn-xs" lay-event="edit">修改</button>
                                {{# if(d.status == 'publish') { }}
                                <button class="layui-btn layui-btn-xs layui-btn-danger" data-pk="{{=d.cid}}" lay-event="removed">下架</button>
                                {{# } else { }}
                                <button class="layui-btn layui-btn-xs layui-btn-normal" data-pk="{{=d.cid}}" lay-event="publish">公开</button>
                                {{# } }}
                                <button class="layui-btn layui-btn-xs layui-btn-warm" lay-event="clear_cache" data-cid="{{=d.cid}}">清理缓存</button>
                            </div>
                            <div class="btn-row">
                                <button class="layui-btn layui-btn-xs" lay-event="add_comment" data-cid="{{=d.cid}}">添加评论</button>
                                {{# if(d.is_home == 1) { }}
                                <button class="layui-btn layui-btn-xs" lay-event="toggle_home">首页不显示</button>
                                {{# } else { }}
                                <button class="layui-btn layui-btn-xs" lay-event="toggle_home">首页显示</button>
                                {{# } }}
                                {{# if(d.hotSearch == '1' || d.hotSearch == 1) { }}
                                <button class="layui-btn layui-btn-xs layui-btn-danger" lay-event="setHot">撤销热搜</button>
                                {{# } else { }}
                                <button class="layui-btn layui-btn-xs layui-btn-warm" lay-event="setHot">一键热搜</button>
                                {{# } }}                           
                            </div>
                        </div>
                    </script>

                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/html" class="data-dialog" id="special-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <input type="hidden" name="cid" value="{{=d.cid}}">

        <div class="layui-form-item">
            <label class="layui-form-label">标题：</label>
            <div class="layui-input-block">

                <input placeholder="标题" name="title"
                       value="{{=d.title }}" class="layui-input">

            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">发布时间：</label>
                <div class="layui-input-inline">
                    <input type="text" name="created" class="layui-input x-date-time" placeholder="yyyy-MM-dd HH:mm:ss" value="{{=d.created}}">
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">分类：</label>
            <div class="layui-input-block">
                {{#  layui.each(themes, function(index, item){ }}
                <input type="checkbox" name="category_ids[]" lay-skin="primary" title="{{item.name}}" value="{{item.id}}"
                        {{# if ((d.category_ids ? d.category_ids:[]).indexOf(item.id)!=-1) { }}
                       checked=""
                       {{#  } }}
                >
                {{#  }); }}
            </div>
        </div>

        <div class="layui-form-item">
            <label class="layui-form-label">标签：</label>
            <div class="layui-input-block">

                <input placeholder="标签" name="tags"
                       value="{{=d.tags_str }}" class="layui-input">

            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">封面图：</label>
                <div class="layui-input-inline">
                    {%html_upload name='banner' src='banner' value='banner'%}
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">是否热搜 ：</label>
                <div class="layui-input-inline">
                    <select name="hotSearch" data-value="{{=d.hotSearch }}">
                        {%html_options options=[0 => '否', 1=> '是']%}
                    </select>
                </div>
            </div>
            
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.cid}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

<script type="text/html" class="data-dialog" id="setType">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <input type="hidden" name="cid" value="{{=d.cid}}">
        <div class="layui-form-item">
            <label class="layui-form-label">类型:</label>
            <div class="layui-input-block">
                <select name="type" id="">
                    {%html_options options=ContentsModel::TYPE%}
                </select>
            </div>
        </div>
        <div class="layui-form-item">
            <label class="layui-form-label">短剧/大事件ID:</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="短剧/大事件ID" name="sid" class="layui-input">
            </div>
        </div>
    </form>
</script>

<script type="text/html" class="data-dialog" id="batchUpdateStatus">
    <form class="layui-form form-dialog" action="" lay-filter="form-save" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">状态:</label>
            <div class="layui-input-block">
                <select name="status" id="batch-update-status">
                    {%html_options options=ContentsModel::STATUS%}
                </select>
            </div>
        </div>
    </form>
</script>

<script type="text/html" id="batchAddComment">
    <form class="layui-form form-dialog" action="" lay-filter="batch-comment-form" style="margin-top: 20px">
        <div class="layui-form-item">
            <label class="layui-form-label">评论的用户:</label>
            <div class="layui-input-block">
                <input type="text" name="user_nicknames" placeholder="哪些用户进行评论,多个用英文逗号分隔" 
                       value="" class="layui-input" lay-verify="required">
                <div class="layui-form-mid layui-word-aux">多个用户昵称用英文逗号分隔</div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">文章的编号:</label>
            <div class="layui-input-block">
                <input type="text" name="article_ids" placeholder="到哪些文章下面进行评论。多个用英文逗号分隔" 
                       value="{{=d.default_cid || ''}}" class="layui-input" lay-verify="required">
                <div class="layui-form-mid layui-word-aux">多个文章ID用英文逗号分隔</div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">评论的内容:</label>
            <div class="layui-input-block">
                <textarea name="comment_contents" placeholder="评论的数据,一行一个" 
                          class="layui-textarea" style="min-height: 200px;" lay-verify="required"></textarea>
                <div class="layui-form-mid layui-word-aux">每行一条评论内容</div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">评论的时间:</label>
            <div class="layui-input-block">
                <div class="layui-input-inline" style="width: 100px;">
                    <input type="number" name="time_from" placeholder="0" value="0" 
                           class="layui-input" lay-verify="required|number" min="0">
                </div>
                <div class="layui-form-mid" style="padding: 0 10px;">小时</div>
                <div class="layui-form-mid" style="padding: 0 5px;">~</div>
                <div class="layui-input-inline" style="width: 100px;">
                    <input type="number" name="time_to" placeholder="2" value="2" 
                           class="layui-input" lay-verify="required|number" min="0">
                </div>
                <div class="layui-form-mid" style="padding: 0 10px;">小时</div>
                <div class="layui-form-mid layui-word-aux" style="padding-left: 10px;">
                    从文章发布时候后的第 <span id="time-from-display">0</span> 小时 ~ 第 <span id="time-to-display">2</span> 小时
                </div>
            </div>
        </div>
        
        <div class="layui-form-item">
            <label class="layui-form-label">是否置顶:</label>
            <div class="layui-input-block">
                <input type="checkbox" name="is_top" value="1" title="置顶" lay-skin="primary">
            </div>
        </div>
        
        <div class="layui-form-item layui-hide">
            <button type="button" class="layui-btn submit" lay-submit lay-filter="batch-comment-form">提交</button>
        </div>
    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    var themes = eval({%$theme_json%});
    var gis_reload = false;
    window.reload_test = function (is_reload){
        gis_reload = is_reload
    }
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

                table.on('tool(table-toolbar)', function (obj) {
                    //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                    var data = obj.data,
                        layEvent = obj.event,
                        that = this;
                    switch (layEvent) {
                        case 'removed':
                            layer.confirm('确定下架该内容吗?', function(index) {
                                layer.close(index);
                                updateStatus([data.cid], 'removed', '下架',table);
                            });
                            break;
                            
                        // 处理上架操作    
                        case 'publish':
                            layer.confirm('确定上架该内容吗?', function(index) {
                                layer.close(index);
                                updateStatus([data.cid], 'publish', '上架',table);
                            });
                            break;

                        case 'app_hide':
                            layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('app_hide')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'clear_cache':
                            layer.confirm('确认操作嘛?', function (index) {
                                console.log(data.cid)
                                layer.close(index);
                                $.post("{%url('clear_by_id')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'add_comment':
                            let currentCid = data.cid;
                            let batchCommentTemplate = $('#batchAddComment').html();
                            let batchCommentContent = laytpl(batchCommentTemplate).render({default_cid: currentCid});
                            
                            let batchCommentLayerIndex = layer.open({
                                type: 1,
                                title: '批量添加评论',
                                area: [`${document.body.clientWidth-300}px`, 'auto'],
                                content: batchCommentContent,
                                btn: ['确定', '取消'],
                                yes: function(index, layero) {
                                    // 触发表单提交
                                    $(layero).find('button[lay-submit]').click();
                                    return false; // 阻止默认关闭
                                },
                                success: function(layero, index) {
                                    // 设置默认文章ID
                                    $(layero).find('input[name="article_ids"]').val(currentCid);
                                    
                                    // 更新时间显示
                                    $(layero).find('input[name="time_from"]').on('input', function() {
                                        $(layero).find('#time-from-display').text($(this).val() || '0');
                                    });
                                    $(layero).find('input[name="time_to"]').on('input', function() {
                                        $(layero).find('#time-to-display').text($(this).val() || '0');
                                    });
                                    
                                    // 绑定表单提交事件
                                    form.on('submit(batch-comment-form)', function(formData) {
                                        let formFields = formData.field;
                                        let userNicknames = (formFields.user_nicknames || '').trim();
                                        let articleIds = (formFields.article_ids || '').trim();
                                        let commentContents = (formFields.comment_contents || '').trim();
                                        let timeFrom = parseInt(formFields.time_from) || 0;
                                        let timeTo = parseInt(formFields.time_to) || 0;
                                        let isTop = formFields.is_top === '1' ? 1 : 0;
                                        
                                        // 表单验证
                                        if (!userNicknames || !articleIds || !commentContents) {
                                            Util.msgErr('请填写完整信息');
                                            return false;
                                        }
                                        
                                        if (timeFrom < 0 || timeTo < 0 || timeFrom > timeTo) {
                                            Util.msgErr('时间范围设置不正确');
                                            return false;
                                        }
                                        
                                        // 提交数据
                                        let postData = {
                                            user_nicknames: userNicknames,
                                            article_ids: articleIds,
                                            comment_contents: commentContents,
                                            time_from: timeFrom,
                                            time_to: timeTo,
                                            is_top: isTop
                                        };
                                        
                                        // 提交前禁用确定按钮并显示加载状态
                                        let $btn = $(layero).find('.layui-layer-btn0');
                                        let originalText = $btn.text();
                                        $btn.addClass('layui-btn-disabled').text('提交中...').prop('disabled', true);
                                        
                                        // 发送请求
                                        $.post("{%url('batchAddComments')%}", postData)
                                            .then(function(res) {
                                                layer.close(index);
                                                if (res.code === 0) {
                                                    Util.msgOk(res.msg || '批量添加评论成功');
                                                } else {
                                                    Util.msgErr(res.msg || '批量添加评论失败');
                                                }
                                            })
                                            .fail(function(xhr, status, error) {
                                                layer.close(index);
                                                Util.msgErr('请求失败，请重试: ' + error);
                                            })
                                            .always(function() {
                                                $btn.removeClass('layui-btn-disabled').text(originalText).prop('disabled', false);
                                            });
                                        
                                        return false; // 阻止表单默认提交
                                    });
                                    
                                    // 渲染表单
                                    form.render(null, 'batch-comment-form');
                                }
                            });
                            break;
                        case 'web_show':
                            layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('web_show')%}", {"id": data.cid})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                            break;
                        case 'edit':
                                layer.open({
                                type: 2,
                                content:'{%url('txt')%}?id='+data.cid,
                                area:['100%','100%'],
                                title:false,
                                closeBtn:0,
                                end:function() {
                                    if (gis_reload) {
                                        table.reload('test');
                                    }
                                }
                            });
                            break;
                        case 'setType':
                            lazy('#setType')
                                .offset('auto')
                                .data(data)
                                .title('类型设置')
                                .area(['800px', '450px'])
                                .dialog(function (id, ele) {
                                    let type = $('select[name=type]').val();
                                    let cid = $('input[name=cid]').val();
                                    let sid = $('input[name=sid]').val();
                                    $.post("{%url('setType')%}", {"cid" : cid, "type" : type, "sid" : sid})
                                        .then(function (json) {
                                            layer.close(id);
                                            if (json.code) {
                                                Util.msgErr(json.msg);
                                            } else {
                                                Util.msgOk(json.msg);
                                                table.reload('test')
                                            }
                                        })
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                });
                            break;

                            case 'toggle_home':
                                const currentStatus = obj.data.is_home; 
                                const newHomeStatus = currentStatus == 1 ? 0 : 1;
                                
                                layer.confirm('确认切换显示状态吗?', function(index) {
                                    layer.close(index);
                                    $.post("{%url('batchSetHome')%}", {
                                        "cid": obj.data.cid,
                                        "is_home": newHomeStatus
                                    }).then(function(json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test');
                                        }
                                    });
                                });
                                break;
                                 case 'setHot':
                                    layer.confirm('确定将该内容设为热搜吗？', function (index) {
                                        layer.close(index);
                                        $.post("{%url('setHotSearch')%}", { cid: data.cid })
                                            .then(function (res) {
                                                if (res.code === 0) {
                                                    layer.msg('设置成功');
                                                    table.reload('test');
                                                } else {
                                                    layer.msg(res.msg || '设置失败');
                                                }
                                            });
                                    });
                                    break;
                            }
                })

            //监听头工具栏事件
            table.on('toolbar(table-toolbar)', function (obj) {
                let layEvent = obj.event,
                    checkStatus = table.checkStatus(obj.config.id),
                    data = checkStatus.data,
                    pkValAry = [],
                    pkName = $(this).data('pk');
                for (let i = 0; i < data.length; i++) {
                    if (typeof (data[i][pkName]) !== "undefined") {
                        pkValAry.push(data[i][pkName])
                    }
                }
                switch (layEvent) {
                    case 'add':
                         layer.open({
                                type: 2,
                                content:'{%url('add_txt')%}',
                                area:['100%','100%'],
                                title:false,
                                closeBtn:0,
                                end:function() {
                                    if (gis_reload) {
                                        table.reload('test');
                                    }
                                }
                            });
                            break;
                        break;
                    case 'delSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择内容');
                        }
                        layer.confirm('确定要批量下架选中的内容吗?', function (index) {
                            layer.close(index);
                            updateStatus(pkValAry, 'removed', '批量下架', table);
                        });
                        break;

                    case 'updateStatus':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        lazy('#batchUpdateStatus')
                            .offset('auto')
                            .data(data)
                            .title('批量设置状态')
                            .area(['800px', '450px'])
                            .dialog(function (id, ele) {
                                let status = $('select[name=status]').val();
                                $.post("{%url('batchSetStatus')%}", {"pks_" : pkValAry.join(','), "status" : status})
                                    .then(function (json) {
                                        layer.close(id);
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            table.reload('test')
                                        }
                                    })
                            })
                            .laytpl(function () {
                                xx.renderSelect(data, $, form);
                            });
                        break;
                    case 'updateListCache':
                          layer.confirm('确认操作嘛?', function (index) {
                                layer.close(index);
                                $.post("{%url('updateHomeCache')%}")
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                        }
                                    })
                            });
                        break;

                         case 'deleteArticles':
                            if (pkValAry.length === 0) {
                                return Util.msgErr('请先选择内容');
                            }
                            layer.confirm('确定要永久删除选中的文章吗？该操作不可恢复！', function (index) {
                                layer.close(index);
                                $.post("{%url('deleteArticles')%}", {
                                    cids: pkValAry.join(',')
                                }).then(function (res) {
                                    if (res.code === 0) {
                                        Util.msgOk('删除成功');
                                        table.reload('test');
                                    } else {
                                        Util.msgErr(res.msg || '删除失败');
                                    }
                                }).fail(function () {
                                    Util.msgErr('请求失败，请重试');
                                });
                            });
                            break;
                }
            });
                $(document).on('click', '.editable-text', function(e) {
                    e.stopPropagation(); 
                    const $span = $(this);
                    const currentValue = $span.text();
                    const field = $span.data('field');
                    const id = $span.data('id');
                    
                    // Create container div
                    const $container = $('<div class="edit-container" style="display:inline-block;"></div>');
                    const $input = $('<input type="text" class="layui-input" style="width:60px;display:inline-block">');
                    const $btn = $('<button class="layui-btn layui-btn-xs layui-btn-normal" style="margin-left:5px">确定</button>');
                    
                    $input.val(currentValue);
                    $container.append($input).append($btn);
                    $span.hide().after($container);
                    
                    $input.focus();
                    
                    $(document).one('click', function(e) {
                        if (!$(e.target).closest('.edit-container').length) {
                            $span.show();
                            $container.remove();
                        }
                    });
                    
                    $btn.on('click', function(e) {
                        e.stopPropagation(); 
                        const newValue = $input.val();
                        
                        $.post("{%url('setHomeTop')%}", {
                            cid: id,
                            home_top: newValue
                        }).then(function(res) {
                            if(res.code === 0) {
                                $span.text(newValue).show();
                                $container.remove();
                                layer.msg('更新成功');
                                table.reload('test');
                            } else {
                                layer.msg(res.msg || '更新失败');
                            }
                        });
                    });
                    
                    $input.on('keyup', function(e) {
                        if(e.keyCode === 13) {
                            $btn.click();
                        }
                    });
            });
            table.on('edit(table-toolbar)', function (obj) {
                let data = {'_pk': obj.data['cid']}
                    data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
            });
            table.on('sort(table-toolbar)', function(obj){
                let orderBy = {};
                orderBy[obj.field] = obj.type;
                tableIns.reload({
                    orderBy: orderBy
                });
                return false;
            });
            function dialogCallback(id, ele, obj) {
                let from = $(ele).find('form')
                $.post("{%url('save')%}", from.serializeArray())
                    .then(function (json) {
                        layer.close(id);
                        if (json.code) {
                            return Util.msgErr(json.msg);
                        }
                        if (typeof (obj) == "undefined") {
                            //添加
                            Util.msgOk(json.msg);
                            table.reload('test')
                        } else {
                            //修改
                            obj.update(json.data);
                            let index = $(obj.tr).data('index')
                            table.cache['test'][index] = json.data;
                            Util.msgOk(json.msg);
                        }
                    })
            }

            form.on('submit(search)', function (data) {
                let fields = data.field;
                let where = {}, like = {}, orderBy = {}, between = {};
                let categoryId = null;
                // 处理表单字段
                for (let k in fields) {
                    if (!fields.hasOwnProperty(k)) continue;
                    let val = (fields[k] || '').trim();
                    // if (!val) continue;

                    // 处理时间字段
                    if (k.endsWith('Time') && /^\d{4}-\d{2}-\d{2}$/.test(val)) {
                        val += ' 00:00:00';
                    }

                    // 处理嵌套字段名
                    if (k.startsWith('where[')) {
                        // 处理 where[status], where[type] 等
                        let match = k.match(/^where\[(.+?)\]$/);
                        if (match && match[1]) {
                            where[match[1]] = val;
                        }
                    } else if (k.startsWith('like[')) {
                        // 处理 like[title], like[author_name] 等
                        let match = k.match(/^like\[(.+?)\]$/);
                        if (match && match[1]) {
                            like[match[1]] = val;
                        }
                    } else if (k.startsWith('orderBy[')) {
                        // 处理 orderBy[fake_view], orderBy[view] 等
                        let match = k.match(/^orderBy\[(.+?)\]$/);
                        if (match && match[1]) {
                            orderBy[match[1]] = val;
                        }
                    } else if (k.startsWith('between[')) {
                        // 处理 between[fieldName][from|to] 格式
                        let match = k.match(/^between\[(.+?)\]\[(.+?)\]$/);
                        if (match && match[1] && match[2]) {
                            let fieldName = match[1];
                            let type = match[2]; // 'from' 或 'to'
                            
                            if (!between[fieldName]) {
                                between[fieldName] = {};
                            }
                            between[fieldName][type] = val;
                        }
                    } else if (k === 'category_id') {
                        // 处理分类搜索 - 作为顶级参数
                        categoryId = val;
                    } else {
                        // 处理其他字段
                        where[k] = val;
                    }
                }

                if (where['custormsort_id'] == undefined){
                    where['custormsort_id'] = '';
                    orderBy['custormsort_id'] ='';
                }else{
                    orderBy['custormsort_id'] = where['custormsort_id'];
                }
                console.log('搜索参数:', { where, like, orderBy, between, categoryId });

                table.reload('test', {
                    where: {
                        where: where,
                        like: like,
                        orderBy: orderBy,
                        between: between,
                        category_id: categoryId
                    },
                    page: { curr: 1 }
                });

                return false;
            });

            //渲染日期
            $('.x-date-time').each(function (key, item) {
                layDate.render({elem: item, 'type': 'datetime'});
            });
            $('.x-date').each(function (key, item) {
                layDate.render({elem: item});
            });
            form.verify(verify);
            layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});
        })

     function updateStatus(cids, status, actionText, table) {
            if (!Array.isArray(cids)) {
                cids = [cids];
            }
            
            $.post("{%url('updateStatus')%}", {
                cids: cids,
                status: status
            }).then(function(res) {
                if(res.code === 0) {
                    layer.msg(actionText + '成功');
                    table.reload('test');
                } else {
                    layer.msg(res.msg || actionText + '失败');
                }
            }).fail(function() {
                layer.msg(actionText + '请求失败，请重试');
            });
    }

</script>

<script src="https://cdn.bootcss.com/jquery/1.11.3/jquery.min.js"></script>
