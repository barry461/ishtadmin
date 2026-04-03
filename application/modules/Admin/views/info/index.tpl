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
                                <input type="text" name="search[id]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">用户id</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[uid]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">审核aff</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[check_aff]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">标题</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[title]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">资源类型</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[type]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">城市代码</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[cityCode]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">cityName</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[cityName]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">妹子数量</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_num]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">妹子年龄</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_age]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">妹子颜值</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_face]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">girl_face_text</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_face_text]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">妹子服务</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_service]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">妹子服务种类</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[girl_service_type]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">营业时间</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[business_hours]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">消费</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[fee]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">环境</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[env]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">详细描述</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[desc]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">地址</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[address]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">电话</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[phone]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">状态</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[status]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">价格</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[coin]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">查看次数</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[view]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">confirm</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[confirm]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">fake</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[fake]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">buy</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[buy]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">favorite</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[favorite]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">created_at</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[created_at]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">updated_at</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[updated_at]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">created_time</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[created_time]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">source_link</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[source_link]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">authentication</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[authentication]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">tran_flag</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[tran_flag]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">is_money</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[is_money]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">price</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[price]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">审核拒绝理由</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[reason]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">0: store, 1:personal</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[post_type]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">contact_info</label>
                            <div class="layui-input-block">
                                <input type="text" name="search[contact_info]" placeholder="请输入"
                                       autocomplete="off" class="layui-input">
                            </div>
                        </div>
                        <div class="layui-inline">
                            <label class="layui-form-label">baoyang包养,waiwei外围,loufeng楼凤</label>
                            <div class="layui-input-block">
                                <select name="where[category]">
                                    <option value="">不限</option>
                                    <option value="loufeng">loufeng</option>
                                    <option value="waiwei">waiwei</option>
                                    <option value="baoyang">baoyang</option>
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
                            <th lay-data="{field:'id'}">id</th>
                            <th lay-data="{field:'uid'}">用户id</th>
                            <th lay-data="{field:'check_aff'}">审核aff</th>
                            <th lay-data="{field:'title'}">标题</th>
                            <th lay-data="{field:'type'}">资源类型</th>
                            <th lay-data="{field:'cityCode'}">城市代码</th>
                            <th lay-data="{field:'cityName'}">cityName</th>
                            <th lay-data="{field:'girl_num'}">妹子数量</th>
                            <th lay-data="{field:'girl_age'}">妹子年龄</th>
                            <th lay-data="{field:'girl_face'}">妹子颜值</th>
                            <th lay-data="{field:'girl_face_text'}">girl_face_text</th>
                            <th lay-data="{field:'girl_service'}">妹子服务</th>
                            <th lay-data="{field:'girl_service_type'}">妹子服务种类</th>
                            <th lay-data="{field:'business_hours'}">营业时间</th>
                            <th lay-data="{field:'fee'}">消费</th>
                            <th lay-data="{field:'env'}">环境</th>
                            <th lay-data="{field:'desc'}">详细描述</th>
                            <th lay-data="{field:'address'}">地址</th>
                            <th lay-data="{field:'phone'}">电话</th>
                            <th lay-data="{field:'status'}">状态</th>
                            <th lay-data="{field:'coin'}">价格</th>
                            <th lay-data="{field:'view'}">查看次数</th>
                            <th lay-data="{field:'confirm'}">confirm</th>
                            <th lay-data="{field:'fake'}">fake</th>
                            <th lay-data="{field:'buy'}">buy</th>
                            <th lay-data="{field:'favorite'}">favorite</th>
                            <th lay-data="{field:'created_at'}">created_at</th>
                            <th lay-data="{field:'updated_at'}">updated_at</th>
                            <th lay-data="{field:'created_time'}">created_time</th>
                            <th lay-data="{field:'source_link'}">source_link</th>
                            <th lay-data="{field:'authentication'}">authentication</th>
                            <th lay-data="{field:'tran_flag'}">tran_flag</th>
                            <th lay-data="{field:'is_money'}">is_money</th>
                            <th lay-data="{field:'price'}">price</th>
                            <th lay-data="{field:'reason'}">审核拒绝理由</th>
                            <th lay-data="{field:'post_type'}">0: store, 1:personal</th>
                            <th lay-data="{field:'contact_info'}">contact_info</th>
                            <th lay-data="{field:'category'}">baoyang包养,waiwei外围,loufe</th>
                            <th lay-data="{fixed: 'right',width: 200 ,align:'center', toolbar: '#operate-toolbar'}">操作
                            </th>
                        </tr>
                        </thead>
                    </table>
                    <script type="text/html" id="toolbar">
                        <div class="layui-btn-container">
                            <button class="layui-btn layui-btn-sm" lay-event="add">
                                添加
                            </button>
                            <button class="layui-btn layui-btn-sm" lay-event="delSelect"
                                    data-pk="id">删除所选
                            </button>
                        </div>
                    </script>
                    <script type="text/html" id="operate-toolbar">
                        <div class="operate-toolbar">
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

                    <input                                     placeholder="用户id" name="d"
                                                               value="{{=d.uid }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">审核aff：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="审核aff" name=heck_aff"
                                                               value="{{=d.check_aff }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">标题：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="标题" name="tie"
                                                               value="{{=d.title }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">资源类型：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="资源类型" namepe"
                    value="{{=d.type }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">城市代码：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="城市代码" nametyCode"
                    value="{{=d.cityCode }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">cityName：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="cityName" name="cityName"
                                                               value="{{=d.cityName }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子数量：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="妹子数量" namerl_num"
                    value="{{=d.girl_num }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子年龄：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="妹子年龄" namerl_age"
                    value="{{=d.girl_age }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子颜值：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="妹子颜值" namerl_face"
                    value="{{=d.girl_face }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">girl_face_text：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="girl_face_text" name="girl_face_text"
                                                               value="{{=d.girl_face_text }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子服务：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="妹子服务" namerl_service"
                    value="{{=d.girl_service }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">妹子服务种类：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="妹子服务种类" girl_service_type"
                    value="{{=d.girl_service_type }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">营业时间：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="营业时间" namesiness_hours"
                    value="{{=d.business_hours }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">消费：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="消费" name="fee"
                                                               value="{{=d.fee }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">环境：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="环境" name="env"
                                                               value="{{=d.env }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">详细描述：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="详细描述" namesc"
                    value="{{=d.desc }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">地址：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="地址" name="adess"
                                                               value="{{=d.address }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">电话：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="电话" name="phe"
                                                               value="{{=d.phone }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">状态：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="状态" name="stus"
                                                               value="{{=d.status }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">价格：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="价格" name="co"
                                                               value="{{=d.coin }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">查看次数：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="查看次数" nameew"
                    value="{{=d.view }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">confirm：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="confirm" name="confirm"
                                                               value="{{=d.confirm }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">fake：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="fake" name="fake"
                                                               value="{{=d.fake }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">buy：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="buy" name="buy"
                                                               value="{{=d.buy }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">favorite：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="favorite" name="favorite"
                                                               value="{{=d.favorite }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">created_at：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="created_at" name="created_at"
                                                               value="{{=d.created_at }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">updated_at：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="updated_at" name="updated_at"
                                                               value="{{=d.updated_at }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">created_time：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="created_time" name="created_time"
                                                               value="{{=d.created_time }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">source_link：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="source_link" name="source_link"
                                                               value="{{=d.source_link }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">authentication：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required"                                    placeholder="authentication" name="authentication"
                           value="{{=d.authentication }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">tran_flag：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required"                                    placeholder="tran_flag" name="tran_flag"
                           value="{{=d.tran_flag }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">is_money：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required"                                    placeholder="is_money" name="is_money"
                           value="{{=d.is_money }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">price：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="price" name="price"
                                                               value="{{=d.price }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">审核拒绝理由：</label>
                <div class="layui-input-inline">

                    <input lay-verify="required"                                    placeholder="审核拒绝理由" name="reason"
                           value="{{=d.reason }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">0: store, 1:personal：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="0: store, 1:personal" name="post_type"
                                                               value="{{=d.post_type }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">contact_info：</label>
                <div class="layui-input-inline">

                    <input                                     placeholder="contact_info" name="contact_info"
                                                               value="{{=d.contact_info }}" class="layui-input">

                </div>
            </div>
        </div>


        <div class="layui-form-item">
            <div class="layui-inline">
                <label class="layui-form-label">baoyang包养,waiwei外围,loufeng楼凤：</label>
                <div class="layui-input-inline">

                    <select name="category">
                        <option value="loufeng"
                                {{=(d.category=='loufeng'||!d.category?'selected="true"':'')}}>loufeng</option>
                        <option value="waiwei"
                                {{=(d.category=='waiwei'?'selected="true"':'')}}>waiwei</option>
                        <option value="baoyang"
                                {{=(d.category=='baoyang'?'selected="true"':'')}}>baoyang</option>
                    </select>

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
    layui.use(['table', 'laytpl', 'form', 'lazy', 'laydate', 'layedit','upload','jquery'],
        function (table, laytpl, form, lazy, layDate, layEdit, upload, $) {
            $ = typeof ($) === "undefined" ? window.$ : $;
            let verify = {}

                table.on('tool(table-toolbar)', function (obj) {
                    //注：tool 是工具条事件名，test 是 table 原始容器的属性 lay-filter="对应的值"
                    var data = obj.data,
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
                                .area([1100 +'px' , document.body.offsetHeight + 'px'])
                                .dialog(function (id, ele) {
                                    dialogCallback(id, ele, obj)
                                })
                                .laytpl(function () {
                                    xx.renderSelect(data, $, form);
                                    Util.uploader('button.but-upload-img', "{%url('upload/upload')%}", layui.upload, layui.jquery);
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
                            .area([1100 +'px' , document.body.offsetHeight + 'px'])
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