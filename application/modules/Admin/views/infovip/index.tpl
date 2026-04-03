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
    }</style>
<div class="layui-fluid">
    <div class="layui-row layui-col-space15">
        <div class="layui-col-md12">
            <div class="layui-card">
                <div class="layui-card-header">管理</div>
                <div class="layui-form layui-card-header layuiadmin-card-header-auto">
                    <div class="layui-form-item">

                        <div class="layui-inline">
                            <label class="layui-form-label">id</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>


                        <div class="layui-inline">
                            <label class="layui-form-label">title</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>


                        <div class="layui-inline">
                            <label class="layui-form-label">城市</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[cityName]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">妹子年龄</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[girl_age]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">身高</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[girl_height]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">罩杯</label>
                            <div class="layui-input-block">

                                <select name="where[girl_cup]" lay-search="" >
                                    <option value="">全部</option>
                                    {%html_options options=InfoVipModel::CUP %}
                                </select>


                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">预约金</label>
                            <div class="layui-input-block">
                                <input type="text" name="where[fee]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>


                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <select name="where[status]" lay-search="" >
                                    <option value="">全部</option>
                                    {%html_options options=InfoVipModel::STATUS %}
                                </select>

                            </div>
                        </div>

                        <div class="layui-inline">
                            <label class="layui-form-label">排序</label>
                            <div class="layui-input-block">
                                <select name="orderBy[sort]" lay-search="">
                                    <option value="">无</option>
                                    <option value="desc">降序</option>
                                    <option value="asc">升序</option>
                                </select>
                            </div>
                        </div>


                        <div class="layui-inline">
                            <label class="layui-form-label">类型</label>
                            <div class="layui-input-block">
                                <select name="where[category]" lay-search="" >
                                    <option value="">全部</option>
                                    {%html_options options=InfoVipModel::CATEGORY%}
                                </select>
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
                           lay-data="{url:'{%url('listAjax')%}', page:true, id:'test',toolbar:'#toolbar'}"
                           lay-filter="table-toolbar">
                        <thead>
                        <tr>
                            <th lay-data="{type:'checkbox'}"></th>
                            <th lay-data="{field:'id', width:80}">id</th>
                            <th lay-data="{field:'aff',width:80}">用户id</th>
                            <th lay-data="{templet:'#description',width:150}">描述</th>
                            <th lay-data="{templet:'#girl',width:150}">妹子</th>
                            <th lay-data="{templet:'#price',width:100}">价格</th>
                            <th lay-data="{templet:'#address',width:150}">地址</th>
                            <th lay-data="{templet:'#contact',width:150}">联系方式</th>
                            <th lay-data="{templet:'#other',width:150}">其它</th>
                            <th lay-data="{field:'sort',width:80,edit:true}">排序</th>
                            <th lay-data="{templet:'#time',width:150}">时间</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="other">
                        <p>favorite: {{=d.favorite}}</p>
                        <p>vvip: {{=d.vvip}}</p>
                        <p>confirm: {{=d.confirm}}</p>
                        <p>appointment: {{=d.appointment}}</p>
                        </script>
                    <script type="text/html" id="description">
                        <p>标题：{{=d.title}}</p>
                        <p>详细描述：{{=d.desc}}</p>
                        <p>查看次数：{{=d.view}}</p>
                        <p>评分：{{=d.mark}}</p>
                        <p>状态：{{=d.status_str}}</p>
                        <p>类型：{{=d.type_str}}</p>
                        <p>类型：{{=d.type_str}}</p>
                        <p>分类：{{=d.category_str}}</p>
                        </script>

                    <script type="text/html" id="address">
                        <p>地址：{{=d.address}}</p>
                        <p>城市代码：{{=d.cityCode}}</p>
                        <p>城市名称：{{=d.cityName}}</p>
                        </script>
                    <script type="text/html" id="contact">
                        <p>{{=d.phone}}</p>
                        <p>微信: {{=d.wechat}}</p>
                        <p>QQ: {{=d.qq}}</p>
                        <p>次数: {{=d.fee_ct}}</p>
                        <p>营业时间: {{=d.business_hours}}</p>
                        </script>
                    <script type="text/html" id="girl">
                        <p>妹子颜值: {{=d.girl_face}}</p>
                        <p>妹子girl_face_text: {{=d.girl_face_text}}</p>
                        <p>妹子年龄: {{=d.girl_age}}</p>
                        <p>妹子身高: {{=d.girl_height}}</p>
                        <p>妹子罩杯: {{=d.cup_str}}</p>
                        <p>妹子数量: {{=d.girl_num}}</p>
                        <p>妹子girl_age_num: {{=d.girl_num}}</p>
                        <p>妹子服务: {{=d.girl_service}}</p>
                        <p>妹子服务种类: {{=d.girl_service_type}}</p>
                    </script>
                    <script type="text/html" id="price">
                        <p style="color: red">价格: {{=d.price}}</p>
                        <p style="color: blue">预约金: {{=d.fee}}</p>
                        <p>一炮价格: {{=d.price_p}}</p>
                        <p>两炮价格: {{=d.price_pp}}</p>
                        <p>包夜价格: {{=d.price_all_night}}</p>
                        <p>消费方式: {{=d.cast_way}}</p>
                    </script>

                    <script type="text/html" id="time">
                        <p>创：{{=d.created_at}}</p>
                        <p>改：{{=d.updated_at}}</p>
                        </script>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>


                            <button class="layui-btn layui-btn-sm" lay-event="batAccept"
                                    data-pk="id">审核通过
                            </button>

                            <button class="layui-btn layui-btn-sm" lay-event="batReject"
                                    data-pk="id">审核拒绝
                            </button>

                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
                            <a data-pk="{{=d.id}}" lay-event="media">媒体</a> |


                            {{# if(d.status === 1 ){  }}
                            <a lay-event="accept" data-pk="{{=d.id}}">通过</a> |
                            <a lay-event="reject" data-pk="{{=d.id}}">拒绝</a> |
                            {{#  } }}


                            <a lay-event="edit">修改</a> |
                            <a data-pk="{{=d.id}}" lay-event="del">删除</a>



                        </div>
                    </script>
                </div>
            </div>
        </div>
    </div>
</div>


<script type="text/html" class="data-dialog" id="user-edit-dialog">
    <fieldset class="layui-elem-field layui-field-title" style="margin-top: 20px;">
        <legend>信息</legend>
    </fieldset>
    <form class="layui-form form-dialog" action="" lay-filter="form-save">


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">用户id：</label>
                <div class="layui-input-inline">

                    <input placeholder="用户id" name="aff"
                           value="{{=d.aff }}" class="layui-input" disabled>

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">标题：</label>
                <div class="layui-input-inline">

                    <input placeholder="标题" name="title"
                           value="{{=d.title }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">详细描述：</label>
                <div class="layui-input-inline">

                    <input placeholder="详细描述" name="desc"
                           value="{{=d.desc }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">城市代码：</label>
                <div class="layui-input-inline">

                    <input placeholder="城市代码" name="cityCode"
                           value="{{=d.cityCode }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">cityName：</label>
                <div class="layui-input-inline">

                    <input placeholder="cityName" name="cityName"
                           value="{{=d.cityName }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">address：</label>
                <div class="layui-input-inline">
                    <input placeholder="address" name="address"
                           value="{{=d.address }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子数量：</label>
                <div class="layui-input-inline">
                    <input placeholder="妹子数量" name="girl_num"
                           value="{{=d.girl_num }}" class="layui-input">
                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">罩杯：</label>
                <div class="layui-input-inline">
                    <select name="where[girl_cup]" lay-search="" data-value="{{=d.girl_cup}}">
                        <option value="">全部</option>
                        {%html_options options=InfoVipModel::CUP %}
                    </select>

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子年龄：</label>
                <div class="layui-input-inline">

                    <input placeholder="妹子年龄" name="girl_age"
                           value="{{=d.girl_age }}" class="layui-input">

                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">girl_age_num：</label>
                <div class="layui-input-inline">

                    <input placeholder="girl_age_num" name="girl_age_num"
                           value="{{=d.girl_age_num }}" class="layui-input">
                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">身高：</label>
                <div class="layui-input-inline">

                    <input placeholder="身高" name="girl_height"
                           value="{{=d.girl_height }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">一炮价格：</label>
                <div class="layui-input-inline">

                    <input placeholder="一炮价格" name="price_p"
                           value="{{=d.price_p }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">两炮价格：</label>
                <div class="layui-input-inline">
                    <input placeholder="两炮价格" name="price_pp"
                           value="{{=d.price_pp }}" class="layui-input">
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">包夜价格：</label>
                <div class="layui-input-inline">
                    <input placeholder="包夜价格" name="price_all_night"
                           value="{{=d.price_all_night }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">消费方式：</label>
                <div class="layui-input-inline">

                    <input placeholder="消费方式" name="cast_way"
                           value="{{=d.cast_way }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">妹子服务种类：</label>
                <div class="layui-input-inline">
                    <input placeholder="妹子服务种类" name="girl_service_type"
                           value="{{=d.girl_service_type }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子颜值：</label>
                <div class="layui-input-inline">

                    <input placeholder="妹子颜值" name="girl_face"
                           value="{{=d.girl_face }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">girl_face_text：</label>
                <div class="layui-input-inline">

                    <input placeholder="girl_face_text" name="girl_face_text"
                           value="{{=d.girl_face_text }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">妹子服务：</label>
                <div class="layui-input-inline">

                    <input placeholder="妹子服务" name="girl_service"
                           value="{{=d.girl_service }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">价格：</label>
                <div class="layui-input-inline">

                    <input placeholder="价格" name="price"
                           value="{{=d.price }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">预约金：</label>
                <div class="layui-input-inline">

                    <input placeholder="预约金" name="fee"
                           value="{{=d.fee }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">营业时间：</label>
                <div class="layui-input-inline">

                    <input placeholder="营业时间" name="business_hours"
                           value="{{=d.business_hours }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">favorite：</label>
                <div class="layui-input-inline">

                    <input placeholder="favorite" name="favorite"
                           value="{{=d.favorite }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">video_valid：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="video_valid" name="video_valid"
                           value="{{=d.video_valid }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">vvip：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="vvip" name="vvip"
                           value="{{=d.vvip }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">phone：</label>
                <div class="layui-input-inline">

                    <input placeholder="phone" name="phone"
                           value="{{=d.phone }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">微信：</label>
                <div class="layui-input-inline">
                    <input placeholder="微信" name="wechat"
                           value="{{=d.wechat }}" class="layui-input">
                </div>
            </div>
            <div class="layui-inline">
                <label class="layui-form-label">QQ：</label>
                <div class="layui-input-inline">
                    <input placeholder="QQ" name="qq"
                           value="{{=d.qq }}" class="layui-input">
                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">类型：</label>
                <div class="layui-input-inline">
                    <select name="where[girl_cup]" lay-search="" data-value="{{=d.type}}">
                        <option value="">全部</option>
                        {%html_options options=InfoVipModel::TYPE %}
                    </select>
                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">


                    <select name="status" lay-search="" data-value="{{=d.status}}">
                        <option value="">全部</option>
                        {%html_options options=InfoVipModel::STATUS %}
                    </select>

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">查看次数：</label>
                <div class="layui-input-inline">

                    <input placeholder="查看次数" name="view"
                           value="{{=d.view }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">confirm：</label>
                <div class="layui-input-inline">

                    <input placeholder="confirm" name="confirm"
                           value="{{=d.confirm }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">appointment：</label>
                <div class="layui-input-inline">

                    <input placeholder="appointment" name="appointment"
                           value="{{=d.appointment }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">类别：</label>
                <div class="layui-input-inline">
                    <select name="category" lay-search="" data-value="{{=d.category}}" >
                        <option value="">全部</option>
                        {%html_options options=InfoVipModel::CATEGORY %}
                    </select>
                </div>
            </div>
        </div>

        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">排序：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="排序" name="sort"
                           value="{{=d.sort }}" class="layui-input">

                </div>
            </div>


            <div class="layui-inline">
                <label class="layui-form-label">置顶 ：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="置顶 越大越靠前" name="top"
                           value="{{=d.top }}" class="layui-input">

                </div>
            </div>

            <div class="layui-inline">
                <label class="layui-form-label">评分：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required" placeholder="评分" name="mark"
                           value="{{=d.mark }}" class="layui-input">

                </div>
            </div>
        </div>

        <div class="layui-form-item layui-hide">
            <input type="hidden" name="_pk" value="{{=d.id}}">
            <button class="layui-btn submit" lay-submit="" lay-filter="save"></button>
        </div>

    </form>
</script>

{%include file="fooler.tpl"%}
<script>
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit', 'upload', 'jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

                table.on('tool(table-toolbar)', function (obj) {
                    //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                    var data = obj.data,
                        ddd = document.documentElement,
                        layEvent = obj.event,
                        that = this;
                    switch (layEvent) {
                        case 'del':
                            layer.confirm('真的删除吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('del')%}", {"_pk": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            obj.del();
                                        }
                                    })
                            });
                            break;
                        case 'edit':
                            lazy('#user-edit-dialog')
                                .data(data)
                                .area([1100 + 'px', document.body.offsetHeight + 'px'])
                                .dialog(function (id, ele) {
                                    dialogCallback(id, ele, obj)
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                    Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                                });
                            break;
                        case 'media':
                            lazy('')
                                .iframe('{%url('infovipresources/index')%}?where[info_id]='+data['id'])
                                .area([`${ddd.clientWidth - 200}px` , `${ddd.clientHeight}px`])
                                .title(`媒体-[${data.id}]${data.title}`)
                                .start(function () {

                                })
                            break;
                        case 'accept':
                            layer.confirm('确定通过吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('accept')%}", {"_pk": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            obj.del();
                                        }
                                    })
                            });
                            break;

                        case 'reject':
                            layer.confirm('确定拒绝吗?', function (index) {
                                layer.close(index);
                                $.post("{%url('reject')%}", {"_pk": $(that).data('pk')})
                                    .then(function (json) {
                                        if (json.code) {
                                            Util.msgErr(json.msg);
                                        } else {
                                            Util.msgOk(json.msg);
                                            obj.del();
                                        }
                                    })
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
                        lazy('#user-edit-dialog')
                            .area([1100 + 'px', document.body.offsetHeight + 'px'])
                            .dialog(function (id, ele) {
                                dialogCallback(id, ele)
                            })
                            .laytpl(function () {
                                xx.renderSelect({}, $, form);
                                Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
                            });
                        break;
                    case 'delSelect':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        layer.confirm('真的删除吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('delAll')%}", {"value": pkValAry.join(',')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                    }
                                })
                        });
                        break;


                    case 'batAccept':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        layer.confirm('真的通过吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('batAccept')%}", {"value": pkValAry.join(',')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                    }
                                })
                        });
                        break;

                    case 'batReject':
                        if (pkValAry.length === 0) {
                            return Util.msgErr('请先选择行');
                        }
                        layer.confirm('真的拒绝吗?', function (index) {
                            layer.close(index);
                            $.post("{%url('batReject')%}", {"value": pkValAry.join(',')})
                                .then(function (json) {
                                    if (json.code) {
                                        Util.msgErr(json.msg);
                                    } else {
                                        Util.msgOk(json.msg);
                                        table.reload('test');
                                    }
                                })
                        });
                        break;
                }
            });
            // 监听单元格编辑
            table.on('edit(table-toolbar)', function (obj) {
                let data = {'_pk': obj.data['id']}
                    data[obj.field] = obj.value;
                $.post("{%url('save')%}", data).then(function (json) {
                    layer.msg(json.msg);
                });
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
        })
</script>
