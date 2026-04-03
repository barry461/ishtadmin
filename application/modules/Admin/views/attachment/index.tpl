{%include file="header.tpl"%}
<body>

<!-- 页面加载loading -->
<div class="page-loading">
    <div class="ball-loader">
        <span></span><span></span><span></span><span></span>
    </div>
</div>

<style>
    .layui-form.form-dialog .layui-input-block {
        margin-right: 30px
    }
    .layui-table td {
        font-size: 12px;
    }
    .layui-table-cell , .operate-toolbar{font-size: 12px;min-height:28px;height: auto;}
</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">切片管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">
                        <div class="layui-inline">
                            <label class="layui-form-label">视频名称</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[name]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[slice_status]">
                                    <option value="">全部</option>
                                    {%html_options selected=data_get($get,'where.slice_status') options=UserUploadModel::SLICE_TIPS%}
                                </select>
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">时间</label>
                            <div class="layui-input-block">
                                {%html_between name="created_at"%}
                            </div>
                        </div>

                        <div class="layui-inline">
                            <button class="layui-btn layuiadmin-btn-useradmin" lay-submit lay-filter="search">
                                <i class="layui-icon layui-icon-search layuiadmin-button-btn"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <script type="text/html" id="toolbar">
                    <div class="layui-btn-container">
                        <button class="layui-btn layui-btn-sm" lay-event="add">
                            上传视频
                        </button>
                    </div>
                </script>

                <div class="layui-card-body">
                    <table class="layui-table"
                           lay-data="{url:'{%url('listAjax',$get)%}', page:true, id:'test',limit:50,toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'name',width: 100 }">视频名称</th>
                            <th lay-data="{field:'progress_rate',width: 100}">进度</th>
                            <th lay-data="{field:'created_at',width: 200 }">时间</th>
                            <th lay-data="{field:'slice_status', width: 100 }">状态</th>
                            <th lay-data="{field:'m3u8_url',width: 400 }">视频地址</th>
                            <th lay-data="{field:'cover',width: 400 }">封面地址</th>
                            <th lay-data="{fixed: 'right',width: 250 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="a10">
                        {{=d.text_show}}
                    </script>
                    <script type="text/html" id="a3">

                        <div style="display: flex;flex-direction: row;" onclick="row_click(this)">
                            <div style="display: flex;flex-direction: row;flex: 0.2">
                                <div><img src="https://secure.gravatar.com/avatar/?s=40&r=G&d="></div>
                                <div style="width: 170px;padding-left: 5px;">
                                    <div>{{d.author}}</div>
                                    <div style="width: 150px;">
                                        <p style="white-space: pre-wrap;color:#cccccc;word-break:break-word;">{{=d.ip}}</p>
                                    </div>
                                </div>
                            </div>
                            <div>
                                <div style="display: flex;flex-direction: row;">
                                    <span style="padding-right: 8px;color:#cccccc;">{{=d.time_line}}</span>
                                    <p style="color: #467B96;">{{=d.contents.title}}</p>
                                </div>
                                <div><p style="white-space: pre-wrap;color:#000;padding: 10px 0">{{=d.text}}</p></div>
                            </div>
                        </div>

                    </script>
                   
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a data-pk="{{=d.coid}}" lay-event="pass"></a> 
                        </div>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 修改添加对话框模板 -->
