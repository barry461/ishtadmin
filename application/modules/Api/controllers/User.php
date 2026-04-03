<?php

use service\CommonService;
use service\UserService;
use Carbon\Carbon;
use service\InfoService;
use service\CommunityService;
use helper\QueryHelper;
use helper\Validator;

class UserController extends BaseController
{
    /**
     * @var UserService
     */
    public $UserService;

    public function updateUserInfoAction(): bool
    {
        try {
            $this->verifyMemberSayRole();
            $nickname = strip_tags($this->data['nickname'] ?? '');
            $thumb = strip_tags($this->data['thumb'] ?? '');

            // 存在未审核的记录直接提示
            $has = MemberUpdateLogModel::where('aff', $this->member->aff)
                ->where('status', MemberUpdateLogModel::STATUS_WAIT)
                ->first();
            test_assert(!$has, '您有待审核的修改请求,请等待审核完成');

            $hasMonth = MemberUpdateLogModel::hasMonthRecord($this->member->aff);
            if ($hasMonth){
                $has = UserPrivilegeModel::hasPrivilegeAndSubTimePrivilege(USER_PRIVILEGE, ProductPrivilegeModel::RESOURCE_TYPE_SYSTEM, ProductPrivilegeModel::PRIVILEGE_TYPE_SETTING, $this->member->aff);
                if (!$has){
                    throw new Exception("一个月之内只能修改一次");
                }
            }

            $update = [];
            if (!empty($nickname)) {
                $nickname = mb_substr($nickname, 0, 8);
                $update['nickname'] = $nickname;
            }
            if (!empty($thumb)) {
                $update['thumb'] = $thumb;
            }

            if (empty($update)) {
                return $this->showJson('成功');
            }

            // 弄到待审核
            $isOk = MemberUpdateLogModel::createRecord($this->member->aff, $update);
            test_assert($isOk, '系统异常');
            return $this->showJson('修改成功,请等待审核');

//            $userService = new UserService($this->member);
//            $rs = $userService->updateUser($this->member->aff, $update);
//            if (empty($rs)) {
//                throw new \Exception('操作失败');
//            }
//            return $this->showJson('成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function invitationAction()
    {
        $Validator = \helper\Validator::make($this->data, [
            'aff_code' => 'required'
        ]);
        if ($Validator->fail($msg)) {
            return $this->errorJson($msg);
        }
        $data = $this->data;
        $aff_code = $data['aff_code'];
        if (false !== strpos($aff_code, 'cgqz_aff:')) {
            $aff_code = str_replace('cgqz_aff:', '', $aff_code);
        }
        $aff = (int)get_num($aff_code);
        $member = $this->member->refresh();
        try {
            $oldInvited = 0;
            if ($member->ip_invite) {
                if (strtotime($member->regdate) + 100 > time()) {
                    $oldInvited = $member->invited_by;
                    $member->invited_by = 0;
                }
            }
            test_assert(empty($member->invited_by), '已经填写过了');
            test_assert($aff != $member->aff, '不能邀请自己');
            test_assert($aff < $member->aff, '本次邀请无效');
            $this->verifyFrequency();
            $this->verifyMemberSayRole();
            /** @var MemberModel $invitedMember */
            $invitedMember = MemberModel::findByAff($aff);
            test_assert($invitedMember, '无效邀请码');
            // $userQuery = \UserProxyModel::query()->where('aff', $user['aff'])->first();
            // if ($userQuery && $userQuery->root_aff != $userQuery->aff) {
            //     return $this->errorJson('您已無法绑定邀请人,您可以分享发展更多下級賺取分成');
            // }
            test_assert(!$invitedMember->isMuteRole(), '邀请人已封号');

            transaction(function () use ($member, $invitedMember, $oldInvited) {
                $member->invited_by = $invitedMember->aff;
                $member->channel = $invitedMember->channel;
                $member->ip_invite = 0;
                $isOk = $member->save();
                test_assert($isOk, '数据错误');
                //邀请用户增加10经验
                if ($oldInvited != $invitedMember->aff) {
                    $extra = [];
                    if ($member->username) {
                        $extra['invited_reg_num'] = \DB::raw('invited_reg_num+1');
                    }
                    $isOk = MemberModel::where('aff', $invitedMember->aff)->increment('invited_num', 1, $extra);
                    test_assert($isOk, '数据错误');

                    if ($member->username) {
                        // 邀请人数据统计
                        jobs([DayInviteModel::class, 'invite'], [$invitedMember->aff, $invitedMember->channel, client_ip()]);
                    }

                    if ($oldInvited) {
                        $isOk = MemberModel::where('aff', $oldInvited)->decrement('invited_num');
                        test_assert($isOk, '数据错误');
                    }
                }

//                $invitedMember->vip_level = max($invitedMember->vip_level, ProductModel::VIP_LEVEL_TMP);
//                $invitedMember->expired_at = Carbon::parse($invitedMember->expired_at)->max(Carbon::now())->addDays(3);
//                $isOk = $invitedMember->save();
//                test_assert($isOk, '给邀请人赠送会员失败');

                if ($invitedMember->channel == 'self') {
//                    $userProxy = UserProxyModel::firstOrCreate(['aff' => $invitedMember->aff]);
//                    test_assert($userProxy, '获取用户代理失败');
//                    $affAry = collect(explode(',', $userProxy->direct_afftext))->push($member->aff);
//                    $afftext = $affAry->map(function ($v) {
//                        return intval($v);
//                    })->filter()->unique()->values()->join(',');
//                    $isOk = $userProxy->increment('direct_proxy_num', 1, ['direct_afftext' => $afftext]);
//                    test_assert($isOk, '处理邀请人推广数据失败');
//                    $isOk = UserProxyLogModel::create(['proxy_aff' => $userProxy->aff, 'user_aff' => $member->aff, 'log_date' => date('Y-m-d')]);
//                    test_assert($isOk, '记录代理日志');
                    SysTotalModel::incrBy('member:self-invited');
                } else {
                    SysTotalModel::incrBy('member:channel-invited');
                }
            });


            if ($invitedMember->channel != 'self') { //渠道用户上报数据中心
                $temp = $this->member->toArray();
                $temp['invited_by'] = $invitedMember->aff;
                $temp['channel'] = $invitedMember->channel;
                \tools\Channel::addUserQueue($temp);
            }
            $member = $this->member;
            $member->clearCached();

            //春节活动
            jobs([LotteryFreeLogModel::class, 'invite'], [$invitedMember->aff]);
            //邀请送金币活动 IP不一样且注册时间不超过48小时
            if ((time() - strtotime($member->regdate)) < 172800 && $member->regip != $invitedMember->regip){
                jobs([ActivityInviteLogModel::class, 'invite'], [$member->aff, $invitedMember->aff]);
            }

            return $this->showJson('成功');

        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function listInvitationAction()
    {
        list($page, $limit) = \helper\QueryHelper::pageLimit();
        $list = MemberModel::selectRaw('nickname,created_at,phone')
            ->where('invited_by', $this->member->aff)
            ->forPage($page, $limit)
            ->orderBy('members.created_at', 'desc')
            ->get()
            ->map(function (MemberModel $item) {
                if ($item->phone === null) {
                    $item->register = '未注册';
                } else {
                    $item->register = '已注册';
                }
                return $item;
            });

        return $this->listJson($list);
    }

    public function userInfoAction()
    {
        $member = $this->member;
        $return = $this->member->toArray();
        $return['money'] = (int)round($this->member->money);
        $return['income_money'] = (int)round($this->member->income_money);
        $return['role_id'] = (int)round($this->member->role_id);
        $return['coins'] = (int)round($this->member->coins);

        $return['invited_by'] = $this->member->invited_by ? generate_code($this->member->invited_by) : null;

        $UserService = new UserService($this->member);
        $return['share'] = $UserService->getMyShareURLDATA($member);
        $return['post_count'] = $member->post_count;
        $return['exp_con'] = (int)setting('exp_con', 2);
        $return['exp_down'] = (int)setting('exp_down', 5);
        $return['fans_count'] = $member->followed_count;
        if (
            (empty($member->username) && $member->oauth_type === MemberModel::TYPE_WEB) ||
            ($member->order_count && empty($member->username))
        ) {
            $return['reg_tip'] = setting('reg:tip', '充值前请先注册/登录，否则后果自负！');
        }

        $return['navigation'] = [
            //'ad_big' => AdsModel::onePos(AdsModel::POSITION_USER_POS_1),
            'ad_big' => CommonService::getAds($this->member,AdsModel::POSITION_USER_POS_1,true),
            //'ad1' => AdsModel::onePos(AdsModel::POSITION_USER_POS_2),
            'ad1' => CommonService::getAds($this->member,AdsModel::POSITION_USER_POS_2,true),
            //'ad2'   => AdsModel::onePos(AdsModel::POSITION_USER_POS_3),
            'ad2'   => CommonService::getAds($this->member,AdsModel::POSITION_USER_POS_3,true),
        ];


        $return['create_content'] = [
            'allow' => 1,
            'msg' => 'allow=1允许用户发布，allow=0，不允许用户发布'
        ];
        //设置订阅价格是否弹窗(认证了不需要)
        $return['is_club_pop'] = $member->auth_status ? 0 : 1;

        if ($member->post_club_id) {
            $postClub = PostClubsModel::findByAff($member->aff);
            $tmp = [
                'post_club_number_num'   => $postClub->member_num,
            ];
        } else {
            $tmp = [
                'post_club_number_num'   => 0,
            ];
        }
        //吃瓜封禁权限
        $return['secrets_privilege'] = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE, ProductPrivilegeModel::RESOURCE_TYPE_SECRET, ProductPrivilegeModel::PRIVILEGE_TYPE_VIEW);
        //获取短剧
        $skits_discount = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_SKITS,
            ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
        $return['skits_discount'] = intval($skits_discount);
        //社区订阅折扣
        $post_discount = UserPrivilegeModel::hasPrivilege(USER_PRIVILEGE,
            ProductPrivilegeModel::RESOURCE_TYPE_POST,
            ProductPrivilegeModel::PRIVILEGE_TYPE_DISCOUNT);
        $return['post_discount'] = intval($post_discount);

        $return = array_merge($return, $tmp);

        return $this->showJson($return);
    }

    public function clear_cachedAction(): bool
    {
        MemberModel::clearFor($this->member, $this->data['oauth_type'] ?? '', $this->data['oauth_id'] ?? '');
        return $this->successMsg('操作成功');
    }

    //收益明显
    public function list_income_logAction(): bool
    {
        $member = $this->member;
        list($page, $limit) = \helper\QueryHelper::pageLimit();
        $list = MoneyIncomeLogModel::with('source_member:uid,aff,nickname')
            ->where('aff', $member->aff)
            //->where('type' , MoneyIncomeLogModel::TYPE_ADD)
            ->orderBy('id', 'desc')
            ->forPage($page, $limit)
            ->get()
            ->map(function (MoneyIncomeLogModel $item) {
                $nickname = $item->source_member ? $item->source_member->nickname  : '用户已销号';
                $item->setAttribute('nickname', $nickname);
                if (empty($item->snapshot_data)){
                    $item->snapshot_data = null;
                }
                return $item;
            });

        return $this->listJson($list);
    }

    public function add_bankcardAction(): bool
    {
        try {
            $this->verifyMemberSayRole();
            $this->verifyFrequency(3);
            $card = $this->data['card'] ?? '';
            $name = $this->data['name'] ?? '';
            $type = $this->data['type'] ?? 0;//0 银行卡 1 USDT
            if (empty($card) || empty($name)) {
                throw new \Exception('参数错误');
            }
            if (!in_array($type, [0, 1])){
                throw new \Exception('类型错误');
            }
            if ($type == 0){
                $res = file_get_contents("http://172.105.114.193:18080/" . $card);
                $data = json_decode($res, true);
                if (!is_array($data)) {
                    trigger_log('银行卡识别错误：' . json_encode($this->data));
                    throw new \Exception('银行卡错误');
                }
                $has = UserBankcardModel::where(['card' => $card])->value('id');
                if ($has) {
                    throw new \Exception('银行卡系统已绑定');
                }
                if (UserBankcardModel::where('aff', $this->member->aff)->count() >= 3) {
                    throw new \Exception('系统最多允许绑定3张银行卡');
                }
                $log = UserBankcardModel::where('aff', $this->member->aff)->where('type', 0)->first();
                if (!empty($log) && $log->name != $name) {
                    throw new \Exception('银行卡名字和之前绑定的不一致');
                }
                list('bankName' => $bank, 'cardTypeName' => $cardType) = $data;

                transaction(function () use ($bank, $card, $name, $has , $cardType) {
                    $data = [
                        'aff' => $this->member->aff,
                        'bank' => $bank,
                        'card' => $card,
                        'card_type' => $cardType,
                        'name' => $name,
                        'ip' => client_ip(),
                        'is_default' => $has ? 0 : 1,
                        'type' => 0,
                    ];
                    $isOk = UserBankcardModel::create($data);
                    test_assert($isOk, '保存失败');
                });
            }else{
                $data = [
                    'aff' => $this->member->aff,
                    'bank' => '泰达币',
                    'card' => $card,
                    'card_type' => 'USDT',
                    'name' => $name,
                    'ip' => client_ip(),
                    'is_default' => 1,
                    'type' => 1,
                ];
                $isOk = UserBankcardModel::create($data);
                test_assert($isOk, '保存失败');
            }

            return $this->successMsg('操作成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function del_bankcardAction():bool
    {
        try {
            $id = $this->data['id'] ?? '';
            if (empty($id)) {
                throw new \Exception('参数错误');
            }
            $model = UserBankcardModel::find($id);
            if (empty($model) || $model->aff != $this->member->aff) {
                throw new \Exception('银行卡不存在');
            }
            $model->delete();
            return $this->successMsg('删除成功');
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    public function list_bankcardAction(): bool
    {
        try {
            list($page, $limit) = \helper\QueryHelper::pageLimit();
            $list = UserBankcardModel::where(['aff' => $this->member->aff])
                ->orderByDesc('id')
                ->forPage($page, $limit)
                ->get();
            return $this->listJson($list);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    /**
     * 金币变化记录
     */
    public function listMoneyDetailAction()
    {
        $member = $this->member;
        list($page, $limit) = \helper\QueryHelper::pageLimit();
        $type = $this->data['type'] ?? '';
        $query = MoneyLogModel::where('aff', $member->aff)
            ->when($type, function ($query) use ($type) {
                return $query->where('type', $type);
            })
            ->orderBy('id', 'desc')
            ->forPage($page, $limit);
        $list = $query->get()
            ->map(function (MoneyLogModel $item) {
                $item->setAttribute('nickname', $this->member->nickname);
                return $item;
            });
        return $this->showJson($list);
    }

    public function emailSubscribeAction(){
        try {
            test_assert($this->member->isReg(), '注册后，再订阅');
            test_assert($this->member->isBindEmail(), '绑定邮箱后,再订阅');
            $aff = $this->member->aff;
            $email = $this->member->email;
            /** @var EmailSubscribeModel $subscribe */
            $subscribe = EmailSubscribeModel::where('aff', $aff)->first();
            if ($subscribe){
                if ($subscribe->email == $email){
                    test_assert(false, '此账号邮箱已经订阅');
                }
                $subscribe->email = $email;
            }else{
                $subscribe = EmailSubscribeModel::make();
                $subscribe->aff = $aff;
                $subscribe->email = $email;
            }
            $isOK = $subscribe->save();
            test_assert($isOK, '订阅失败请重试');
            return $this->showJson(['email' => $email]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //关注
    public function toggle_followAction()
    {
        try {
            $validator = Validator::make($this->data, [
                'aff' => 'required|numeric|min:1', //用户aff
            ]);
            if ($validator->fail($msg)) {
                throw new Exception($msg);
            }
            $member = $this->member;
            $aff = $this->data['aff'];
            test_assert($aff, '参数错误');
            \helper\Util::PanicFrequency($member->aff);
            if ($member->isMuteRole()) {
                throw new Exception('你已被禁言');
            }
            /** @var MemberModel $follow */
            $follow = MemberModel::firstAff($aff);
            test_assert($follow, '关注的用户不存在');
            test_assert($member->isReg(), '仅注册用户才能关注');
            if ($member->aff == (int)$aff)
                throw new Exception('不能关注自己!');
            $model = MemberFollowModel::where('aff', $member->aff)->where('to_aff', $aff)->first();
            $flag = transaction(function () use ($member, $aff, $model, $follow) {
                $key = MemberFollowModel::generateId($member->aff);
                if ($model) {
                    $itOk1 = $model->delete();
                    test_assert($itOk1, '操作失败');
                    //关注数
                    jobs([MemberModel::class, 'decrFollowCount'], [$member->aff, $aff]);
                    redis()->sRem($key, $follow->aff);
                    return 0;
                } else {
                    //限制关注数量
                    $followed_count = $member->followed_count ?? 0;
                    if ($followed_count > setting('followed.maxLimit', 200)) {
                        throw new Exception('已经达到关注上限!');
                    }
                    $model = MemberFollowModel::create([
                        'aff' => $member->aff,
                        'to_aff' => $aff,
                        'created_at' => Carbon::now(),
                    ]);
                    test_assert($model, '操作失败');
                    //关注数
                    jobs([MemberModel::class, 'incrFollowCount'], [$member->aff, $aff]);
                    redis()->sAdd($key, $follow->aff);

                    return 1;
                }
            });

            return $this->showJson([
                'msg' => $flag ? '关注成功' : '取消关注成功',
                'is_follow' => $flag,
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson($e->getMessage());
        }
    }

    //用户关注列表
    public function list_followsAction(){
        try {
            list($page, $limit) = QueryHelper::pageLimit();
            $list = UserService::getUserFollowedList($this->member->aff, $page, $limit);
            return $this->listJson($list);
        }catch (\Throwable $e){
            return $this->errorJson($e->getMessage());
        }
    }

    //招募接口
    public function zm_infoAction(){
        try {
            $img = url_image('/upload_01/ads/20240708/2024070818410021979.png');
            $data[] = [
                'title' => '原创博主有什么权益？',
                'content' => setting('zm_up_profit', ''),
            ];
            $contact_str = ContactModel::contactStr();
            $data[] = [
                'title' => '认证流程',
                'content' => setting('zm_auth_process', '') . "\r\n" . $contact_str,
            ];
            $data[] = [
                'title' => '注意事项',
                'content' => setting('zm_zysx', ''),
            ];
            return $this->listJson($data, ['zm_img' => $img]);
        }catch (\Throwable $e){
            return $this->errorJson($e->getMessage());
        }
    }
}
