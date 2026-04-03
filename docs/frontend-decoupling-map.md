# 前台前后端分离映射说明

更新时间：2026-04-02

## 1. 结论先说

这套 cmy_adminv2 可以做前台前后端分离，但不能直接照搬 91jav_new 的功能文档逐页重构。

原因有两个：

1. 当前仓库的前台业务模型和 91jav_new 不完全一致。
2. 当前仓库已经存在一套偏 App / PWA 的 API，但不是围绕 Web 前台页面设计的标准前台 BFF。

因此，正确做法不是直接重写前台页面，而是先做功能映射：

1. 能直接复用的 API 直接给新前端用。
2. 可部分复用但字段不合适的 API 做轻度改造。
3. 缺失或强依赖 SEO/模板的页面，单独补 Web API 或保留 SSR。

## 2. 现状判断

### 2.1 当前前台不是前后端分离

当前前台仍然是服务端渲染：

1. 首页和下载页主要由 application/controllers/Index.php、application/controllers/Web.php 直接组装数据并输出模板。
2. 公共导航、SEO、主题、页脚、统计代码等逻辑都直接绑定在模板渲染链路中。

### 2.2 当前仓库已有可复用 API 基础

现有 API 主要集中在以下几个控制器：

1. application/modules/Api/controllers/Home.php
2. application/modules/Api/controllers/Contents.php
3. application/modules/Api/controllers/Account.php
4. application/modules/Api/controllers/User.php
5. application/modules/Api/controllers/Community.php
6. application/modules/Api/controllers/Message.php
7. application/modules/Api/controllers/Rank.php

这说明前台分离不是从 0 开始，而是“已有 App API，缺 Web 前台 API 规整层”。

## 3. 功能映射

下表中的“对应 91jav 功能”表示能力层面的参考，不代表当前仓库已有同名页面。

