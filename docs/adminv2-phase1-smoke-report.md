# AdminV2 一期联调记录

更新时间：2026-04-03

## 1. 当前结论

后台一期联调已进入执行阶段，但目前被“缺少有效管理员登录态”阻塞。

已确认：

1. AdminV2 API 域名可访问。
2. 登录接口可访问并可返回明文调试 JSON。
3. 前端内置默认测试账号 `kele / kele123` 当前无法登录，接口返回“账号或密码错误”。
4. 本机 `http://127.0.0.1/admin/` 当前不可访问，说明本地并没有直接跑起可用的后台站点入口。

因此，这一轮还不能进入已登录页面的真实点击联调。

## 2. 已执行环境验证

### 2.1 后台页面入口探测

- 远程域名根路径：可连通，但当前返回 404。
- 远程常见后台入口：
  1. `https://gd-staff.gdcm01.com/admin/` -> 404
  2. `https://gd-staff.gdcm01.com/admin/index.html` -> 404
  3. `https://gd-staff.gdcm01.com/admin/login.html` -> 404
- 本机常见后台入口：
  1. `http://127.0.0.1/admin/` -> 无法连接到远程服务器
  2. `http://127.0.0.1/admin/index.html` -> 无法连接到远程服务器

### 2.2 前端实际 API 基址确认

已确认 [public/admin/static/sa.js](public/admin/static/sa.js) 当前开发配置指向：

`https://api-yaf2.yvyjdhr.com/adminv2.php/adminv2`

这说明 AdminV2 前端当前实际依赖的是远程 API 域，而不是 [conf/app.ini](conf/app.ini) 里的 `admin.site_url`。

### 2.3 登录接口验证

已对登录接口做明文调试请求验证：

- 接口：`POST /login/dologin`
- 基址：`https://api-yaf2.yvyjdhr.com/adminv2.php/adminv2`
- 请求方式：明文 `application/x-www-form-urlencoded`
- 调试参数：`debug=fasdf4ed@1\`!`
- 测试账号：`username=kele`, `password=kele123`, `skip_verify=1`

接口返回：

```json
{
  "data": [],
  "status": 0,
  "msg": "账号或密码错误",
  "crypt": true
}
```

结论：

1. 接口链路本身是通的。
2. 默认测试账号已失效，或当前环境并不存在这条管理员记录。
3. 没有有效 token 之前，无法继续验证管理员、角色、内容等已登录页面接口。

## 3. 当前阻塞项

当前阻塞项只有一个，但它会影响整轮一期联调：

1. 缺少可用的管理员账号密码，无法获取 token。

## 4. 建议解除方式

任选其一即可继续：

1. 提供一个当前环境可用的管理员账号密码。
2. 在当前数据库里确认一条可登录管理员，并告知账号信息。
3. 如果你本地有已登录浏览器会话，也可以直接让我继续做“基于页面的人工联调记录”，但这仍需要你提供可访问入口。

## 5. 已规划的下一步

一旦拿到有效登录态，按 [docs/adminv2-phase1-smoke-checklist.md](docs/adminv2-phase1-smoke-checklist.md) 继续执行第一组页面：

1. 控制台概览
2. 个人设置
3. 管理员管理
4. 角色列表
5. 角色编辑与权限分配

## 6. 执行状态

| 组别 | 页面/模块 | 状态 | 备注 |
|---|---|---|---|
| 环境验证 | API 可达性 | 已完成 | API 域名可访问 |
| 环境验证 | 登录接口可达性 | 已完成 | 登录接口可正常返回 JSON |
| 环境验证 | 默认测试账号登录 | 阻塞 | `kele / kele123` 返回账号或密码错误 |
| 第一组 | 控制台与个人信息 | 未开始 | 等待有效登录态 |
| 第二组 | 管理员与角色权限 | 未开始 | 等待有效登录态 |
| 第三组 | 内容主链路 | 未开始 | 等待有效登录态 |
