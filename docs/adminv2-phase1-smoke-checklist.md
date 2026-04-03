# AdminV2 一期联调清单

更新时间：2026-04-03

## 1. 用途

这份清单用于后台一期页面的真实点击联调。

当前代码已经完成一轮静态收口，但以下项目大多还没有做运行态验证。

目标不是一次性覆盖全后台，而是先验证一期高频页面是否已经满足：

1. 能正常打开。
2. 列表能正确加载。
3. 查询、重置、分页、弹窗能正常工作。
4. 保存、删除、状态切换等关键动作能命中当前 AdminV2 接口。
5. 页面不会再因为旧返回结构、旧字段名、旧成功判断导致异常。

## 2. 前置条件

联调前先确认下面几项：

1. 已能正常登录后台 AdminV2。
2. 数据库有基础测试数据：管理员、角色、分类、标签、文章、评论、独立页、附件。
3. 上传目录和静态资源目录可写。
4. 当前环境已启用 AdminV2 路由，相关接口可访问。
5. 浏览器打开控制台，重点关注真实报错，不要把普通日志当成问题。近期已清理大部分联调噪音。

## 3. 执行顺序

建议按下面顺序联调，前一组通过后再进入下一组：

1. 控制台与个人信息
2. 管理员与角色权限
3. 内容主链路：分类、标签、文章列表、文章编辑器、单页编辑器、评论
4. 系统设置：基础设置、SEO、外观
5. 内容周边：附件、独立页面、自定义排序、内链、广告、操作日志、作者列表
6. 统计页：用户统计、蜘蛛日志、在线统计

## 4. 全局验收口径

每个页面至少检查下面几项：

1. 首次进入页面无白屏、无明显报错。
2. 数据列表或详情数据与接口返回一致。
3. 查询后结果变化正确。
4. 重置后参数恢复默认，列表重新加载。
5. 弹窗关闭后再次打开，不应残留上一次校验错误或旧数据。
6. 保存成功后页面提示正确，必要时列表刷新。
7. 失败时有明确错误提示，不应静默失败。

## 5. 页面清单

### 5.1 控制台与个人信息

#### 控制台概览

- 页面：public/admin/sa-view/console/overview.html
- 关键接口：/statistics/siteOverview, /contents/recent, /comments/recent, /statistics/todayAuthorRanking, /statistics/monthAuthorRanking
- 检查项：
  1. [ ] 概览统计卡片正常显示文章数、评论数、分类数。
  2. [ ] 最近文章卡片能展示最新文章列表。
  3. [ ] 最近评论卡片能展示最新评论列表。
  4. [ ] 今日作者排行和本月作者排行正常显示。
  5. [ ] 快捷入口点击后能正常跳转目标菜单。

#### 个人设置

- 页面：public/admin/sa-view/user/user-profile.html
- 关键接口：/account/profile, /statistics/siteOverview, /setting/updateNickname, /setting/updatePassword, /setting/updateSecret
- 检查项：
  1. [ ] 页面能正确回显当前管理员昵称、角色信息、最后登录时间。
  2. [ ] 修改昵称后提示成功，刷新后可见新值。
  3. [ ] 修改密码时错误密码能正确提示，正确提交后成功。
  4. [ ] 修改谷歌密钥时成功和失败提示正确。

### 5.2 管理员与角色权限

#### 管理员管理

- 页面：public/admin/sa-view/setting/admin-manage.html
- 关键接口：/account/list, /account/save, /account/delete, /account/ban, /role/list
- 检查项：
  1. [ ] 列表加载正常，角色筛选和状态筛选生效。
  2. [ ] 新增管理员时角色可从下拉选择，保存后列表刷新。
  3. [ ] 编辑管理员后角色、状态变更生效。
  4. [ ] 启用/禁用操作成功后状态立即刷新。
  5. [ ] 单个删除和批量删除成功。

#### 角色列表

- 页面：public/admin/sa-view/role/role-list.html
- 关键接口：/role/list, /role/delete
- 检查项：
  1. [ ] 列表分页、关键词查询正常。
  2. [ ] 角色权限数量显示正常。
  3. [ ] 删除角色后列表刷新。

#### 角色新增/编辑

- 页面：public/admin/sa-view/role/role-add.html
- 关键接口：/role/detail, /role/save
- 检查项：
  1. [ ] 新增角色可正常保存。
  2. [ ] 编辑角色时可正确回显详情。
  3. [ ] 保存后列表可见变更。

#### 权限分配

- 页面：public/admin/sa-view/role/menu-setup.html
- 关键接口：/role/permissionTree, /role/detail, /role/save
- 检查项：
  1. [ ] 权限树正常加载。
  2. [ ] 已有权限勾选状态回显正确。
  3. [ ] 修改权限后保存成功，重新进入可看到最新勾选状态。