| 前台能力 | 对应 91jav 功能 | cmy_adminv2 现状 | 可复用 API | 结论 |
|---|---|---|---|---|
| 启动配置 / 导航初始化 | F-01 首页、F-60 下载页 | 已有 App/PWA 初始化接口 | Api/Home/config、Api/Home/getContactList | 可直接复用，但字段偏客户端配置 |
| 首页内容流 | F-01 首页、F-11 最近更新、F-14 热门排行 | 已有内容列表、热门列表、分类列表 | Api/Contents/list_category、list_contents、popular；Api/Rank/hot_list、rank_list | 可复用，建议新增 Web 专用聚合接口 |
| 内容详情 | F-10 影片详情、F-30 文章详情 | 已有内容详情接口 | Api/Contents/detail_content | 可直接复用，但字段命名仍偏内容中心 |
| 评论列表 / 回复列表 | F-10、F-30、F-54、F-55 | 已覆盖 | Api/Contents/list_comments、list_reply_comments、create_comment、like_comment | 可直接复用 |
| 搜索结果 / 热搜 | F-15、F-16 | 已覆盖搜索和搜索建议 | Api/Contents/search、search_options、searchBk、searchOld | 可直接复用，建议统一只保留一套搜索入口 |
| 标签 / 分类列表 | F-20、F-22 | 已有分类和标签接口 | Api/Contents/list_category、list_tags、list_contents_tag | 部分复用，当前没有完全对应 91jav 的主题模型 |
| 收藏内容 | F-40、F-50 | 已覆盖 | Api/Contents/trigger_favorite、list_my_favorite | 可直接复用 |
| 点赞内容 | 详情页互动 | 已覆盖 | Api/Contents/trigger_like | 可直接复用 |
| 用户登录 / 注册 | F-45、F-46 | 已覆盖 | Api/Account/registerByPassword、loginByPassword、validateUsername、newValidateUsername | 可直接复用 |
| 邮箱验证码 / 绑定邮箱 | F-58 | 已覆盖大部分 | Api/Account/sendEmailCode、validateEmailCode、bindEmail、changeEmail | 可直接复用 |
| 用户资料 | F-44、F-49 | 已覆盖 | Api/User/userInfo、updateUserInfo | 可直接复用 |
| 关注 / 订阅 | F-51、F-52 | 已有关注类能力 | Api/User/toggle_follow、list_follows | 可复用，但是否等价于“订阅女优”要按业务再确认 |
| 反馈 / 联系我们 | F-56、F-57 | 已覆盖反馈，未看到标准 contact Web API | Api/Message/feeding、feedback；Api/Home/getContactList | 反馈可复用，联系我们需单独整理 |
| 消息中心 / 系统通知 | 个人中心扩展能力 | 已覆盖 | Api/Message/getMessageList、getSystemNoticeList、getUnreadCount | 可直接复用 |
| 社区帖子 / 用户中心扩展 | 文档未重点覆盖 | 已覆盖较完整 | Api/Community/* | 可直接复用，是当前仓库的现有优势能力 |
| 下载页 | F-60、F-61、F-62、F-63 | 当前仍由服务端渲染控制器承担 | IndexController、WebController；Api/Home/config 可提供部分配置 | 建议保留后端输出下载描述文件，前端只接配置数据 |

## 4. 明显缺口

以下是前台真正做分离时必须补的点。

### 4.1 缺少 Web 前台聚合接口

现有 Api/Contents 和 Api/Home 是按 App/PWA 思路设计的，前端如果直接使用，会遇到这些问题：

1. 接口粒度偏底层，一个页面需要多次请求拼装。
2. 返回字段带明显客户端历史包袱，比如 version、oauth_type、app_hide、is_slice 等。
3. 首屏页面需要的 SEO、面包屑、结构化数据、导航元信息没有统一 JSON 接口。

建议补一层 Web 前台聚合接口，例如：

1. api/web/home
2. api/web/content/detail
3. api/web/search
4. api/web/user/center

### 4.2 登录态能力不完整

账号登录注册已有，但目前未明显看到以下标准前台接口：

1. 明确的 logout 接口
2. 忘记密码 / 重置密码接口
3. 修改密码接口
4. Token 刷新 / 续期接口

如果新前端要完整替代旧前台，这几项需要补齐。

### 4.3 SEO 页面不适合直接改纯 SPA

当前前台里 SEO、模板变量、主题配置、统计代码都绑定在 WebController 渲染链路中。

这类页面如果直接改纯 SPA，会有几个问题：

1. 首屏内容抓取变差。
2. 标题、描述、canonical、结构化数据难以稳定输出。
3. 统计代码和主题模板能力要重建。

所以前台分离建议两种路线二选一：

1. 半分离：内容页保留 SSR，用户中心和互动模块前后端分离。
2. 真分离：前端使用 SSR 框架，后端改为纯 API。

就当前仓库而言，更推荐先走半分离。

### 4.4 91jav 文档中的部分功能在当前仓库中并非同一业务对象

91jav_new 的功能文档里有这类页面：

1. 女优列表 / 女优详情
2. 影片详情页
3. 主题 / 标签 / 影评体系

而 cmy_adminv2 当前更偏：

1. 内容流 / 文章流
2. 社区帖子
3. App / PWA / 下载页
4. 会员与互动能力

这意味着那份文档适合拿来做“迁移方法参考”，不适合做“逐项照抄开发清单”。

## 5. 建议的迁移顺序

### 第一阶段：低风险页面先分离

优先迁移下面这些功能：

1. 登录 / 注册
2. 我的信息
3. 我的收藏
4. 评论列表与发表评论
5. 反馈与消息中心

原因：

1. 已有 API 覆盖较多。
2. 对 SEO 影响小。
3. 容易快速验证新前端链路。

### 第二阶段：内容列表和详情页半分离

做法：

1. 页面壳子先保留服务端路由。
2. 页面主体数据改由前端请求 Api/Contents。
3. SEO 信息仍由后端输出。

这一步能在不破坏收录的前提下，把主要交互迁到前端。

### 第三阶段：决定是否走 SSR 全分离

当下面这三件事都准备好后，再考虑完整前后端分离：

1. Web 聚合 API 完整。
2. 登录态、SEO、统计方案成型。
3. 新前端路由和页面组件稳定。

## 6. 最终建议

针对 cmy_adminv2，这次前台分离建议定义为：

1. 后台不动。
2. 前台优先做半分离。
3. 先复用现有 Api 模块。
4. 再补一层 Web 前台聚合 API。
5. 最后再决定是否把首页、列表页、详情页切到 SSR 前端。

如果直接一步走纯前后端分离，成本最高的不是页面重写，而是 SEO、下载页、登录态和聚合接口的重建。
