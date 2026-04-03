<?php

namespace commands;

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class DBMigrateCommand
{

    private $migration_table = 'migrations_log';
    public $signature = 'db:migrate';
    public $description = '迁移数据库';

    public function handle($argv)
    {
        if ($argv == "new") {
            $this->create_migrate();
        } elseif ($argv == "up") {
            $this->initMigrationTable();;
            $this->up();
        } else {
            echo <<<'S'
./cli db:migrate new        ;创建一个新的迁移脚本
./cli db:migrate up         ;执行迁移

S;
        }
    }


    private function create_migrate()
    {
        // migrations 目录
        $migrationPath = APP_PATH.'/migrations';
        if (!file_exists($migrationPath)) {
            mkdir($migrationPath, 0755, true);
        }
        $date = date('Ymd');
        $files = glob("{$migrationPath}/{$date}_*.php");
        $number  = sprintf("%003d",count($files) + 1);
        $code =<<<'PHP'
<?php

use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Schema\Blueprint;

class Migration20250904_{number}
{

    public function up()
    {
        // 以下代码等价于sql
        // alter table user add column phone varchar(20) not null after email;
        // alter table user add column address varchar(15) not null after phone;
        DB::schema()->table('user' , function (Blueprint $table){
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('address', 15)->nullable()->after('phone');
        });
        // 等价 
        // create table log ( id int not null , log varchar(255) )
        DB::schema()->create('log',function (Blueprint $table){
            $table->increments('id');
            $table->string('log' , 255)->comment('日志');
        });
    }

    public function down()
    {
    }
}
PHP;
        $code = str_replace('{number}' , $number , $code);
        $code = str_replace('20250904' , $date , $code);
        $file = "{$migrationPath}/{$date}_{$number}.php";
        file_put_contents($file , $code);
        echo "Create Migration[$file] script Success\n";

    }

    public function up()
    {
        // migrations 目录
        $migrationPath = APP_PATH . '/migrations';
        if (!file_exists($migrationPath)){
            echo "Skipped (migration dir not found): $migrationPath\n";
            return;
        }

        // 获取已执行的迁移
        $executed = DB::table($this->migration_table)->pluck('migration')->toArray();

        // 当前 batch
        $lastBatch = DB::table($this->migration_table)->max('batch') ?? 0;
        $newBatch = $lastBatch + 1;

        // 扫描文件
        $files = glob($migrationPath . '/*.php');
        sort($files);

        foreach ($files as $file) {
            $class = pathinfo($file, PATHINFO_FILENAME);
            $class = "Migration{$class}";
            if (in_array($class, $executed)) {
                echo "Skipped (already migrated): $class\n";
                continue;
            }
            require_once $file;

            $instance = new $class();
            echo "Migrating: $class ... ";
            try {
                $instance->up();
                echo "done\n";
            } catch (\Throwable $e) {
                echo "warning: {$e->getMessage()}\n";
                continue;
            }
            DB::table($this->migration_table)->insert([
                'migration' => $class,
                'batch' => $newBatch,
            ]);
        }
    }


    private function initMigrationTable()
    {
        if (!DB::schema()->hasTable($this->migration_table)) {
            DB::schema()->create($this->migration_table, function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration', 255)->nullable();
                $table->integer('batch')->nullable();
                $table->timestamp('executed_at')->useCurrent();
            });
        }
    }
}