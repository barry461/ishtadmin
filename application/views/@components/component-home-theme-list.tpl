<div class="home-theme-section">
    <div class="theme-main-container">
        <div class="theme-grid-wrapper">
            <div class="home-list-title">
                <div class="title-left">
                    <h2>影片主题</h2>
                </div>
                <div class="title-right">
                    <a href="/theme/list" class="more-text">
                        <span>更多</span>
                        <img src="__ROOT_PATH__/__base/images/icon-arror-r.png" title="更多" alt="更多" />
                    </a>
                </div>
            </div>
            <div class="theme-grid-list">
                {foreach name="theme_list" item="theme"}
                <a href="{$theme.url|default='#'}" class="theme-item">
                    <div class="theme-item-img">
                        <img x-image-loader-url="__ROOT_PATH__/{$theme.img|default=''}" alt="{$theme.title|default=''}" />
                    </div>
                    <div class="theme-item-info">
                        <h3 class="theme-item-title">{$theme.title|default=''}</h3>
                        <p class="theme-item-count">{$theme.count|default=''}</p>
                    </div>
                </a>
                {/foreach}
            </div>
        </div>
        <div class="theme-collection-wrapper">
            <div class="collection-title">
                <h2>专题合集</h2>
            </div>
            <div class="collection-list-inner">
                <a href="/collection/qiangjian" class="collection-card collection-card-pink">
                    <div class="collection-icon">🔥</div>
                    <h3 class="collection-card-title">#强奸</h3>
                    <p class="collection-card-desc">
                        强奸最新在 91JAV 中仅作为虚构剧情中突袭材料的分类标签使用，所有内容均为成年演员在严格的影视设定，不对应任何现实行为。该标签主要服务于娱幻饰选求，用于区分具有强烈戏剧冲突和情结张力感剧情内容，帮助用户致效事强度进行选择。
                    </p>
                </a>
                <a href="/collection/bulun" class="collection-card collection-card-blue">
                    <div class="collection-icon">👍</div>
                    <h3 class="collection-card-title"># 不伦</h3>
                    <p class="collection-card-desc">
                        不伦最新用于归类虚拟剧情中的伦理冲突类题材内容，强调人物关系的复杂性和戏剧张力。该标签本质是剧情分类工具，便于用户根据故事走向与选择效果。91JAV 将近期更新的不伦题材内容统一整理，提高整体浏览与选择效率。
                    </p>
                </a>
            </div>
        </div>
    </div>
</div>
