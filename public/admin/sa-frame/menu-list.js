// 一个菜单可以包括的所有属性 
// {
// 	id: '12345',		// 菜单id, 必须唯一
// 	name: '用户中心',		// 菜单名称, 同时也是tab选项卡上显示的名称
// 	icon: 'el-icon-user',	// 菜单图标, 参考地址:  https://element.eleme.cn/#/zh-CN/component/icon
//	info: '管理所有用户',	// 菜单介绍, 在菜单预览和分配权限时会有显示 
// 	url: 'sa-view/user/user-list.html',	// 菜单指向地址
// 	parentId: 1,			// 所属父菜单id, 如果指定了一个值, sa-admin在初始化时会将此菜单转移到指定菜单上 
// 	isShow: true,			// 是否显示, 默认true
// 	isBlank: false,		// 是否属于外部链接, 如果为true, 则点击菜单时从新窗口打开 
// 	childList: [			// 指定这个菜单所有的子菜单, 子菜单可以继续指定子菜单, 至多支持四级菜单
// 		// .... 
// 	],
//	click: function(){}		// 点击菜单执行一个函数 
// }

// 定义菜单列表 
var menuList = [
	{
		id: '1',
		name: '控制台',
		info: '首页仪表盘',
		childList: [
			{ id: '1-1', name: '概要', icon: 'el-icon-data-board', url: 'sa-view/console/overview.html' },
			{ id: '1-2', name: '个人设置', icon: 'el-icon-user', url: 'sa-view/user/user-profile.html' },
			{ id: '1-3', name: '插件', icon: 'el-icon-box', url: 'sa-view/plugins/plugins-list.html', isShow: false },
			{ id: '1-4', name: '外观', icon: 'el-icon-brush', url: 'sa-view/appearance/appearance.html' },
			{ id: '1-5', name: '备份', icon: 'el-icon-copy-document', url: 'sa-view/role/menu-list.html', isShow: false }
		]
	},
	{
		id: '2',
		name: '撰写',
		info: '内容发布',
		childList: [
			{ id: '2-1', name: '新文章', icon: 'el-icon-edit', url: 'sa-view/article/article-write.html' },
			{ id: '2-2', name: '新增页面', icon: 'el-icon-document-add', url: 'sa-view/article/page-write.html' },
			{ id: '2-3', name: '编辑页面', icon: 'el-icon-document', url: 'sa-view/article/page-write.html', isShow: false },
			// “继续编辑xxx文章”入口始终放在撰写菜单的最后
			{ id: '2-1-0', name: '继续编辑文章', icon: 'el-icon-edit', url: 'sa-view/article/article-write.html', isShow: false },
			{ id: '2-1-1', name: '编辑文章', icon: 'el-icon-edit', url: 'sa-view/article/article-write.html', isShow: false }
		]
	},
	{
		id: '3',
		name: '管理',
		info: '内容与用户管理',
		childList: [
			{ id: '3-1', name: '作者管理和远程发布端用户管理', icon: 'el-icon-data-line', url: 'sa-view/manage/user-statistics.html' },
			{ id: '3-2', name: '在线统计', icon: 'el-icon-monitor', url: 'sa-view/manage/online-statistics.html' },
			{ id: '3-3', name: '文章', icon: 'el-icon-document', url: 'sa-view/manage/article-list.html' },
			{ id: '3-5', name: '独立页面', icon: 'el-icon-document-copy', url: 'sa-view/manage/independent-page-list.html' },
			{ id: '3-6', name: '评论', icon: 'el-icon-chat-line-square', url: 'sa-view/manage/comment-list.html' },
			{ id: '3-7', name: '分类', icon: 'el-icon-collection-tag', url: 'sa-view/manage/category-list.html' },
			{ id: '3-8', name: '自定义排序', icon: 'el-icon-sort', url: 'sa-view/manage/custom-sort.html' },
			{ id: '3-9', name: '标签', icon: 'el-icon-price-tag', url: 'sa-view/manage/tag-list.html' },
			{ id: '3-10', name: '文件', icon: 'el-icon-folder', url: 'sa-view/manage/file-list.html' },
			{ id: '3-12', name: '缓存清理', icon: 'el-icon-delete', url: 'sa-view/manage/cache-clear.html' },
			{ id: '3-13', name: '广告管理', icon: 'el-icon-picture-outline', url: 'sa-view/manage/ad-manage.html' },
			{ id: '3-14', name: '操作日志', icon: 'el-icon-document-copy', url: 'sa-view/manage/operation-log.html' },
			{ id: '3-15', name: '蜘蛛访问记录', icon: 'el-icon-aim', url: 'sa-view/manage/spider-log.html' }
		]
	},
	{
		id: '4',
		name: '设置',
		info: '系统设置',
		childList: [
			{ id: '4-1', name: '网站设置', icon: "el-icon-setting", url: 'sa-view/setting/basic.html' },
			{ id: '4-2', name: '网站中转设置', icon: "el-icon-share", url: 'sa-view/setting/basic-zz.html' },
			{ id: '4-3', name: '管理员管理', icon: "el-icon-user-solid", url: 'sa-view/setting/admin-manage.html' },
			{ id: '4-4', icon: "el-icon-edit-outline", name: 'SEO设置', url: 'sa-view/setting/seo.html' },
		]
	},
	{
		id: '5',
		name: '站内链接',
		info: '站内内链管理',
		childList: [
			{
				id: '5-1',
				name: '内链管理',
				icon: 'el-icon-link',
				url: 'sa-view/internal-link/internal-link-manage.html'
			}
		]
	}
]
