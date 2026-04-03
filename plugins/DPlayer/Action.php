<?php

class DPlayer_Action extends Typecho_Widget
{
    public function comments()
    {

        $id = (int)$this->request->get('id');

        $db = Typecho_Db::get();
        $query= $db->select('text')->where("type='comment' and status='approved' and cid={$id}")->limit(1000)->from('table.comments');
        $results = $db->fetchAll($query);
        $duration = 180;
        $comments = [];
        foreach ($results as $result) {
            $comments[] = [$this->generateRandomFloat(0, $duration), 0, $this->generateColor(), '', $result['text']];
        }

        $data = [
            'code' => 0,
            'data' => $comments,
            'msg' => 'ok'
        ];

        $return = json_encode($data);
        $path = __TYPECHO_ROOT_DIR__."/storage/danmaku/{$id}.json";
        write_file($path, $return);
        echo $return;
    }

    private function generateColor(): string
    {
        $colors = [
            '845EC2',
            'FF6F91',
            'FF9671',
            'FFC75F',
            'FF8066',
            '845EC2',
            'FF9000',
            'FF605C'
        ];
        return $colors[mt_rand(0, count($colors) - 1)];
    }

    private function generateRandomFloat(float $minValue, float $maxValue): float
    {
        $num = $minValue + mt_rand() / mt_getrandmax() * ($maxValue - $minValue);
        return sprintf("%.2f", $num);
    }
}