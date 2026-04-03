<?php

use service\OptionService;
use tools\GoogleAuthenticator;

class SettingController extends AdminV2BaseController
{
    /**
     * 保存站点基础设置
     *
     * 接口示例（POST /adminv2/setting/doSet）：
     * 支持的参数（全部可选，传哪个改哪个）：
     * - title               站点名称
     * - siteUrl             站点地址
     * - description         站点描述
     * - keywords            站点关键词
     * - siteDes             首页SEO描述
     * - index_seo_title     首页SEO标题
     * - index_seo_desc      首页SEO描述（如需与 siteDes 区分）
     * - zz_title            中转页名称
     * - zz_siteUrl          中转页展示域名
     * - zz_description      中转页描述
     * - zz_keywords          中转页关键词
     * - zz_line             中转页主线路域名（可为逗号分隔的多域名）
     * - zz_backup_line      中转页备用线路域名（可为逗号分隔的多域名）
     */
    public function doSetAction()
    {
        if (!$this->getRequest()->isPost()) {
            $this->errorJson('请求方式错误');
            return false;
        }

        // 允许更新的字段（与旧 Admin 后台保持兼容）
        $allowedFields = [
            'title',
            'brand',
            'siteUrl',
            'description',
            'keywords',
            'siteDes',
            'index_seo_title',
            'index_seo_desc',
            'zz_title',
            'zz_siteUrl',
            'zz_description',
            'zz_keywords',
            'zz_line',
            'zz_backup_line',
        ];

        $params = $this->data ?? [];
        $updateData = [];

        foreach ($allowedFields as $field) {
            if (isset($params[$field])) {
                $updateData[$field] = trim((string)$params[$field]);
            }
        }

        if (empty($updateData)) {
            $this->errorJson('未传入任何修改参数');
            return false;
        }

        try {
            transaction(function () use ($updateData) {
                $optionService = new OptionService();

                foreach ($updateData as $key => $val) {
                    // 保存到 options 表
                    $optionService->set($key, $val);

                    // 同步更新 application/config.php 中的 site_url（兼容旧逻辑）
                    if ($key === 'siteUrl') {
                        $file = APP_PATH . '/application/config.php';
                        $site = @include $file;
                        if (!is_array($site)) {
                            $site = [];
                        }
                        $site['site_url'] = $val;
                        $writeData = "<?php\n\n\nreturn " . var_export($site, true) . ";";
                        
                        // 使用临时文件原子写入，避免文件损坏
                        $tempFile = $file . '.tmp.' . time();
                        $written = @file_put_contents($tempFile, $writeData, LOCK_EX);
                        if ($written !== false && $written > 0) {
                            if (@rename($tempFile, $file)) {
                                // 写入成功
                            } else {
                                @unlink($tempFile);
                                throw new \Exception('写入配置文件失败');
                            }
                        } else {
                            @unlink($tempFile);
                            throw new \Exception('写入配置文件失败');
                        }
                    }
                }
            });

            // 清理 options 相关缓存（与旧 Admin 行为保持一致）
            yac()->delete("options");
            yac()->delete("options:all");

            $this->successMsg('保存成功', [
                'settings' => $this->getSettingsData(),
            ]);
            return false;
        } catch (\Throwable $e) {
            $this->errorJson('保存失败：' . $e->getMessage());
            return false;
        }
    }

    /**
     * 获取站点基础设置
     *
     * GET /adminv2/setting/get
     */
    public function getAction()
    {
        $data = $this->getSettingsData();
        $this->showJson($data);
        return false;
    }

    /**
     * 聚合站点基础设置数据
     */
    protected function getSettingsData(): array
    {
        // 使用全局 options() 方法获取，与前台保持一致
        $title       = options('title', '');
        $brand       = options('brand', '');
        $siteUrl     = options('siteUrl', '');
        $description = options('description', '');
        $keywords    = options('keywords', '');
        $siteDes     = options('siteDes', '');

        // 新增首页SEO字段（如果未设置则回退到已有字段）
        $indexSeoTitle = options('index_seo_title', $title);
        $indexSeoDesc  = options('index_seo_desc', $siteDes ?: $description);

        // 中转页相关配置
        $zzTitle      = options('zz_title', '');
        $zzSiteUrl    = options('zz_siteUrl', '');
        $zzDescription = options('zz_description', '');
        $zzKeywords   = options('zz_keywords', '');
        $zzLine       = options('zz_line', '');
        $zzBackupLine = options('zz_backup_line', '');

        return [
            'title'            => $title,
            'brand'            => $brand,
            'siteUrl'          => $siteUrl,
            'description'      => $description,
            'keywords'         => $keywords,
            'siteDes'          => $siteDes,
            'index_seo_title'  => $indexSeoTitle,
            'index_seo_desc'   => $indexSeoDesc,
            'zz_title'         => $zzTitle,
            'zz_siteUrl'       => $zzSiteUrl,
            'zz_description'  => $zzDescription,
            'zz_keywords'      => $zzKeywords,
            'zz_line'          => $zzLine,
            'zz_backup_line'   => $zzBackupLine,
            'current_host'     => $_SERVER['HTTP_HOST'] ?? '',
            'current_protocol' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
        ];
    }

    /**
     * 修改谷歌动态验证码密钥
     * POST /adminv2/setting/updateSecret
     * 
     * 参数:
     * - password: 当前密码 (必填)
     * - card_num: 当前动态码 (如果已绑定密钥则必填，用于验证身份)
     * - secret: 新密钥 (可选，留空则自动生成)
     */
    public function updateSecretAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $password = $this->data['password'] ?? '';
        $cardNum = $this->data['card_num'] ?? '';
        $secret = trim((string)($this->data['secret'] ?? ''));

