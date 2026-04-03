<?php


namespace repositories;


use tools\RedisService;
use tools\TextSimilarity;

trait StrFilterRepository
{
    /**
     * 敏感词比对
     * @param $str
     * @return int
     * @throws \Safe\Exceptions\PcreException
     */
    public function strFilter($str): int
    {
        // 只保留汉字
        $str = preg_replace('/[^\p{Han}]/iu', '', $str);
        $isChecked = 1;
        $keywords = RedisService::redis()->sMembers(\CommentKeywordsModel::REDIS_SPAM_SAMPLE_LIST);
        if (empty($keywords)) {
            $comments = \CommentKeywordsModel::query()
                ->select('keyword')
                ->orderBy('created_at', 'desc')
                ->limit(50)->get();
            $keywords = [];
            foreach ($comments as $key => $comment) {
                $keywords[] = $comment->keyword;
            }
            !empty($keywords) && RedisService::redis()->sAddArray(\CommentKeywordsModel::REDIS_SPAM_SAMPLE_LIST, $keywords);
        }

        $similarity = new TextSimilarity();
        foreach ($keywords as $keyword) {
            $similarity->setText($str, $keyword);
            $percent = $similarity->run();
            if ($percent > 0.5) {
                $isChecked = -1;
                break;
            }
        }

        if ($isChecked === 1) {
            foreach ($keywords as $keyword) {
                mb_similar_text($str, $keyword, $percent);
                if ($percent > 50) {
                    $isChecked = -1;
                    break;
                }
            }
        }
        return $isChecked;
    }

    /**
     * 更新关键词
     */
    public function updateSpamCache():void
    {
        $keywords = \CommentKeywordsModel::query()
            ->orderBy('created_at', 'desc')
            ->limit(300)->pluck('keyword')->toArray();
        if ($keywords) {
            RedisService::redis()->del(\CommentKeywordsModel::REDIS_SPAM_SAMPLE_LIST);
            RedisService::redis()->sAddArray(\CommentKeywordsModel::REDIS_SPAM_SAMPLE_LIST, $keywords);
        }
    }

    /**
     * 比对并更新关键词
     * @param $comment
     * @throws \Safe\Exceptions\PcreException
     */
    public function recordspam($comment)
    {
        $insert = $this->strFilter($comment);
        if ($insert === 1) {
            $this->recordspam($comment);
            $this->updateSpamCache();
        }
    }

    public function showFilterBio($content)
    {
        $maybe_spam = 0;
        $numArr = array('⑴','⑵','⑷','⑸','⑺','⑼','⒈','⒎','⒏','⒐','①','②','③','④','⑤','⑥','⑦','⑧','⑨','0','1','2','3','4','5','6','7','8','9','㊀','㊁','㊂','㊅','㊈','₁','₂','₄','₅','₇','₉','₀','〇','❶','❸','❻','❽','❾','❺','❷','❼','咬','8⃣','伞','四','溜','趴','ⓢ','ⓜ','ⓧ','QQ','qq','Qq','微','薇','x','零','一','二','三','四','五','六','七','八','九','¹','⁰','²','³','⁴','⁹',
            '弌','伊','衣','医','吚','依','祎','洢','咿','渏','猗','壹','揖','椅','漪','乁','义','仪','夷','诒','冝','宜','狋','怡','饴','贻','峓','胰','桋','宧','移','貽','遗','颐','疑','遺','彛','彜','彝','乙','已','以','矣','佁','尾','依','蚁','倚','椅','踦','乂','义','亿','弋','艺','忆','艾','阣','仡','议','肊','伇','亦','异','忔','屹','抑','役','佚','译','邑','易','诣','呹','呭','驿','泆','怿','绎','弈','奕','疫','羿','昳','轶','食','益','谊','逸','翊','翌','豙','嗌','溢','缢','義',
            '儿','而','尔','尓','耳','迩','饵','珥','铒','贰','铒','貮','貳',
            '弎','参','叁','毵','毶','伞','散','糁','馓','散',
            '','厶','丝','司','私','泀','咝','思','斯','锶','嘶','撕','死','巳','似','寺','汜','泤','姒','伺','祀','驷','饲','泗','俟','枱','柶','牭','食','肂','竢','肆',
            '兀','乌','邬','弙','污','汙','汚','圬','呜','巫','杇','於','屋','诬','钨','恶','烏','亡','无','毋','芜','吾','吴','吳','唔','無','蜈','','午','伍','仵','怃','忤','武','侮','逜','捂','兀','兀','乌','勿','阢','务','戊','坞','物','误','恶','悟',
            '溜','熘','蹓','刘','浏','流','留','琉','旈','裗','硫','遛','馏','榴','锍','陆',
            '七','沏','妻','柒','栖','桤','凄','倛','娸','捿','淒','悽','萋','戚','桼','欺','欹','期','缉','踦','蹊','丌','亓','祁','齐','圻','忯','芪','岐','岓','奇','其','祈','祇','肵','歧','陭','耆','蚚','脐','斊','畦','跂','埼','萁','帺','骐','骑','猉','崎','掑','淇','棋','棊','祺','蛴','琪','琦','锜','褀','旗','鲯','鳍','麒','乞','邔','企','芑','岂','屺','杞','启','起','啓','啟','啔','婍','绮','綺','稽','气','讫','迄','汔','汽','弃','矵','呮','泣','妻','炁','契','氣','噐','器','憩',
            '丷','八','仈','巴','叭','朳','玐','吧','岜','扷','芭','夿','疤','柭','釟','蚆','粑','笆','捌','哵','拔','炦','癹','詙','跋','魃','鼥','把','钯','鈀','靶','叭','把','伯','坝','爸','杷','垻','罢','耙','跁','鲅','靶','鮁','','霸','抜',
            '丩','纠','鸠','究','赳','阄','啾','揪','九','久','乆','汣','灸','玖','韭','镹','酒','韮','匛','旧','臼','疚','咎','柩','桕','厩','救','就','舅','鹫','鷲','杦',
            '扌','番','辶','畐'
        );//①②③④⑤⑥⑦⑧⑨ ⓢⓜⓢⓧ❾❺❷❼ 〇 咬8⃣️伞四四0⃣️四溜趴溜溜 ㊁㊅㊈㊅㊀㊅㊂㊀㊀㊁ ₁₅₂₉₇₁₄₁₇₀₉ ⑴⑸⑵⑼⑺⑴⑷⑴⑺0⑼  ⒈⒐⒐⒎⒏⒏⒎⒏⒏⒈⒎ ❶(❽)(❼)(❼)(❻)(❸)(❷)(❺)(❷)(❻)(❺) ¹⁰⁰³¹²⁴⁰⁹¹
        $i = 1;
        foreach( $numArr as $key=>$value ){  // 过滤掉带圈数字
            if($i>3){
                $maybe_spam = 1;
                break;
            }
            if( stripos($content,$value)!==false ){
                $i++;
            }
        }
        return $maybe_spam?true:false;
    }
}