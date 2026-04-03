<?php

class LibCsv
{
    protected $data = [];

    public function push($title, $value)
    {
        $value = str_replace(["\r","\n"] , '' , (string)$value);
        if (!isset($this->data[$title])) {
            $this->data[$title] = [];
        }
        $this->data[$title][] = str_replace(',', "\,", $value);
        echo "\t\t\t\t\t\t\t\t\t\t\r";
        echo "push $title , $value\r";

    }

    public function output($file)
    {
        file_put_contents($file, $this->toString());
    }

    public function outputGbk($file)
    {
        $str = $this->toString();
        $str = mb_convert_encoding($str , 'GBK' ,'utf-8');
        file_put_contents($file, $str);
    }

    public function print()
    {
        echo "\r\n\r\n";
        echo $this->toString();
    }

    public function toString(): string
    {

        $ary = $this->data;
        $header = [];
        foreach ($ary as $name => $items) {
            $header[] = $name;
        }
        $items = current($ary);
        $str = join(',', $header) . "\r\n";
        for ($i = 0; $i < count($items); $i++) {
            $tmp = [];
            foreach ($header as $name) {
                $tmp[] = $ary[$name][$i] ?? 0;
            }
            $str .= join(',', $tmp) . "\r\n";
        }
        return $str;
    }


}