<script type="text/html" class="data-dialog" id="add-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>切片信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">
        <div class="layui-form-item">
            <label class="layui-form-label">视频名称：</label>
            <div class="layui-input-block">
                <input lay-verify="required" placeholder="视频名称" name="name" id="name"
                       class="layui-input">
            </div>
        </div>
        
       <div class="layui-form-item">
            <label class="layui-form-label">视频文件：</label>
            <div class="layui-input-block">
                <button type="button" class="layui-btn" id="upload-video">
                    <i class="layui-icon">&#xe67c;</i>上传视频
                </button>
                <div class="layui-progress layui-hide" lay-filter="upload-progress" lay-showpercent="true">
                    <div class="layui-progress-bar" lay-percent="0%"></div>
                </div>
                <input type="hidden" name="mp4_url" value="{{=d.mp4_url||''}}">
                <div class="layui-word-aux">支持 mp4 格式，大小不超过 500MB</div>
            </div>
        </div>


        <div class="layui-form-item">
            <label class="layui-form-label">封面图：</label>
            <div class="layui-input-block">
                <button type="button" class="layui-btn" id="upload-cover">
                    <i class="layui-icon">&#xe67c;</i>上传封面
                </button>
                <div class="layui-inline" id="cover-preview" style="display:none;margin-top:10px;">
                    <img src="" style="max-width: 200px; max-height: 200px;">
                    <button type="button" class="layui-btn layui-btn-xs layui-btn-danger" onclick="removeCover()">
                        <i class="layui-icon">&#xe640;</i>移除
                    </button>
                </div>
                <input type="hidden" name="cover" value="{{=d.cover||''}}">
                <div class="layui-word-aux">支持 jpg、png、gif 格式，大小不超过 2MB</div>
            </div>
        </div>
    </form>
</script>


