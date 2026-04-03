<template>
	<!-- 顶部：菜单栏 -->
	<div class="menu-box-1">
		<div class="menu-box-2">
			<!-- 
				菜单：
					mode = 菜单模式（horizontal=水平，vertical=垂直）
					unique-opened = 是否只有菜单打开 
					default-active = 正在高亮的菜单id   
					参考文档：https://element.eleme.cn/#/zh-CN/component/menu
			-->
			<el-menu class="el-menu-style-1" mode="horizontal" :unique-opened="false" :default-active="$root.activeMenuId"
				@select="selectMenu">
				<template v-for="(menu, index) in $root.menuList">
					<!-- 1 如果是子菜单 -->
					<el-menu-item v-if="!menu.childList && menu.isShow !== false && $root.showList.indexOf(menu.id) > -1"
						:index="menu.id + ''" :key="index">
						<span class="menu-i"><i :class="menu.icon" :title="menu.name"></i></span>
						<span class="menu-name">{{ menu.name }}</span>
					</el-menu-item>
					<!-- 1 如果是父菜单 -->
					<el-submenu v-if="menu.childList && menu.isShow !== false && $root.showList.indexOf(menu.id) > -1"
						:index="menu.id + ''" :key="index">
						<template slot="title">
							<span class="menu-i"><i :class="menu.icon" :title="menu.name"></i></span>
							<span class="menu-name">{{ menu.name }}</span>
						</template>
						<!-- 遍历其子项 -->
						<template v-for="(menu2, index2) in menu.childList">
							<!-- 2 如果是子菜单 -->
							<el-menu-item v-if="!menu2.childList && menu2.isShow !== false && $root.showList.indexOf(menu2.id) > -1"
								:index="menu2.id + ''" :key="index2">
								<span class="menu-i"><i :class="menu2.icon" :title="menu2.name"></i></span>
								<span class="menu-name">{{ menu2.name }}</span>
							</el-menu-item>
							<!-- 2 如果是父菜单 -->
							<el-submenu v-if="menu2.childList && menu2.isShow !== false && $root.showList.indexOf(menu2.id) > -1"
								:index="menu2.id + ''" :key="index2">
								<template slot="title">
									<span class="menu-i"><i :class="menu2.icon" :title="menu2.name"></i></span>
									<span class="menu-name">{{ menu2.name }}</span>
								</template>
								<!-- 遍历其子项 -->
								<template v-for="(menu3, index3) in menu2.childList">
									<!-- 3 如果是子菜单 -->
									<el-menu-item
										v-if="!menu3.childList && menu3.isShow !== false && $root.showList.indexOf(menu3.id) > -1"
										:index="menu3.id + ''" :key="index3">
										<span class="menu-i"><i :class="menu3.icon" :title="menu3.name"></i></span>
										<span class="menu-name">{{ menu3.name }}</span>
									</el-menu-item>
									<!-- 3 如果是父菜单 -->
									<el-submenu v-if="menu3.childList && menu3.isShow !== false && $root.showList.indexOf(menu3.id) > -1"
										:index="menu3.id + ''" :key="index3">
										<template slot="title">
											<span class="menu-i"><i :class="menu3.icon" :title="menu3.name"></i></span>
											<span class="menu-name">{{ menu3.name }}</span>
										</template>
										<!-- 4 -->
										<template v-for="(menu4, index4) in menu3.childList">
											<el-menu-item v-if="menu4.isShow !== false && $root.showList.indexOf(menu4.id) > -1"
												:index="menu4.id + ''" :key="index4">
												<span class="menu-i"><i :class="menu4.icon" :title="menu4.name"></i></span>
												<span class="menu-name">{{ menu4.name }}</span>
											</el-menu-item>
										</template>
									</el-submenu>
								</template>
							</el-submenu>
						</template>
					</el-submenu>
				</template>
			</el-menu>
			<!-- tab被拖拽时的遮罩（左拖拽：关闭） -->
			<div class="shade-fox" v-if="$root.isDrag" @dragover="$event.preventDefault();"
				@drop="$event.preventDefault(); $event.stopPropagation(); $root.$refs['com-right-menu'].rightTab = $root.dragTab; $root.$refs['com-right-menu'].right_close();">
				<span style="font-size: 16px;">关闭</span>
			</div>
		</div>
	</div>
</template>

<script>
module.exports = {
	data() {
		return {

		}
	},
	methods: {
		// 点击子菜单时触发的回调  
		// 参数：index=点击菜单index标识（不是下标，是菜单id）, 
		// 		indexArray=所有已经打开的菜单id数组，形如：['1', '1-1', '1-1-1'] 
		selectMenu: function (index, indexArray) {
			this.$root.showMenuById(index);
		},
	},
	created() {
	}
}
</script>

<style scoped>
/* 水平菜单布局 */
.menu-box-1 {
	width: 100%;
	height: 100%;
	overflow-x: auto;
	overflow-y: hidden;
}

.menu-box-2 {
	width: 100%;
	height: 100%;
	white-space: nowrap;
}

.menu-box-1 i[class^=el-icon-] {
	font-size: 16px;
}

.menu-box-2 .menu-i {
	display: inline-block;
	vertical-align: middle;
	margin-right: 5px;
}

/* 动画速度加快 */
.menu-box-1,
.menu-box-2 * {
	transition: all 0.2s;
}

/* 隐藏边框 */
.el-menu {
	border: 0px;
	background-color: transparent;
	display: flex;
	overflow-y: hidden;
	overflow-x: auto;
}

/* 水平模式下的菜单高度 */
.el-menu-style-1.el-menu--horizontal {
	height: 100%;
	border-bottom: none;
}

.el-menu-style-1.el-menu--horizontal>.el-menu-item,
.el-menu-style-1.el-menu--horizontal>.el-submenu .el-submenu__title {
	height: 100%;
	line-height: var(--nav-top-height, 60px);
	border-bottom: 3px solid transparent;
	transition: all 0.3s ease;
}

/* 增强选中菜单的视觉效果 */
.el-menu-style-1.el-menu--horizontal>.el-menu-item.is-active {
	position: relative;
}

.el-menu-style-1.el-menu--horizontal>.el-menu-item.is-active::after {
	content: '';
	position: absolute;
	bottom: 0;
	left: 0;
	right: 0;
	height: 3px;
	background-color: currentColor;
}

/* 子菜单样式 */
.el-submenu .el-menu-item {
	height: 40px !important;
	line-height: 40px !important;
}

/* 隐藏遮罩 */
.shade-fox {
	display: none !important;
}
</style>
