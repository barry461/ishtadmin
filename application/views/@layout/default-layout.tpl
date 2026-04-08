<!DOCTYPE html>
<html lang="zh-CN">
{include file="@common/header" /}
<body>
<!-- 公告弹窗脚本 -->
<script>
    var advertiseList = [
        {literal}{key: 1, url: '{/literal}__IMAGE_DOMAIN__{literal}/hc237/uploads/default/other/2026-03-05/989d2069b5f5ae542fe3666699fe1c34.gif', href: ''},{/literal},
        {literal}{key: 2, url: '{/literal}__IMAGE_DOMAIN__{literal}/hc237/uploads/default/other/2026-02-03/0c1051fe9e21351edc253670942932d9.gif', href: 'https://4yzu3y.top'},{/literal},
        {literal}{key: 3, url: '{/literal}__IMAGE_DOMAIN__{literal}/hc237/uploads/default/other/2026-02-10/f005fae996a02240db527a9b0ca131be.gif', href: 'https://182.0497845.cc'},{/literal},
        {literal}{key: 4, url: '{/literal}__IMAGE_DOMAIN__{literal}/hc237/uploads/default/other/2025-12-22/84883dfbe5c7a75fa669ad59a2c144e5.gif', href: 'https://pg61.8615481.cc'},{/literal}
    ];
</script>

<!-- 侧边导航 -->
{include file="@common/aside-navbar" /}

<!-- 顶部导航 -->
<header id="site-header" class="site-header">
    {include file="@common/header-navbar" /}
</header>

<!-- 页面主内容 -->
<div id="site-content" class="site-content">
    {__CONTENT__}
</div>

<!-- 页脚 -->
{include file="@components/component-footer" /}

<!-- SVG图标库 + 脚本 -->
{include file="@common/footer" /}
</body>
</html>
