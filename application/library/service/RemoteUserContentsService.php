<?php

namespace service;

use AttachmentImagesModel;
use AttachmentModel;
use CommentsTaskModel;
use ContentsModel;
use FieldsModel;
use MetasModel;
use ProjectModel;
use TempMvModel;
use tools\LibMarkdown;
use UserContentsModel;
use UserUploadModel;
use TagRelationshipsModel;
use TagsModel;
class RemoteUserContentsService
{

    public function listContents($uid, $status, $kwy, $page, $limit)
    {
        $query = UserContentsModel::query()
            ->where('aff', $uid)
            ->where('user_type', UserContentsModel::USER_TYPE_SNS)
            ->when($kwy, function ($q) use ($kwy) {
                $q->where('title', 'like', "%$kwy%");
            })
            ->when($status != 10, function ($q) use ($status) {
                if ($status == UserContentsModel::STATUS_PASSED) {
                    $q->whereIn('status', [UserContentsModel::STATUS_PASSED, UserContentsModel::STATUS_WAIT_SLICE]);
                } else {
                    $q->where('status', $status);
                }
            });

        $total = 0;
        if ($page == 1) {
            $querycount = clone $query;
            $total = $querycount->count('id');
        }

        $list = $query
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get();
        $list = collect($list)->map(function ($item) {
            $tem = [];
            $category_id = json_decode($item->category_id, true);
            if ($category_id) {
                foreach ($category_id as $v) {
                    $tem[] = [
                        'id' => $v
                    ];
                }
            }
            $item->category_id = $tem;
            if (in_array($item->status, [UserContentsModel::STATUS_WAIT, UserContentsModel::STATUS_DRAFT])) {
                $dplayerpattern = \tools\LibMarkdown::getShortcodeRegex(['dplayer']);
                preg_match_all("/$dplayerpattern/", $item->body, $dplayermatches);
                foreach ($dplayermatches[3] as $v) {
                    $tag = htmlspecialchars_decode($v);
                    $attrs = \tools\LibMarkdown::shortcodeParseAttrs($tag);
                    if (!empty($attrs['url']) && !str_contains($attrs['url'], \PostMediaModel::getR2Mp4PlayUrl()) && !str_contains($attrs['url'], 'm3u8')) {
                        $item->body = str_replace($attrs['url'], TB_CHECK_VIDEO . '/' . ltrim($attrs['url'], '/'), $item->body);
                    }
                }
            }
            if ($item->status == UserContentsModel::STATUS_PASSED) {
                $dplayerpattern = \tools\LibMarkdown::getShortcodeRegex(['dplayer']);
                preg_match_all("/$dplayerpattern/", $item->body, $dplayermatches);
                $existUrl = [];
                foreach ($dplayermatches[3] as $v) {
                    $tag = htmlspecialchars_decode($v);
                    $attrs = \tools\LibMarkdown::shortcodeParseAttrs($tag);
                    if (!in_array($attrs['url'], $existUrl) && str_contains($attrs['url'], 'm3u8')) {
                        $existUrl[] = $attrs['url'];
                        $item->body = str_replace($attrs['url'], TB_VIDEO_ADM_US . '/' . ltrim($attrs['url'], '/'), $item->body);
                    }
                }
            }
            $tags = json_decode($item->tags, true);
            if ($tags) {
                $item->tags = implode(',', $tags);
            }
            return $item;
        });

        return [
            'list' => $list,
            //'total' => ceil($total / $limit)
            'total' => $total
        ];
    }

