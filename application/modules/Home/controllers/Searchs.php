<?php

/**
 * SearchsController.php 搜索
 * @author  chenmoyuan
 */

use Tracking\Helper;

/**
 * 搜索
 */
class SearchsController extends WebController{

    public function searchAction()
    {
        $keyword = trim($this->getRequest()->getParam('keyword'));

        if ($keyword === '') {
            return $this->x404();
        }

        // 将繁体转换为简体进行搜索
        $simplifiedKeyword = $this->convertToSimplified($keyword);
        $escapedKeyword = addcslashes($simplifiedKeyword, '\\%_');
//        trigger_log("原始关键词: {$keyword}, 转换后: {$simplifiedKeyword}, 转义后: {$escapedKeyword}");
        // var_dump($escapedKeyword);die();
        $this->page = (int) ($this->getRequest()->getParam('page', 1));
        
        // 处理第一页重定向
        if ($this->handleFirstPageRedirect('search', [$keyword])) {
            return;
        }
        
        $limit = $this->limit;

        $query = ContentsModel::queryWebPost()
            ->where('title', 'like', "%{$escapedKeyword}%", 'and', '\\');

        $cacheKeyBase = 'search-list-'.md5(strtolower($simplifiedKeyword));
        $listKey = "{$cacheKeyBase}:page:{$this->page}";
        $countKey = "{$cacheKeyBase}:total";
        
        // cached('')->clearGroup('gp:search-list', 'gp:search-count');

        $list = cached($listKey)
            ->group('gp:search-list')
            ->chinese("WEB端搜索列表缓存")
            ->fetchPhp(function () use ($query, $limit) {
                $result = $query
                    ->selectRaw('cid,title,created,`order`,type,status,commentsNum,is_home,home_top,is_slice,authorId,fake_view,view')
                    ->with([
                        'categoryRelationships.category', 'fields', 'author',
                    ])
                    ->orderByDesc('created')
                    ->forPage($this->page, $limit)
                    ->get();
                
//                trigger_log("SQL查询结果数量: " . $result->count());
                return $result;
            });

        $count = cached($countKey)
            ->group('gp:search-count')
            ->chinese("WEB端搜索列表分页缓存")
            ->fetchPhp(function () use ($query) {
                return $query->count();
            });

        // var_dump($count);die();

        if (empty($count)){
            return $this->x404();
        }

        $defaultBanner = 'https://pic.gsjqen.cn/upload_01/xiao/20241012/2024101212472264626.jpeg';
        $banner = $defaultBanner;

        if ($count > 0 && !empty($list) && $list->isNotEmpty()) {
            $randIndex = array_rand($list->all());
            $item = $list[$randIndex] ?? null;

            if (!empty($item->fields[0]->str_value)) {
                $banner = $item->fields[0]->str_value;
            }
        }

        $pageResult = $this->pageAssign($count, $limit);
        if ($pageResult === true) {
            return true;
        }
        list($this->page, $totalPage) = $pageResult;
        
        // 基础变量
        $brand = options('brand', '') ?: options('title', '007吃瓜');
        $favicon = options('favicon_ico', '/favicon.ico');
        $logoUrl = options('logo_url', '');
        $homeUrl = rtrim(options('siteUrl'), '/') . '/';
        $twitterSite = options('twitter_site', '@your_handle');
        
        // 生成分页相关URL（关键词会自动进行URL编码）
        $permanent_domain = rtrim(options('siteUrl'), '/');
        $canonical_url = $this->page > 1 
            ? $permanent_domain . rtrim(url('search.page', [$keyword, $this->page], false), '/') . '/'
            : $permanent_domain . rtrim(url('search', [$keyword], false), '/') . '/';
        $prev_link = '';
        $next_link = '';
        
        if ($this->page > 1) {
            if ($this->page == 2) {
                $prev_url = $permanent_domain . rtrim(url('search', [$keyword], false), '/') . '/';
            } else {
                $prev_url = $permanent_domain . rtrim(url('search.page', [$keyword, $this->page - 1], false), '/') . '/';
            }
            $prev_link = '<link rel="prev" href="' . $prev_url . '" />';
        }
        
        if ($this->page < $totalPage) {
            $next_url = $permanent_domain . rtrim(url('search.page', [$keyword, $this->page + 1], false), '/') . '/';
            $next_link = '<link rel="next" href="' . $next_url . '" />';
        }
        
        // config 模板
        $pageLabel = $this->page > 1 ? "第{$this->page}页" : '';
        $remark = SeoTplModel::seo_config('search_list');
        $remarkVars = $this->parseRemarkVariables($remark);
        $titleTpl = $remarkVars['TITLE'] ?? '搜索结果 - {KEYWORD}相关内容 - {PAGE} | 畅享热门吃瓜黑料 - {BRAND}';
        $descTpl = $remarkVars['DESCRIPTION'] ?? '展示关于「{KEYWORD}」的搜索结果{PAGE}...';
        $keywordsTpl = $remarkVars['KEYWORDS'] ?? '{KEYWORD},{KEYWORD}爆料,{KEYWORD}合集,黑料网,吃瓜网,成人视频,禁漫小说,{BRAND}';

        $title = $this->replaceVariables($titleTpl, [ 'KEYWORD' => $keyword, 'PAGE' => $pageLabel, 'BRAND' => $brand ]);
        $description = $this->replaceVariables($descTpl, [ 'KEYWORD' => $keyword, 'PAGE' => $pageLabel ]);
        $keywords = $this->replaceVariables($keywordsTpl, [ 'KEYWORD' => $keyword, 'BRAND' => $brand ]);

        // LOGO 绝对 URL
        $logoAbs = $logoUrl;
        if ($logoAbs && !preg_match('#^https?://#i', $logoAbs)) {
            $logoAbs = rtrim($permanent_domain, '/') . '/' . ltrim($logoAbs, '/');
        }

        // 获取SEO模版并替换变量（搜索页面不输出结构化数据）
        $header = SeoTplModel::seo_tpl('search_list');
        // 移除所有结构化数据相关的内容
        $header = preg_replace('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>.*?<\/script>/is', '', $header);
        $header = preg_replace('/\{LD_JSON\}/', '', $header);
        $replace = [
            '{TITLE}' => htmlspecialchars(filter_pure_text($title)),
            '{DESCRIPTION}' => htmlspecialchars(filter_pure_text($description)),
            '{KEYWORDS}' => htmlspecialchars($keywords),
            '{CANONICAL}' => $canonical_url,
            '{PREV}' => $prev_link,
            '{NEXT}' => $next_link,
            '{BRAND}' => htmlspecialchars($brand),
            '{FAVICON}' => $favicon,
            '{LOGOURL}' => $logoAbs, // 使用绝对路径的logo
            '{TWITTER_SITE}' => $twitterSite,
            '{TWITTER_IMAGE}' => $logoAbs, // Twitter图片使用绝对路径的logo
            '{OG_IMAGE}' => $logoAbs, // OG图片使用绝对路径的logo
            '{HOMEURL}' => $homeUrl,
            '{KEYWORD}' => htmlspecialchars($keyword),
            '{PAGE}' => $pageLabel,
        ];
        // 合并所有后台变量到替换数组（系统变量优先级更高）
        $replace = array_merge($this->getVariableReplacements(), $replace);
        $header = str_replace(array_keys($replace), array_values($replace), $header);
        
        // 设置header到视图
        $this->assign('header', $header);
        $this->assign([
            'banner'        => $banner,
            'lists'         => $list,
            'currentPage'   => $this->page,
            'totalPage'     => $totalPage,
            'keyword'       => $keyword,
            'PageNavigator' => new PageNavigator($this->page, $totalPage, url_raw('search.page', [$keyword]), url_raw('search', [$keyword])),
        ]);

        // 埋点数据注入 (仅第一页发送 keyword_search)
        if ($this->page == 1) {
            $trackingData = Helper::getSearchTracking($keyword, $count);
            $this->assign('tracking', $trackingData);
        }

        $this->display('search');
    }

