{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>.layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }

    .layui-form-select .layui-select-title input {
        width: 168px;
    }
</style>

<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                </div>
                <!-- 缓存清理按钮区域 -->
                <div class="layui-card-body" style="padding: 20px;">
                    <style>
                        .cache-btn-group {
                            margin-bottom: 20px;
                        }
                        .cache-btn-group-title {
                            font-size: 14px;
                            font-weight: bold;
                            color: #333;
                            margin-bottom: 12px;
                            padding-bottom: 8px;
                            border-bottom: 1px solid #e6e6e6;
                        }
                        .cache-btn-item {
                            margin-bottom: 10px;
                        }
                        .cache-btn-item .layui-btn {
                            width: 100%;
                        }
                    </style>
                    
                    <!-- 内容缓存 -->
                    <div class="cache-btn-group">
                        <div class="cache-btn-group-title">
                            <i class="layui-icon layui-icon-file"></i> 内容缓存
                        </div>
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('index_list_cache', '首页列表缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 首页列表缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('cate_list_cache', '分类列表缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 分类列表缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('content_cache', '文章详情缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 文章详情缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('advert_cache', '广告管理缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 广告管理缓存
                                </button>
                            </div>
                        </div>
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('transit_cache', '中转页面缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 中转页面缓存
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- 系统缓存 -->
                    <div class="cache-btn-group">
                        <div class="cache-btn-group-title">
                            <i class="layui-icon layui-icon-set"></i> 系统缓存
                        </div>
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('system_settings', '系统设置缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 系统设置缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-normal" onclick="clearCache('system_variables', '系统变量缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 系统变量缓存
                                </button>
                            </div>
                        </div>
                    </div>
                    
                    <!-- YAC缓存 -->
                    <div class="cache-btn-group">
                        <div class="cache-btn-group-title">
                            <i class="layui-icon layui-icon-template"></i> YAC缓存
                        </div>
                        <div class="layui-row layui-col-space10">
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-warm" onclick="clearCache('yac_default', '导航及SEO缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 导航及SEO缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-warm" onclick="clearCache('yac_lib', '全部LIBYAC缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 全部LIBYAC缓存
                                </button>
                            </div>
                            <div class="layui-col-md3 layui-col-sm6 cache-btn-item">
                                <button class="layui-btn layui-btn-sm layui-btn-warm" onclick="clearCache('yac_all', '全部YAC缓存')">
                                    <i class="layui-icon layui-icon-refresh"></i> 全部YAC缓存
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <form class="layui-form" lay-filter="search-form" id="search-form">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">缓存名称</label>
                            <div class="layui-input-inline">
                                <input type="text" name="name" placeholder="输入缓存名称" autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <button class="layui-btn" lay-submit lay-filter="search">搜索</button>
                        </div>
                    </div>
                </form>
                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{field:'name',align:'center'}">名称</th>
                            <th lay-data="{field:'group',align:'center'}">组名</th>
                            <th lay-data="{fixed: 'right',width: 300 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm layui-btn-warm" lay-event="selective_clear" data-pk="id">
                                <i class="layui-icon layui-icon-refresh"></i> 选择性清理缓存
                            </button>
                            <!--<button class="layui-btn layui-btn-sm layui-btn-danger" lay-event="refresh_yac" data-pk="id">
                                <i class="layui-icon layui-icon-delete"></i> 清理所有缓存
                            </button>-->
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <a class="layui-btn layui-btn-danger layui-btn-xs" data-pk="{{=d.id}}" data-group="{{=d.group}}" lay-event="refresh"><i
                                    class="layui-icon layui-icon-refresh"></i>刷新缓存</a>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'], function (table, laytpl, form, lazy, layDate, layEdit) {

        let verify = {}

            // 清理单个缓存类型的函数
            window.clearCache = function(cacheType, cacheName) {
                // 显示确认对话框
                layer.confirm('确定要清理' + cacheName + '吗？', {
                    icon: 3,
                    title: '确认清理缓存'
                }, function(index) {
                    // 显示加载提示
                    var loadingIndex = layer.load(1, {
                        shade: [0.1,'#fff']
                    });
                    
                    $.post("{%url('selective_clear')%}", {
                        cache_types: [cacheType],
                        options: []
                    })
                    .then(function (json) {
                        layer.close(loadingIndex);
                        if (json.code) {
                            Util.msgErr(json.msg);
                        } else {
                            Util.msgOk(json.msg);
                            // 刷新表格数据
                            table.reload('test');
                        }
                    })
                    .catch(function(error) {
                        layer.close(loadingIndex);
                        Util.msgErr('请求失败，请重试');
                        console.error('缓存清理请求失败:', error);
                    });
                    
                    layer.close(index);
                });
            };

            //监听头工具栏事件
             table.on('toolbar(table-toolbar)', function (obj) {
                 var layEvent = obj.event;
                 switch (layEvent) {
                     case 'selective_clear':
                        showSelectiveClearDialog();
                        break;
                     case 'refresh_yac':
                        // 显示确认对话框
                        layer.confirm('确定要清理所有缓存吗？这将清理当前项目的所有缓存数据。', {
                            icon: 3,
                            title: '确认清理所有缓存'
                        }, function(index) {
                            // 显示加载提示
                            var loadingIndex = layer.load(1, {
                                shade: [0.1,'#fff']
                            });
                            
                            $.post("{%url('refresh_yac')%}", {})
                                .then(function (json) {
                                    layer.close(loadingIndex);
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        // 刷新表格数据
                                        table.reload('test');
                                    }
                                })
                                .catch(function(error) {
                                    layer.close(loadingIndex);
                                    Util.msgErr('请求失败，请重试');
                                    console.error('缓存清理请求失败:', error);
                                });
                            
                            layer.close(index);
                        });
                        break;
                }
            });
            
            // 显示选择性清理缓存对话框
            function showSelectiveClearDialog() {
                var html = `
                    <style>
                        .cache-clear-dialog label {
                            display: block;
                            margin: 5px 0;
                            cursor: pointer;
                        }
                        .cache-clear-dialog input[type="checkbox"] {
                            margin-right: 8px;
                        }
                        .cache-clear-dialog strong {
                            color: #333;
                            font-weight: bold;
                        }
                    </style>
                    <div style="padding: 20px;" class="cache-clear-dialog">
                        <form class="layui-form" lay-filter="cache-clear-form">
                            <div class="layui-form-item">
                                <label class="layui-form-label">缓存类型</label>
                                <div class="layui-input-block">
                                    <div style="margin-bottom: 10px;">
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="selectListCache(this)">列表缓存</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="selectDetailCache(this)">文章缓存</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="selectAdvertCache(this)">广告管理缓存</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="selectTrasitCache(this)">中转页面缓存</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-normal" onclick="selectConfigCache(this)">配置缓存</button>
                                        <button type="button" class="layui-btn layui-btn-xs" onclick="selectAllCacheTypes(this)">全选</button>
                                        <button type="button" class="layui-btn layui-btn-xs layui-btn-primary" onclick="deselectAllCacheTypes(this)">全不选</button>
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <strong>列表缓存：</strong><br>
                                        <input type="checkbox" name="cache_types[]" value="index_list_cache" title="首页列表缓存" lay-skin="primary">
                                        <input type="checkbox" name="cache_types[]" value="cate_list_cache" title="分类列表缓存" lay-skin="primary">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <strong>文章详情缓存：</strong><br>
                                        <input type="checkbox" name="cache_types[]" value="content_cache" title="文章详情缓存" lay-skin="primary">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <strong>广告管理缓存：</strong><br>
                                        <input type="checkbox" name="cache_types[]" value="advert_cache" title="广告管理缓存" lay-skin="primary">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <strong>中转页面缓存：</strong><br>
                                        <input type="checkbox" name="cache_types[]" value="transit_cache" title="中转页面缓存" lay-skin="primary">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <strong>配置缓存：</strong><br>
                                        <input type="checkbox" name="cache_types[]" value="system_settings" title="系统设置缓存" lay-skin="primary">
                                        <input type="checkbox" name="cache_types[]" value="system_variables" title="系统变量缓存" lay-skin="primary">
                                        <input type="checkbox" name="cache_types[]" value="yac_lib" title="LibYac缓存" lay-skin="primary">
                                        <input type="checkbox" name="cache_types[]" value="yac_default" title="导航及SEO缓存" lay-skin="primary">
                                        <input type="checkbox" name="cache_types[]" value="yac_all" title="全部YAC缓存" lay-skin="primary">
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                `;

                layer.open({
                    type: 1,
                    title: '选择性清理缓存',
                    area: ['600px', '500px'],
                    content: html,
                    btn: ['开始清理', '取消'],
                    success: function(layero, index) {
                        console.log('对话框打开成功');
                        
                        // 初始化Layui复选框
                        layui.form.render('checkbox');
                        
                        // 定义全局函数来处理按钮点击
                        window.selectAllCacheTypes = function(btn) {
                            console.log('点击全选按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        
                        window.deselectAllCacheTypes = function(btn) {
                            console.log('点击全不选按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layui.form.render('checkbox');
                        };
                        
                        window.selectBusinessCache = function(btn) {
                            console.log('点击业务数据缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="content_cache"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="user_data"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="app_categories"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="pc_categories"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        
                        window.selectFileCache = function(btn) {
                            console.log('点击文件缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="file_cache"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="views_cache"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="yac_html"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        
                        window.selectConfigCache = function(btn) {
                            console.log('点击配置缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="system_settings"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="system_variables"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="yac_lib"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="yac_default"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="yac_all"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };

                        window.selectListCache = function(btn) {
                            console.log('点击列表缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="index_list_cache"]').prop('checked', true);
                            layero.find('input[name="cache_types[]"][value="cate_list_cache"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        window.selectDetailCache = function(btn) {
                            console.log('点击文章详情缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="content_cache"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        window.selectAdvertCache = function(btn) {
                            console.log('点击广告管理缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="advert_cache"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                        window.selectTrasitCache = function(btn) {
                            console.log('点击中转页面缓存按钮');
                            layero.find('input[name="cache_types[]"]').prop('checked', false);
                            layero.find('input[name="cache_types[]"][value="transit_cache"]').prop('checked', true);
                            layui.form.render('checkbox');
                        };
                    },
                    yes: function(index, layero) {
                        var formData = new FormData(layero.find('form')[0]);
                        var cacheTypes = [];
                        var cacheGroups = [];
                        var options = [];
                        
                        // 收集选中的缓存类型
                        layero.find('input[name="cache_types[]"]:checked').each(function() {
                            cacheTypes.push($(this).val());
                        });
                        
                        // 收集选中的缓存组
                        layero.find('input[name="cache_groups[]"]:checked').each(function() {
                            cacheGroups.push($(this).val());
                        });
                        
                        // 收集选中的选项
                        layero.find('input[name="options[]"]:checked').each(function() {
                            options.push($(this).val());
                        });
                        
                        if (cacheTypes.length === 0 && cacheGroups.length === 0) {
                            Util.msgErr('请至少选择一种缓存类型或缓存组');
                            return;
                        }
                        
                        layer.close(index);
                        
                        // 显示加载提示
                        var loadingIndex = layer.load(1, {
                            shade: [0.1,'#fff']
                        });
                        
                        $.post("{%url('selective_clear')%}", {
                            cache_types: cacheTypes,
                            options: options
                        })
                        .then(function (json) {
                            layer.close(loadingIndex);
                            if (json.code) {
                                Util.msgErr(json.msg);
                            } else {
                                Util.msgOk(json.msg);
                                // 刷新表格数据
                                table.reload('test');
                            }
                        })
                        .catch(function(error) {
                            layer.close(loadingIndex);
                            Util.msgErr('请求失败，请重试');
                            console.error('选择性缓存清理请求失败:', error);
                        });
                    }
                });
            }

            table.on('tool(table-toolbar)', function (obj) {
                //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                var data = obj.data,
                    layEvent = obj.event,
                    that = this;
                switch (layEvent) {
                    case 'refresh':
                        $.post("{%url('refresh')%}", {"_pk": $(that).data('pk'),'group':$(that).data('group')})
                            .then(function (json) {
                                if (json.code) {
                                    Util.msgErr(json.msg);
                                } else {
                                    Util.msgOk(json.msg);
                                    obj.del();
                                }
                            })
                        break;
                }
            })
        form.on('submit(search)', function (data) {
            var where = {}, ary = data.field, k;
            for (k in ary) {
                if (ary.hasOwnProperty(k) && ary[k].length > 0) {
                    if (k.substring(k.length - 4) === 'Time' && /^\d{4}-\d{2}-\d{2}$/.test(ary[k])) {
                        ary[k] += " 00:00:00";
                    }
                    where[k] = ary[k];
                } else {
                    where[k] = "__undefined__"
                }
            }
            table.reload('test', {
                where: where,
                page: {curr: 1}
            });
            return false;
        });

        form.verify(verify);
    })
</script>