    public function createContentsRemote($uid, $title, $created, $body, $cover, $tags, $category, $is_draft, $id)
    {
        if ($id) {
            $content = UserContentsModel::find($id);
            test_assert($content, "文章不存在");
            test_assert($content->status == UserContentsModel::STATUS_DRAFT, '只有草稿才能修改');
        } else {
            $content = UserContentsModel::make([
                'aff' => $uid,
                'user_type' => UserContentsModel::USER_TYPE_SNS
            ]);
        }

        test_assert($content, '操作失败');
        test_assert($content->aff == $uid, '您没有权限操作');
        $body = str_replace(TB_CHECK_VIDEO, '', $body);
        if ($created) {
            $content->created = strtotime("$created +0700");
        }
        $content->title = $title;
        $content->cover = $cover;
        $content->created_at = time();
        $content->aff = $uid;
        $content->denied_at = 0;
        $content->denied_reason = '';
        if ($is_draft == 1) {
            //替换播放地址的全链接
            $pattern = "/http[s]?:\/\/(?:[a-zA-Z]|[0-9]|[$-_@.&+]|[!*\(\),]|(?:%[0-9a-fA-F][0-9a-fA-F]))+/";
            preg_match_all($pattern, $body, $matches);
            if (count($matches[0]) > 0) {
                foreach ($matches[0] as $v) {
                    $v = rtrim($v, ')');
                    $new = parse_url($v, PHP_URL_PATH);
                    if (str_ends_with($new, 'm3u8')) {
                        $body = str_replace($v, $new, $body);
                    }
                }
            }
            $content->body = $body;
            $content->status = UserContentsModel::STATUS_PASSED;
        } else {
            $content->body = $body;
            $content->status = UserContentsModel::STATUS_DRAFT;
        }
        $category = explode(',', $category);
        if (is_array($category) && $category) {
            $content->category_id = json_encode($category);
        }
        // 支持 # 和 , 作为分隔符
        $tags = preg_split('/[#,\s]+/u', str_replace('，', ',', $tags));
        if (is_array($tags) && $tags) {
            $tags = array_map(function ($v) {
                return trim($v);
            }, $tags);
            $tags = array_filter($tags); // 过滤空标签
            // 验证标签：只允许中文、字母、数字和横杠
            $tagPattern = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-]+$/u';
            foreach ($tags as $tag) {
                if (!preg_match($tagPattern, $tag)) {
                    throw new \RuntimeException("标签 '{$tag}' 格式不正确，只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格");
                }
            }
            $tags = array_unique($tags);
            $content->tags = json_encode($tags);
        }
        $isOk = $content->save();
        test_assert($isOk, '保存失败');
        //移动表
        if ($is_draft == 1) {
            $this->createContent($content);
        }
    }

    public function delContents($uid, $id)
    {
        $contents = UserContentsModel::find($id);
        test_assert($contents, '文章不存在');
        test_assert($contents->status == UserContentsModel::STATUS_DRAFT, '只有草稿才能删除');
        test_assert($contents->aff == $uid, '此篇文章属于其他用户');
        test_assert($contents->user_type == UserContentsModel::USER_TYPE_SNS, '用户类型错误');
        $isOk = $contents->delete();
        test_assert($isOk, '删除失败');
    }

    public function createContent(\UserContentsModel $userContent)
    {
        \DB::beginTransaction();
        $content = ContentsModel::make();
        $content->title = $userContent->title;
        $content->text = '<!--markdown-->' . $userContent->body;
        $content->status = ContentsModel::STATUS_WAITING;
        $content->modified = time();
        $content->created = strtotime($userContent->created);
        $content->type = ContentsModel::TYPE_POST;
        $content->is_slice = 0;
        $content->allowComment = 1;
        $content->allowPing = 1;
        $content->allowFeed = 1;
        $content->authorId = $userContent->aff;
        //是否可以设置发布时间和状态直接通过
        $user = \UsersModel::find($userContent->aff);
        //管理员
        if ($user->group == 'administrator') {
            $content->status = ContentsModel::STATUS_PUBLISH;
            if ($userContent->created) {
                $content->created = $userContent->getRawOriginal('created');
            }
        }
        $content->save();
        $category = json_decode($userContent->category_id, true);
        if (is_array($category) && $category) {
            foreach ($category as $cate) {
                \DB::table('category_relationships')->insertOrIgnore([
                    'category_id' => $cate,
                    'cid' => $content->cid,
                ]);
            }
        }
        $fields = [
            'banner' => parse_url($userContent->cover, PHP_URL_PATH),
            'contentLang' => '0',
            'disableBanner' => '1',
            'disableDarkMask' => '0',
            'enableFlowChat' => '0',
            'enableMathJax' => '0',
            'enableMermaid' => '0',
            'headTitle' => '0',
            'hotSearch' => '0',
            'redirect' => '',
            'TOC' => '0',
        ];
        foreach ($fields as $field => $value) {
            \DB::table('fields')->updateOrInsert(
                ['cid' => $content->cid, 'name' => $field],
                ['type' => 'str', 'str_value' => $value]
            );


        }
        if (str_contains($content->text, '.mp4') && preg_match_all('#url="([^"]+)"#', $content->text, $ary)) {
            TempMvModel::makeAndSlice($ary, $userContent, $content);
            $userContent->status = UserContentsModel::STATUS_WAIT_SLICE;
        } else {
            $content->is_slice = 1;
            $content->save();
        }
        $tags = json_decode($userContent->tags, true);
        $tags = collect($tags)->filter(function ($tag) {
            return !empty(trim($tag));
        })->map(function ($item) {
            return trim($item);
        })->values();

        // 验证标签：只允许中文、字母、数字和横杠
        $tagPattern = '/^[\x{4e00}-\x{9fa5}a-zA-Z0-9\-]+$/u';
        foreach ($tags as $tag) {
            if (!preg_match($tagPattern, $tag)) {
                throw new \RuntimeException("标签 '{$tag}' 格式不正确，只能包含中文、字母、数字和横杠，禁止使用表情、符号和空格");
            }
        }

        if ($tags->count() > 0) {
            $existingTags = TagsModel::useWritePdo()
                ->whereRaw("name COLLATE utf8mb4_bin IN (" . implode(',', array_fill(0, $tags->count(), '?')) . ")", $tags->toArray())
                ->get();

            $newTags = collect([]);

            foreach ($existingTags as $tag) {
                if ($tags->contains($tag->name)) {
                    $newTags->push($tag);
                }
            }

            $diff = $tags->diff($newTags->pluck('name'));

            foreach ($diff as $tagName) {
                $tag = TagsModel::create([
                    'name' => $tagName,
                    'created_at' => time(),
                    'updated_at' => time(),
                ]);
                $newTags->push($tag);
            }

            if ($newTags->count() > 0) {
                foreach ($newTags as $tag) {
                    TagRelationshipsModel::updateOrInsert([
                        'cid' => $content->cid,
                        'tag_id' => $tag->id,
                    ]);
                }

            }
        }

        $userContent->cid = $content->cid;
        $userContent->save();
        \DB::commit();
    }

    public function uploadMv($uid, $name, $mp4_url, $cover, $upload_type)
    {
        $userUpload = UserUploadModel::make();
        $userUpload->user_id = $uid;
        $userUpload->progress_rate = 100;
        $userUpload->name = $name ?: basename($mp4_url);
        $userUpload->upload_type = $upload_type;
        $userUpload->upload_status = UserUploadModel::UPLOAD_STATUS_OK;
        $userUpload->cover = parse_url($cover, PHP_URL_PATH);
        $userUpload->mp4_url = $mp4_url;
        $userUpload->created_at = date('Y-m-d H:i:s');
        $userUpload->updated_at = date('Y-m-d H:i:s');
        $isOk = $userUpload->save();
        test_assert($isOk, '保存失败');
        if ($mp4_url) {
            jobs([UserUploadModel::class, 'makeSlice'], [$userUpload]);
        }
    }

    public function uploadAttachment($uid, $cid, $name, $mp4_url, $cover, $upload_type)
    {

        $userUpload = AttachmentModel::make();
        $userUpload->user_id = $uid;
        $userUpload->cid = $cid;
        $userUpload->progress_rate = 100;
        $userUpload->name = $name ?: basename($mp4_url);
        $userUpload->upload_type = $upload_type;
        $userUpload->upload_status = AttachmentModel::UPLOAD_STATUS_OK;
        $userUpload->cover = parse_url($cover, PHP_URL_PATH);
        $userUpload->mp4_url = $mp4_url;
        $userUpload->created_at = date('Y-m-d H:i:s');
        $userUpload->updated_at = date('Y-m-d H:i:s');
        $isOk = $userUpload->save();
        test_assert($isOk, '保存失败');
        //使用批量提交，代码切换至contentsServicve->handleVideoSlice()
        if ($mp4_url && 'product' == T_ENV) {
            jobs([AttachmentModel::class, 'makeSlice'], [$userUpload]);
        }
    }

    public function uploadAttachmentImage($uid, $cid, $name, $image_url, $image_src)
    {

        $userUpload = AttachmentImagesModel::make();
        $userUpload->user_id = $uid;
        $userUpload->cid = $cid;
        $userUpload->name = $name ?: basename($image_url);
        $userUpload->image_url = parse_url($image_url, PHP_URL_PATH);
        $userUpload->image_src = $image_src;
        $userUpload->created_at = date('Y-m-d H:i:s');
        $userUpload->updated_at = date('Y-m-d H:i:s');
        $isOk = $userUpload->save();
        test_assert($isOk, '保存失败');
        //使用批量提交，代码切换至contentsServicve->handleVideoSlice()
        // if ($mp4_url){
        //     jobs([AttachmentModel::class, 'makeSlice'], [$userUpload]);
        // }
    }

    public function mvList($uid, $slice_status, $kwy, $page, $limit)
    {
        $query = UserUploadModel::query()
            ->when($slice_status != -1, function ($q) use ($slice_status) {
                $q->where('slice_status', $slice_status);
            })
            ->where('user_id', $uid)
            ->when($kwy, function ($q) use ($kwy) {
                $q->where('name', 'like', "%$kwy%");
            });

        $list = $query->selectRaw('id,name,created_at,progress_rate,cover,mp4_url,m3u8_url,slice_status')
            ->orderByDesc('id')
            ->forPage($page, $limit)
            ->get();
        $total = 0;
        if ($page == 1) {
            $total = $query->count('id');
        }

        return [
            'list' => $list,
            'total' => ceil($total / $limit)
        ];
    }

    public function addComments($cid, $content, $begin, $end)
    {
        $task = CommentsTaskModel::make();
        $task->cid = $cid;
        $task->content = $content;
        $task->begin = $begin;
        $task->end = $end;
        $task->is_run = CommentsTaskModel::RUN_WAIT;
        $task->created_at = date('Y-m-d H:i:s');
        $task->updated_at = date('Y-m-d H:i:s');
        $isOk = $task->save();
        test_assert($isOk, '保存失败');
    }

    public function projectList()
    {
        return ProjectModel::listProjects();
    }
}