{%include file="fooler.tpl"%}
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
    function row_click(){}
    layui.use(['element','table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (element,table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {},
                tool = {  },
                toolbar = {
                    "pass": function (obj, pkValAry, that) {
                        $.post("{%url('pass')%}", {"value": pkValAry.join(',')})
                            .then(function (json) {
                                if (json.code){
                                    Util.msgErr(json.msg);
                                }else{
                                    $('button.layui-btn.layuiadmin-btn-useradmin').click()
                                }
                            })
                    }
                };
 
            row_click =function (that){
                let tr = $(that).parents('tr');
                if ($(tr).hasClass('layui-table-click')){
                    $(tr).removeClass('layui-table-click');
                }else{
                    $(tr).addClass('layui-table-click');
                }

                $(tr).find('div.layui-form-checkbox').click();
            }

           

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
                    default:
                        if (typeof (toolbar[layEvent]) !== "undefined") {
                            toolbar[layEvent](obj, pkValAry, this)
                        }
                        break;
                    case 'add':
                        lazy('#add-dialog')
                            .width(`${document.body.clientWidth-800}px`)
                            .dialog(function (id, ele) {
                                dialogCallback(id, ele)
                            })
                            .laytpl(function () {
                                xx.renderSelect({}, $, form);
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            });


                           async function getR2uploadUrl() {

                                    try {
                                            const res = await fetch('{%url("mv/getr2uploadurl")%}');
                                            return await res.json(); // 应返回 { status: 1, data: { uploadUrl, UploadName, publicUrl } }
                                        } catch (e) {
                                            return { status: 0, msg: '接口请求失败' };
                                        }
                                }

                                // 上传文件到 R2
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
                                    return { code: -1, msg: '获取上传链接失败' };
                                }

                                const { uploadUrl, UploadName, publicUrl } = res.data;
                                const formData = new FormData();
                                formData.append('video', file, UploadName);

                                try {
                                    const response = await axios.put(uploadUrl, formData.get('video'), {
                                    headers: { 'Content-Type': 'video/mp4' },
                                    onUploadProgress: onProgress ? function (progressEvent) {
                                        const progress = Math.round((progressEvent.loaded * 100) / (progressEvent.total || 1));
                                        onProgress(progress);
                                    } : undefined
                                    });

                                    return response.status === 200
                                    ? { code: 1, msg: publicUrl }
                                    : { code: -1, msg: '上传失败' };

                                } catch (e) {
                                    return { code: -1, msg: e.message || '上传异常' };
                                }
                                }

                                // 绑定 layui.upload 自定义逻辑
                                layui.use(['upload', 'element', 'layer'], function () {
                                var upload = layui.upload;
                                var element = layui.element;
                                var layer = layui.layer;

                                upload.render({
                                    elem: '#upload-video',
                                    auto: false,
                                    accept: 'video',
                                    choose: function (obj) {
                                    var files = obj.pushFile();
                                    var fileKey = Object.keys(files)[0];
                                    var file = files[fileKey];

                                    $('.layui-progress').removeClass('layui-hide');
                                    element.progress('upload-progress', '0%');
                                    layer.load();

                                    r2fileUploadMp4(file, function (percent) {
                                        element.progress('upload-progress', percent + '%');
                                    }).then(function (res) {
                                        layer.closeAll('loading');
                                        console.log(res);
                                        if (res.code === 1) {
                                        layer.msg('上传成功');
                                        $('input[name="mp4_url"]').val(res.msg);
                                        $('.layui-word-aux').text('上传成功，视频地址：' + res.msg);
                                        } else if (res.code === -1) {
                                        } else {
                                        layer.msg('上传失败：' + res.msg);
                                        }
                                    }).catch(function () {
                                        layer.closeAll('loading');
                                        layer.msg('上传异常，请重试');
                                    }).finally(function () {
                                        $('.layui-progress').addClass('layui-hide');
                                    });
                                    }
                                });
                                });
                            

                            
                            upload.render({
                                elem: '#upload-cover',
                                url: '{%url("upload/upload")%}',
                                accept: 'images',
                                acceptMime: 'image/*',
                                size: 1024*2, 
                                data: {
                                    
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                before: function(obj){
                                    layer.load(); 
                                },
                                done: function(res){
                                    layer.closeAll('loading');
                                    if(res.code === 200){
                                        layer.msg('上传成功');
                                        $('input[name="cover"]').val(res.data.url);
                                        $('#cover-preview').show().find('img').attr('src', res.data.url);
                                    } else {
                                        layer.msg('上传失败：' + res.msg);
                                    }
                                },
                                error: function(){
                                    layer.closeAll('loading');
                                    layer.msg('上传失败，请重试');
                                }
                            });

                            
                            window.removeCover = function() {
                                $('input[name="cover"]').val('');
                                $('#cover-preview').hide().find('img').attr('src', '');
                            };
                        break;
                
                   
                }
            });
            // 监听单元格编辑
            table.on('edit(table-toolbar)', function (obj) {
                let data = {'_pk': obj.data['coid']}
                data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
            });

            
            function dialogCallback(id, ele, obj) {
                let from = $(ele).find('form')

                
                $.post("{%url('upload_mv')%}", from.serializeArray())
                    .then(function (json) {
                        if (json.code) {
                            return Util.msgErr(json.msg);
                        }
                        if (typeof (obj) == "undefined") {
                            layer.close(id);
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

          function getR2UploadUrl() {
                return new Promise((resolve, reject) => {
                    $.ajax({
                        url: "{%url('getR2UploadUrl')%}",
                        type: "GET",
                        success: function (response) {
                            if (response.code === 0) {
                                resolve(response.data);
                            } else {
                                layer.msg(response.msg);
                                reject(response.msg);
                            }
                        },
                        error: function () {
                            layer.msg('获取上传地址失败，请稍后再试');
                            reject("获取失败");
                        }
                    });
                });
            }

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

            //渲染日期
            $('.x-date-time').each(function (key, item) {
                layDate.render({elem: item, 'type': 'datetime'});
            });
            $('.x-date').each(function (key, item) {
                layDate.render({elem: item});
            });
            form.verify(verify);
            layEdit.set({uploadImage: {url: Util.config("editUpload", '')}});

            function tableUpdate(obj, json) {
                obj.update(json.data);
                let index = $(obj.tr).data('index')
                table.cache['test'][index] = json.data;
                layer.msg('ok', {time: 400})
            }
        

            form.on('submit(submitForm)', function () {
                
                const submitData = {
                    name: $('#name').val(),
                    mp4_url: $('input[name="mp4_url"]').val(),
                    cover: $('input[name="cover"]').val()
                };
                if (!submitData.name) {
                    return Util.msgErr('视频名称不能为空');
                }
                if (!submitData.mp4_url) {
                    return Util.msgErr('视频文件不能为空');
                }
                if (!submitData.cover) {
                    return Util.msgErr('封面图不能为空');
                }
       
            $.post('{%url('upload_mv')%}', submitData, function(res) {
                console.log(res);
                if (res.code === 0 ){

                    Util.msgOk(res.msg);
                
                }else {

                     Util.msgErr(res.msg);
                }

            });
      
      return false;
      
    });   
       
    })
</script>