        if (empty($password)) {
            return $this->validationError('当前密码不能为空');
        }

        // 获取当前登录用户
        if (empty($this->user)) {
            return $this->unauthorized('请先登录');
        }

        $user = $this->user;

        // 验证当前密码
        if (!$user->verifyPassword($password)) {
            return $this->errorJson('当前密码错误');
        }

        // 如果已绑定密钥，需要验证当前动态码
        if (!empty($user->secret)) {
            if (empty($cardNum) || strlen($cardNum) != 6) {
                return $this->validationError('动态码格式错误，必须为6位数字');
            }
            $googleAuthor = new GoogleAuthenticator();
            $secretCheck = $googleAuthor->verifyCode($user->secret, $cardNum, 1);
            if (!$secretCheck) {
                return $this->errorJson('动态码验证失败，请确认当前动态码是否正确');
            }
        }
        // 如果未绑定密钥，则不需要验证动态码

        try {
            transaction(function () use ($secret, $user) {
                // 如果传入了新密钥，使用传入的；否则自动生成
                if (empty($secret)) {
                    $googleAuthor = new GoogleAuthenticator();
                    $newSecret = $googleAuthor->createSecret(32);
                } else {
                    $newSecret = $secret;
                }
                
                // 直接用 update 只改密钥，防止把用户名密码等其他字段也改了
                $updateResult = ManagerModel::where('uid', $user->uid)
                    ->update(['secret' => $newSecret]);
                
                if (!$updateResult) {
                    throw new \Exception('更新密钥失败');
                }
            });

            // 重新获取用户信息以获取新密钥
            $user->refresh();
            $googleAuthor = new GoogleAuthenticator();
            $qrUrl = $googleAuthor->getQRCodeGoogleUrl($user->username, $user->secret, options('title', '管理后台'));

            return $this->successMsg('密钥更新成功', [
                'secret' => $user->secret,
                'qr_url' => $qrUrl,
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson('更新失败：' . $e->getMessage());
        }
    }

    /**
     * 修改个人密码
     * POST /adminv2/setting/updatePassword
     * 
     * 参数:
     * - old_password: 当前密码 (必填)
     * - new_password: 新密码 (必填)
     * - confirm_password: 确认密码 (必填)
     */
    public function updatePasswordAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $oldPassword = $this->data['old_password'] ?? '';
        $newPassword = $this->data['new_password'] ?? '';
        $confirmPassword = $this->data['confirm_password'] ?? '';

        if (empty($oldPassword)) {
            return $this->validationError('当前密码不能为空');
        }
        if (empty($newPassword)) {
            return $this->validationError('新密码不能为空');
        }
        if (strlen($newPassword) < 6) {
            return $this->validationError('新密码长度不能少于6位');
        }
        if ($newPassword !== $confirmPassword) {
            return $this->validationError('两次输入的密码不一致');
        }
        if ($oldPassword === $newPassword) {
            return $this->validationError('新密码不能与当前密码相同');
        }

        // 获取当前登录用户
        if (empty($this->user)) {
            return $this->unauthorized('请先登录');
        }

        $user = $this->user;

        // 验证当前密码
        if (!$user->verifyPassword($oldPassword)) {
            return $this->errorJson('当前密码错误');
        }

        try {
            transaction(function () use ($user, $newPassword) {
                // 直接用 update 只改密码，防止把用户名等其他字段也改了
                $updateResult = ManagerModel::where('uid', $user->uid)
                    ->update(['password' => ManagerModel::makePasswordHash($newPassword)]);
                
                if (!$updateResult) {
                    throw new \Exception('更新密码失败');
                }
            });

            return $this->successMsg('密码修改成功');
        } catch (\Throwable $e) {
            return $this->errorJson('修改失败：' . $e->getMessage());
        }
    }

    /**
     * 修改昵称（管理员显示名称）
     * POST /adminv2/setting/updateNickname
     *
     * 参数:
     * - nickname: 新昵称 (必填)
     * 
     * 说明:
     * - nickname 是显示名称，与登录账号 username 分离
     * - username 是登录的唯一标识，不能修改
     */
    public function updateNicknameAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->errorJson('请求方式错误');
        }

        $nickname = trim((string)($this->data['nickname'] ?? ''));

        if ($nickname === '') {
            return $this->validationError('昵称不能为空');
        }
        if (mb_strlen($nickname) > 50) {
            return $this->validationError('昵称长度不能超过50个字符');
        }

        // 获取当前登录用户
        if (empty($this->user)) {
            return $this->unauthorized('请先登录');
        }

        /** @var ManagerModel $user */
        $user = $this->user;

        // 如果昵称未变化，直接返回
        if ($user->nickname === $nickname) {
            return $this->successMsg('昵称修改成功', [
                'nickname' => $user->nickname,
            ]);
        }

        try {
            transaction(function () use ($user, $nickname) {
                // 修改 nickname 字段，不修改 username（username 是登录标识）
                $updateResult = ManagerModel::where('uid', $user->uid)
                    ->update(['nickname' => $nickname]);
                
                if (!$updateResult) {
                    throw new \Exception('更新昵称失败');
                }
            });

            return $this->successMsg('昵称修改成功', [
                'nickname' => $nickname,
            ]);
        } catch (\Throwable $e) {
            return $this->errorJson('修改失败：' . $e->getMessage());
        }
    }
}