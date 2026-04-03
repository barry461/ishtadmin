<?php
namespace commands;

use Illuminate\Support\Facades\DB;
use Yaf\Registry;

class ModelCommand
{
    public $signature = 'make:model';
    public $description = '生成模型';

    private $desc = [];
    private $column = '';
    private $modelName = '';

    public function handle($table)
    {
        $config = Registry::get('database');
        // var_dump($config);exit;
        try {
            $this->modelName = $this->toCamelCase($table);
            // var_dump($config->prefix);exit;
            $columns = \DB::select('show full columns from ' . $config->prefix . $table);

            $fillAble = [];
            $key = '';
            foreach ($columns as $column) {
                $type = $this->getColumnType($column->Type);
                $this->desc[] = " * @property {$type} \${$column->Field} {$column->Comment}";
                $fillAble[] = "'" . $column->Field . "'";
                if ($column->Key === 'PRI') {
                    $key = $column->Field;
                }
            }
            $fillAble = implode(",\n", $fillAble);

            $tpl = file_get_contents(__DIR__ . '/model.tpl');
            $models  =  sprintf($tpl, implode("\n", $this->desc), $this->modelName, $table, $fillAble, $key);
            // echo $models;exit;
            $this->write($models);
            //
            echo '生成完成';
        }catch (\Exception $exception) {
            echo '表不存在或异常';
        }
    }

    private function write($content)
    {
        try {
            $path = APP_PATH . 'application/models/' . ucfirst($this->modelName) . '.php';
            if (file_exists($path)){
                die($content . "\r\n");
            }
            $file = fopen($path, 'wb+');
            fwrite($file, $content);
            fclose($file);
            echo '文件位置：' . $path . "\r\n";
        }catch (\Exception $exception)  {
            echo '文件写入失败';
        }
    }

    private function getColumnType($type)
    {
        $int = ['int', 'tinyint', 'double', 'integer', 'bigint', 'float', 'decimal', 'timestamp'];
        $newType = substr($type, 0, strpos($type, '('));
        return in_array($newType, $int) ? 'int' : 'string';
    }

    /**
     * 转驼峰
     * @param string $string
     * @return string
     */
    private function toCamelCase(string $string):string
    {
        $array =  explode('_', $string);
        $temp = '';
        if (count($array) > 1) {

            foreach ($array as $item) {
                $temp .= ucfirst($item);
            }
        } else {
            $temp = ucfirst($string);
        }
        return $temp;
    }
}