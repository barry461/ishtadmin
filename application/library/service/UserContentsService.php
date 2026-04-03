<?php

namespace service;


class UserContentsService
{

    public function createContents(
        \MemberModel $member,
        $title,
        $body,
        $cover ,
        $tags,
        $id
    ) {
        if ($id) {
            $content = \UserContentsModel::find($id);
        } else {
            $content = \UserContentsModel::make([
                'aff'    => $member->aff,
                'status' => \UserContentsModel::STATUS_WAIT,
            ]);
        }
        test_assert($content, '操作失败');
        test_assert($content->aff == $member->aff, '您没有权限操作');
        test_assert($content->status != \UserContentsModel::STATUS_PASSED, '当前文章已采纳，不允许修改');
        $content->body = $body;
        $content->title = $title;
        $content->cover = $cover;
        
        // 处理并验证标签
        if (!empty($tags)) {
            // 支持 # 和 , 作为分隔符
            $tagsArray = preg_split('/[#,\s]+/u', str_replace('，', ',', $tags));
            $tagsArray = array_map('trim', $tagsArray);
            $tagsArray = array_filter($tagsArray); // 过滤空标签
            // 验证标签：只允许中文、字母、数字和横杠
            $tagPattern = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-]+$/u';
            foreach ($tagsArray as $tag) {
                if (!preg_match($tagPattern, $tag)) {
                    throw new \RuntimeException("标签 '{$tag}' 格式不正确，只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格");
                }
            }
            $tagsArray = array_unique($tagsArray);
            $content->tags = json_encode($tagsArray);
        } else {
            $content->tags = json_encode([]);
        }
        $content->created_at = time();
        $content->aff = $member->aff;
        $content->denied_at = 0;
        $content->denied_reason = '';
        $content->status = \UserContentsModel::STATUS_WAIT;
        if (!$content->exists){
            $count = \UserContentsModel::where('aff' , $member->aff)
                ->whereBetween('created_at' , [strtotime('Y-m-d 00:00:00'),strtotime('Y-m-d 23:59:59')])->count();
            if ($count > setting('user:contents:max' ,20)){
                throw new \RuntimeException('您每天发布的内容以达到了上限');
            }
        }

        $content->save();
    }


    public function listContents(\MemberModel $member, $status, $page, $limit)
    {
        return \UserContentsModel::selectShield(['body'])
            ->where('aff', $member->aff)
            ->where('status', $status)
            ->forPage($page, $limit)
            ->get();
    }


}