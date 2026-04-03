<?php

/**
 * 主播收益
 * Class IndexController
 */
class ChartController extends \Yaf\Controller_Abstract
{

    static $baseDir = APP_PATH . '/storage/chart/';

    public function chartActiveUserAction()
    {
        $this->chartActiveUser(strtotime(date('Y-m-d 00:00:00')));
    }

    /**
     * 日活
     * @param int $item 日期的时间戳
     * @return int|mixed
     */
    protected function chartActiveUser($item)
    {
        $file = __FUNCTION__;
        $data = $this->getContentData($file);
        $date = date('Ymd', $item);
        if ($date === date('Ymd')) {
            $number = MemberLogModel::whereBetween('lastactivity', [$item, $item + 86400])->count('id');
            $data[$date] = $number;
            $this->setContentData($file, $data);
        }
        return $data[$date] ?? 0;
    }

    /**
     * 日活的依赖
     * @param $fileName
     * @param $data
     */
    protected function setContentData($fileName, $data)
    {
        $pathFile = self::$baseDir . $fileName . '.json';
        if (!file_exists(dirname($pathFile))) {
            @mkdir(dirname($pathFile), 0755, true);
        }
        file_put_contents($pathFile, json_encode($data));
    }

    /**
     * 日活的依赖
     * @param $fileName
     * @return array
     */
    protected function getContentData($fileName)
    {
        $pathFile = self::$baseDir . $fileName . '.json';
        if (!file_exists($pathFile)) {
            return [];
        }
        $data = file_get_contents($pathFile);
        return json_decode($data, true);
    }


}