### 5.3 内容主链路

#### 分类管理

- 页面：public/admin/sa-view/manage/category-list.html
- 关键接口：/category/list, /category/save, /category/delete
- 检查项：
  1. [ ] 列表按树结构显示正确。
  2. [ ] 新增一级分类和子分类成功。
  3. [ ] 编辑分类时父级分类回显正确。
  4. [ ] 删除父分类时层级联动符合预期。

#### 标签管理

- 页面：public/admin/sa-view/manage/tag-list.html
- 关键接口：/tag/list, /tag/save, /tag/delete
- 检查项：
  1. [ ] 关键词查询与重置正常。
  2. [ ] 新增标签成功。
  3. [ ] 编辑标签后刷新正确。
  4. [ ] 单个删除和批量删除成功。

#### 评论管理

- 页面：public/admin/sa-view/manage/comment-list.html
- 关键接口：/comments/list, /comments/save, /comments/approve, /comments/waiting, /comments/spam, /comments/filter, /comments/delete, /comments/deleteSameIp, /comments/banIp, /comments/banIpRange
- 检查项：
  1. [ ] 列表分页、筛选、待审核数量刷新正常。
  2. [ ] 评论状态切换到通过、待审核、垃圾、过滤都成功。
  3. [ ] 新增评论成功。
  4. [ ] 删除、删除同 IP 评论成功。
  5. [ ] 单 IP 和 IP 段封禁成功后列表刷新。

#### 文章列表

- 页面：public/admin/sa-view/manage/article-list.html
- 关键接口：/contents/list, /contents/authors, /contents/categories, /contents/delete, /contents/updateStatus, /contents/batchSetCategory, /contents/toggleHotSearch, /contents/updateSortField
- 检查项：
  1. [ ] 列表、作者选项、分类选项正常加载。
  2. [ ] 查询、重置、分页正常。
  3. [ ] 批量状态修改成功。
  4. [ ] 批量分类设置和清空分类成功。
  5. [ ] 热搜设置成功且列表状态更新。
  6. [ ] 批量删除成功。
  7. [ ] 自定义排序字段可编辑并保存。

#### 文章编辑器

- 页面：public/admin/sa-view/article/article-write.html
- 关键接口：/contents/getById, /contents/save, /contents/preview, /contents/authors, /contents/categories, /upload/uploadLocal
- 检查项：
  1. [ ] 新建文章可正常保存草稿。
  2. [ ] 新建文章可直接发布。
  3. [ ] 待审核状态保存后跳转正确。
  4. [ ] 编辑已有文章时详情回填正常。
  5. [ ] 作者、分类、自定义字段、热搜等字段提交正确。
  6. [ ] 主图/附件上传成功并正确写回。
  7. [ ] 预览草稿可打开有效地址。

#### 单页编辑器

- 页面：public/admin/sa-view/article/page-write.html
- 关键接口：/independent/detail, /independent/save, /contents/authors, /upload/uploadLocal
- 检查项：
  1. [ ] 新建单页可保存草稿。
  2. [ ] 新建单页可直接发布。
  3. [ ] 编辑单页时详情回填正确。
  4. [ ] 主图上传成功并正确预览。
  5. [ ] 自定义字段提交和回显一致。

### 5.4 系统设置

#### 基础设置

- 页面：public/admin/sa-view/setting/basic.html
- 关键接口：/setting/get, /setting/doSet
- 检查项：
  1. [ ] 页面能正确回显站点设置。
  2. [ ] description 与 siteDes 修改后保持一致。
  3. [ ] 保存成功后刷新仍保持最新值。

#### 中转设置

- 页面：public/admin/sa-view/setting/basic-zz.html
- 关键接口：/setting/get, /setting/doSet
- 检查项：
  1. [ ] description 与 siteDes 回显一致。
  2. [ ] 保存成功后刷新稳定。

#### SEO 设置

- 页面：public/admin/sa-view/setting/seo.html
- 关键接口：/seotpl/list, /seotpl/detail, /seotpl/save, /seotpl/delete, /seostatcode/list
- 检查项：
  1. [ ] SEO 模板列表正常加载。
  2. [ ] 新增模板时 Monaco 编辑器正常初始化。
  3. [ ] 连续打开不同模板编辑时编辑器不会残留旧内容。
  4. [ ] 保存成功后列表刷新。
  5. [ ] 删除模板成功。
  6. [ ] 统计代码列表正常加载。

#### 外观设置

