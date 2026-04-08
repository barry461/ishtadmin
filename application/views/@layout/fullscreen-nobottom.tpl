<!DOCTYPE html>
<html theme='dark'>
    <!-- 页面头部 start -->
    {include file="@common/header" /}
    <!-- 页面头部 end -->

    <!-- 主体内容 start -->
    <body>
        <!--  -->
        <div id="xqbj-container" class="full-container">

            <!-- 头部导航 start -->
            <header class="xqbj-header">
                {include file="@common/header-navbar" /}
            </header>
            <!-- 头部导航 end -->

            <!-- 页面内容 start -->
            <main class="xqbj-main">
                <div class="xqbj-main-container">
                    {__CONTENT__}
                </div>
            </main>
            <!-- 页面内容 end -->
        </div>

    </body>
    <!-- 主体内容 start -->

    <!-- 页面底部 start -->
    {include file="@common/footer" /}
    <!-- 页面底部 end -->
</html>
