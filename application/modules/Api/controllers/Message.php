<?php

use service\AppFeedSystemService;
use service\MessageService;

class MessageController extends BaseController
{
    /**
     * 反馈列表
     * post 请求
     * page 页数
     */
    public function feedbackAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'page' => 'required|numeric'
        ]);
        if ($Validator->fail($msg)) {
            return $this->showJson($msg);
        }
        $page = $this->data['page'];

        $limit = 30;
        $offset = ($page - 1) * $limit;
        $offset < 0 and $offset = 0;
        $userFeeds = UserFeedModel::select('id', 'question', 'status', 'created_at', 'message_type', 'image_1')
            ->where('uuid', $this->member['uuid'])
            ->orderBy('created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
        $data = [];
        if ($userFeeds) {
            $items = $userFeeds->toArray();
            foreach ($items as $key => $item) {
                $item['content'] = $item['question'];
                $data[$key]['id'] = $item['id'];
                if ($item['status'] == 2) {
                    $data[$key]['nickname'] = 'lts在线';
                    // $data[$key]['thumb'] = config('default_jd_thumb');
                } else {
                    $data[$key]['nickname'] = $this->member['nickname'];
                    // $data[$key]['thumb'] = url_avatar($this->member['thumb']);
                }

                $data[$key]['message'] = $item['message_type'] == '1' ? $item['content'] : url_image($item['content']);
                $data[$key]['messageType'] = $item['message_type'];
                $data[$key]['status'] = $item['status'];
                $data[$key]['createdAt'] = $item['created_at'];
            }
        }
        UserFeedModel::where('uuid', $this->member['uuid'])->update(['is_read' => 1]);
        $this->showJson($data);
    }


    /**
     * 用户反馈
     * post请求
     * content 反馈内容
     * message_type 消息类型
     *
     */
    public function feedingAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'content' => 'required',
            'type'    => 'required|numeric'
        ]);
        if ($Validator->fail($msg)) {
            return $this->showJson($msg);
        }
        $message = $this->data['content'] ?? '';
        $data = [
            'uuid'         => $this->member->uuid,
            'user_ip'      => USER_IP,
            'status'       => 1,
            'is_read'      => 0,
            'message_type' => $this->data['type'],
        ];
        $data['question'] = $message;
        if (UserFeedModel::create($data)) {
            if (setting('enable_public_feed' , 0)){
                $appFeedSystemService = new AppFeedSystemService();
                $appFeedSystemService->sendRemoteRequest(null, [
                    'app'       => VIA,
                    'uuid'      => $this->member->uuid,
                    'app_type'  => $this->member->oauth_type,
                    'aff'       => $this->member->aff,
                    'product'   => 0,
                    'type'      => $this->data['type'],
                    'nickname'  => $this->member->nickname,
                    'content'   => $message,
                    'version'   => $this->member->app_version,
                    'ip'        => USER_IP,
                    'vip_level' => MemberModel::VIP_LEVEL[$this->member->vip_level] ?? '非会员',
                    'status'    => 0,
                ]);
            }
        }
        $this->showJson('提交成功');
    }

    public function getMessageListAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'page'  => 'required|numeric',
            'limit' => 'required|numeric|max:50'
        ]);
        if ($Validator->fail($msg)) {
            return $this->showJson('', 0, $msg);
        }
        $limit = $this->data['limit'];
        $offset = ($this->data['page'] - 1) * $limit;
        $messageService = new MessageService();
        $messages = MessageModel::select('members.nickname', 'members.thumb', 'message.*')
            ->join('members', 'members.aff', '=', 'message.from_aff')
            ->where('message.aff', $this->member->aff)
            ->limit($limit)
            ->offset($offset)
            ->orderBy('created_at', 'desc')
            ->get();
        if ($messages->count()) {
            $messages = $messages->toArray();
            foreach ($messages as &$message) {
                $message['content'] = $messageService->getContent($message);
            }
            MessageModel::where('aff', $this->member->aff)
                ->where('is_read', MessageModel::IS_NOT_READ)
                ->update(['is_read' => MessageModel::IS_READ]);
        }
        // $messages = null;
        return $this->showJson($messages);
    }


    public function getSystemNoticeListAction(): bool
    {
        $Validator = \helper\Validator::make($this->data, [
            'page'  => 'required|numeric',
            'limit' => 'required|numeric|max:50'
        ]);
        if ($Validator->fail($msg)) {
            return $this->showJson('', 0, $msg);
        }
        $limit = $this->data['limit'];
        $page = $this->data['page'];
        $list = SystemNoticeModel::where('aff', $this->member->aff)
            ->forPage($page, $limit)
            ->orderBy('id', 'desc')
            ->get();
        if ($list->count()) {
            $list = $list->toArray();
            SystemNoticeModel::where('aff', $this->member->aff)
                ->where('read', SystemNoticeModel::IS_UN_READ)
                ->update(['read' => SystemNoticeModel::IS_READ]);
        }
        return $this->showJson($list);
    }


    public function getUnreadCountAction()
    {
        // $redisKey = 'unread:page:'.$this->member->aff;
        // $return = redis()->getWithSerialize($redisKey);
        // if(!$return){

        $lastSysNotice = SystemNoticeModel::queryBase('aff', $this->member->aff)->orderByDesc('id')->first();
        if ($lastSysNotice) {
            $lastSysNotice->append('question');
        }

        /** @var ?MessageModel $message */
        $message = MessageModel::with('from_member:nickname,thumb,aff')->where('aff', $this->member->aff)->first();
        $return = [
            'systemNoticeCount' => SystemNoticeModel::queryUnread('aff', $this->member->aff)->count(),
            'systemNotice'      => $lastSysNotice,
            'feedCount'         => UserFeedModel::queryUnreply('uuid', $this->member->uuid)->count(),
            'feed'              => UserFeedModel::where('uuid', $this->member->uuid)->orderByDesc('id')->first(),
            'messageCount'      => MessageModel::queryUnread()->count(),
            'message'           => $message ? $message->flattenRelation() : null,
        ];


        if ($return['message']) {
            $messageService = new MessageService();
            $return['message'] = $return['message']->toArray();
            $return['message']['content'] = $messageService->getContent($return['message']);
        } else {
            $return['message'] = null;
        }
        //     redis()->setWithSerialize($redisKey,$return,600);
        // }
        $this->showJson($return);
    }


    public function reset_unread_msgAction()
    {
        if ($this->member->unread_reply) {
            $this->member->update([
                'unread_reply' => 0,
            ]);
            $this->member->syncCached($this->member_key);
        }
        $this->successMsg('操作成功');
    }

}