<?php


class BanipController extends BackendBaseController
{

    use \repositories\HoutaiRepository;

    public function listAjaxAction(): bool
    {
        $ips = redis()->sMembers(BAN_IPS_KEY);
        $all = collect($ips)->map(function ($ip) {
            return ['ip' => $ip];
        });
        $result = [
            'count' => $all->count(),
            'data'  => $all,
            "msg"   => '',
            "desc"  => '',
            'code'  => 0
        ];
        return $this->ajaxReturn($result);
    }

    public function indexAction()
    {
        $this->display();
    }

    protected function getModelClass(): string
    {
        return '';
    }

    protected function getPkName(): string
    {
        return 'ip';
    }

    protected function getLogDesc(): string
    {
        return '';
    }


    public function delAllAction(): bool
    {
        try {
            $banIps = $_POST['ips'] ?? '';
            $banIps = explode(",", $banIps);
            $banIps = array_unique($banIps);
            $banIps = array_filter($banIps);
            collect($banIps)->map(function ($ip) {
                redis()->sRem(BAN_IPS_KEY, $ip);
            });
            return $this->ajaxSuccessMsg('删除禁止IP成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function saveAction()
    {
        try {
            $ip = $_POST['ip'] ?? '';
            test_assert($ip, '禁止IP不能为空');
            //如果是IPV6 不处理
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)){
                $host = $ip;
            }else{
                $ip1 = 'https://' . $ip;
                $host = parse_url($ip1, PHP_URL_HOST);
                if (!$host){
                    $host = $ip;
                }
            }
            redis()->sAdd(BAN_IPS_KEY, $host);
            return $this->ajaxSuccessMsg('禁止IP成功');
        } catch (Throwable $e) {
            return $this->ajaxError($e->getMessage());
        }
    }

    public function sysWebAction(){
        $ips = redis()->sMembers(BAN_IPS_KEY);
        if (count($ips) > 0){
            $ips = implode(',',$ips);
        }else{
            $ips = '';
        }
        $url = 'https://51cg1.com/ping.php?_yaf=ban-ip';
        \tools\HttpCurl::post($url,['ip' => $ips]);
        $this->ajaxSuccessMsg('同步成功');
    }
}