- 页面：public/admin/sa-view/appearance/appearance.html
- 关键接口：/theme/getImages, /theme/saveImages, /pageset/getNav, /pageset/saveNav, /pageset/getPage, /pageset/savePage, /upload/uploadLocal
- 检查项：
  1. [ ] 主题图片正常回显。
  2. [ ] 上传 logo、占位图、favicon 等图片成功。
  3. [ ] 保存图片配置后刷新仍可回显。
  4. [ ] 导航设置保存成功，重新打开后数据结构不乱。
  5. [ ] 页面设置保存成功。

### 5.5 内容周边

#### 文件管理

- 页面：public/admin/sa-view/manage/file-list.html
- 关键接口：/attachment/list
- 检查项：
  1. [ ] 上传者和所属内容列显示正确。
  2. [ ] 查询和重置正常。
  3. [ ] 切片状态和上传状态显示正常。

#### 独立页面列表

- 页面：public/admin/sa-view/manage/independent-page-list.html
- 关键接口：/independent/list, /independent/delete, /independent/updateStatus
- 检查项：
  1. [ ] 列表分页解析正确。
  2. [ ] 进入编辑页能正确带上 cid。
  3. [ ] 状态切换成功。
  4. [ ] 单个删除和批量删除成功。

#### 自定义排序

- 页面：public/admin/sa-view/manage/custom-sort.html
- 关键接口：/sortfield/list, /sortfield/save, /sortfield/toggleStatus
- 检查项：
  1. [ ] 列表加载正常。
  2. [ ] 新增和编辑成功。
  3. [ ] 状态点击切换成功。

#### 广告管理

- 页面：public/admin/sa-view/manage/ad-manage.html
- 关键接口：/advert/list, /advert/positionOptions
- 检查项：
  1. [ ] 关键词搜索正常，不再依赖 title 字段。
  2. [ ] 重置后恢复默认查询条件。
  3. [ ] 广告位选项正常加载。

#### 操作日志

- 页面：public/admin/sa-view/manage/operation-log.html
- 关键接口：/adminlog/list
- 检查项：
  1. [ ] 列表加载、查询、重置正常。
  2. [ ] 打开详情时 context 为字符串、对象、空值都不报错。

#### 内链管理

- 页面：public/admin/sa-view/internal-link/internal-link-manage.html
- 关键接口：/internallink/listAjax, /internallink/save, /internallink/del, /internallink/delAll
- 检查项：
  1. [ ] 列表按 pageJson 正常解析。
  2. [ ] 搜索会重置页码。
  3. [ ] target_url 可接受相对路径和 http(s) 链接。
  4. [ ] 新增、编辑、单删、批删都成功。

#### 作者列表

- 页面：public/admin/sa-view/manage/user-list.html
- 关键接口：/users/authors, /users/groups
- 检查项：
  1. [ ] 作者列表正常加载。
  2. [ ] 用户组筛选正常。
  3. [ ] 用户组名称显示正确。

### 5.6 统计页

#### 用户统计

- 页面：public/admin/sa-view/manage/user-statistics.html
- 关键接口：/users/authors, /users/saveAuthor
- 检查项：
  1. [ ] 列表分页按 pageJson 正常显示。
  2. [ ] 编辑作者信息并保存成功。
  3. [ ] 保存后列表刷新可见变更。

#### 蜘蛛日志

- 页面：public/admin/sa-view/manage/spider-log.html
- 关键接口：/spiderlog/stats, /spiderlog/list
- 检查项：
  1. [ ] 统计概览正常显示。
  2. [ ] 查看明细弹窗后分页正常。
  3. [ ] 日期筛选生效。

#### 在线统计

- 页面：public/admin/sa-view/manage/online-statistics.html
- 关键接口：/statistics/onlineUsers
- 检查项：
  1. [ ] 默认最近 7 天数据能正常显示。
  2. [ ] 自定义日期范围查询生效。
  3. [ ] 图表切换每日总量、平均、峰值模式正常。
  4. [ ] 空数据时页面正常，不再出现假数据。

## 6. 建议记录方式

联调时建议给每个页面记录下面 4 个字段：

1. 结果：通过 / 阻塞 / 部分通过
2. 复现步骤
3. 实际结果
4. 涉及接口和截图

## 7. 当前已知风险

当前仍需重点关注这几类问题：

1. 编辑器页的上传、自动保存、草稿恢复联动是否完全稳定。
2. 外观设置中的复杂 JSON 字段在脏数据环境下是否会触发格式问题。
3. 角色权限与管理员体系是否存在权限数据边界情况。
4. 某些低频旧页面仍可能保留 mockData 或旧后台实现，但暂未纳入一期联调范围。