    /**
     * 转换为简体中文
     */
    private function convertToSimplified($text)
    {
        // 使用更简单的方法：只转换常见的繁体字符
        $traditional = ['紅', '國', '華', '東', '南', '西', '北', '學', '習', '語', '說', '話', '讀', '寫', '書', '畫', '電', '視', '機', '車', '馬', '龍', '鳳', '鳥', '魚', '蟲', '草', '木', '花', '樹', '山', '水', '火', '土', '金', '銀', '銅', '鐵', '鋼', '鋁', '鋅', '鉛', '錫', '鎳', '鉻', '錳', '鉬', '鎢', '釩', '鈦', '鋯', '鉿', '鈮', '鉭', '釕', '銠', '鈀', '鋨', '銥', '鉑', '頭', '髮', '臉', '腳', '手', '眼', '耳', '鼻', '口', '心', '腦', '血', '肉', '骨', '皮', '毛', '牙', '舌', '喉', '胃', '肝', '肺', '腎', '膽', '脾', '腸', '膀胱', '子宮', '卵巢', '睾丸', '陰莖', '陰道', '肛門', '乳房', '乳頭', '陰毛', '腋毛', '鬍鬚', '眉毛', '睫毛', '指甲', '腳趾', '手指', '關節', '肌肉', '神經', '血管', '淋巴', '腺體', '細胞', '基因', '染色體', '蛋白質', '酶', '激素', '抗體', '病毒', '細菌', '真菌', '寄生蟲', '腫瘤', '癌症', '炎症', '感染', '發燒', '咳嗽', '感冒', '流感', '肺炎', '支氣管炎', '哮喘', '肺結核', '肺癌', '胃癌', '肝癌', '腸癌', '乳腺癌', '子宮頸癌', '前列腺癌', '白血病', '淋巴瘤', '黑色素瘤', '皮膚癌', '腦瘤', '骨癌', '軟骨肉瘤', '纖維肉瘤', '脂肪肉瘤', '平滑肌肉瘤', '橫紋肌肉瘤', '血管肉瘤', '淋巴管肉瘤', '神經鞘瘤', '神經纖維瘤', '腦膜瘤', '垂體瘤', '甲狀腺瘤', '腎上腺瘤', '胰腺瘤', '膽囊瘤', '脾臟瘤', '胸腺瘤', '胸膜瘤', '心包瘤', '心肌瘤', '心臟瓣膜病', '冠心病', '心肌梗塞', '心力衰竭', '心律失常', '高血壓', '低血壓', '動脈硬化', '靜脈曲張', '血栓', '栓塞', '動脈瘤', '靜脈瘤', '血管炎', '雷諾病', '布爾格病', '糖尿病', '甲狀腺功能亢進', '甲狀腺功能減退', '腎上腺功能亢進', '腎上腺功能減退', '垂體功能亢進', '垂體功能減退', '性早熟', '性發育遲緩', '不孕症', '流產', '宮外孕', '葡萄胎', '絨毛膜癌', '子宮內膜異位症', '子宮肌瘤', '卵巢囊腫', '多囊卵巢綜合徵', '更年期綜合徵', '骨質疏鬆症', '關節炎', '風濕性關節炎', '類風濕性關節炎', '痛風', '骨關節炎', '強直性脊柱炎', '系統性紅斑狼瘡', '乾燥綜合徵', '硬皮病', '皮肌炎', '血管炎', '韋格納肉芽腫', '結節性多動脈炎', '巨細胞動脈炎', '過敏性紫癜', '血小板減少性紫癜', '血友病', '地中海貧血', '鐮狀細胞貧血', '再生障礙性貧血', '白血病', '淋巴瘤', '多發性骨髓瘤', '骨髓增生異常綜合徵', '真性紅細胞增多症', '原發性血小板增多症', '骨髓纖維化', '慢性粒細胞白血病', '急性淋巴細胞白血病', '慢性淋巴細胞白血病', '霍奇金淋巴瘤', '非霍奇金淋巴瘤', '伯基特淋巴瘤', '濾泡性淋巴瘤', '瀰漫性大B細胞淋巴瘤', '套細胞淋巴瘤', '邊緣區淋巴瘤', '毛細胞白血病', '漿細胞白血病', '肥大細胞白血病', '嗜鹼性粒細胞白血病', '嗜酸性粒細胞白血病', '單核細胞白血病', '巨核細胞白血病', '紅白血病', '混合細胞白血病', '未分化白血病', '急性早幼粒細胞白血病', '慢性粒細胞白血病急變期', '慢性淋巴細胞白血病急變期', '骨髓移植', '幹細胞移植', '臍帶血移植', '外周血幹細胞移植', '自體移植', '異體移植', '同基因移植', '異基因移植', '半相合移植', '單倍型移植', '雙倍型移植', '三倍型移植', '四倍型移植', '五倍型移植', '六倍型移植', '七倍型移植', '八倍型移植', '九倍型移植', '十倍型移植'];
        $simplified = ['红', '国', '华', '东', '南', '西', '北', '学', '习', '语', '说', '话', '读', '写', '书', '画', '电', '视', '机', '车', '马', '龙', '凤', '鸟', '鱼', '虫', '草', '木', '花', '树', '山', '水', '火', '土', '金', '银', '铜', '铁', '钢', '铝', '锌', '铅', '锡', '镍', '铬', '锰', '钼', '钨', '钒', '钛', '锆', '铪', '铌', '钽', '钌', '铑', '钯', '锇', '铱', '铂', '头', '发', '脸', '脚', '手', '眼', '耳', '鼻', '口', '心', '脑', '血', '肉', '骨', '皮', '毛', '牙', '舌', '喉', '胃', '肝', '肺', '肾', '胆', '脾', '肠', '膀胱', '子宫', '卵巢', '睾丸', '阴茎', '阴道', '肛门', '乳房', '乳头', '阴毛', '腋毛', '胡须', '眉毛', '睫毛', '指甲', '脚趾', '手指', '关节', '肌肉', '神经', '血管', '淋巴', '腺体', '细胞', '基因', '染色体', '蛋白质', '酶', '激素', '抗体', '病毒', '细菌', '真菌', '寄生虫', '肿瘤', '癌症', '炎症', '感染', '发烧', '咳嗽', '感冒', '流感', '肺炎', '支气管炎', '哮喘', '肺结核', '肺癌', '胃癌', '肝癌', '肠癌', '乳腺癌', '宫颈癌', '前列腺癌', '白血病', '淋巴瘤', '黑色素瘤', '皮肤癌', '脑瘤', '骨癌', '软骨肉瘤', '纤维肉瘤', '脂肪肉瘤', '平滑肌肉瘤', '横纹肌肉瘤', '血管肉瘤', '淋巴管肉瘤', '神经鞘瘤', '神经纤维瘤', '脑膜瘤', '垂体瘤', '甲状腺瘤', '肾上腺瘤', '胰腺瘤', '胆囊瘤', '脾脏瘤', '胸腺瘤', '胸膜瘤', '心包瘤', '心肌瘤', '心脏瓣膜病', '冠心病', '心肌梗塞', '心力衰竭', '心律失常', '高血压', '低血压', '动脉硬化', '静脉曲张', '血栓', '栓塞', '动脉瘤', '静脉瘤', '血管炎', '雷诺病', '布尔格病', '糖尿病', '甲状腺功能亢进', '甲状腺功能减退', '肾上腺功能亢进', '肾上腺功能减退', '垂体功能亢进', '垂体功能减退', '性早熟', '性发育迟缓', '不孕症', '流产', '宫外孕', '葡萄胎', '绒毛膜癌', '子宫内膜异位症', '子宫肌瘤', '卵巢囊肿', '多囊卵巢综合征', '更年期综合征', '骨质疏松症', '关节炎', '风湿性关节炎', '类风湿性关节炎', '痛风', '骨关节炎', '强直性脊柱炎', '系统性红斑狼疮', '干燥综合征', '硬皮病', '皮肌炎', '血管炎', '韦格纳肉芽肿', '结节性多动脉炎', '巨细胞动脉炎', '过敏性紫癜', '血小板减少性紫癜', '血友病', '地中海贫血', '镰状细胞贫血', '再生障碍性贫血', '白血病', '淋巴瘤', '多发性骨髓瘤', '骨髓增生异常综合征', '真性红细胞增多症', '原发性血小板增多症', '骨髓纤维化', '慢性粒细胞白血病', '急性淋巴细胞白血病', '慢性淋巴细胞白血病', '霍奇金淋巴瘤', '非霍奇金淋巴瘤', '伯基特淋巴瘤', '滤泡性淋巴瘤', '弥漫性大B细胞淋巴瘤', '套细胞淋巴瘤', '边缘区淋巴瘤', '毛细胞白血病', '浆细胞白血病', '肥大细胞白血病', '嗜碱性粒细胞白血病', '嗜酸性粒细胞白血病', '单核细胞白血病', '巨核细胞白血病', '红白血病', '混合细胞白血病', '未分化白血病', '急性早幼粒细胞白血病', '慢性粒细胞白血病急变期', '慢性淋巴细胞白血病急变期', '骨髓移植', '干细胞移植', '脐带血移植', '外周血干细胞移植', '自体移植', '异体移植', '同基因移植', '异基因移植', '半相合移植', '单倍型移植', '双倍型移植', '三倍型移植', '四倍型移植', '五倍型移植', '六倍型移植', '七倍型移植', '八倍型移植', '九倍型移植', '十倍型移植'];
        return str_replace($traditional, $simplified, $text);
    }

    private function parseRemarkVariables($remark)
    {
        $vars = [];
        if (empty($remark)) return $vars;

        $scripts = [];
        if(preg_match_all("/\{LD_JSON\}\s*=\s*<script(.*?)<\/script>/is", $remark, $scripts)){
            $vars["LD_JSON"] = "<script".$scripts[1][0].'</script>';
            $remark = str_replace($scripts[0][0], '', $remark);
        }

        $lines = explode("\n", $remark);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || !strpos($line, '=')) continue;
            if (preg_match('/^\{([^}]+)\}\s*=\s*(.+)$/', $line, $m)) {
                $vars[trim($m[1])] = trim($m[2]);
            }
        }
        return $vars;
    }

}