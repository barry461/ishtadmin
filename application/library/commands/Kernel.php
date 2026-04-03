<?php
namespace commands;

class Kernel
{
    protected $commands = [
        ModelCommand::class,
        QueueCommand::class,
        ReportCommand::class,
        JobsCommand::class,
        DBMigrateCommand::class,
        IndexNowCommand::class,
        IndexNowFlushAllCommand::class,
        IndexNowFlushOneHourCommand::class,
        CleanDuplicateTagsCommand::class,
        CleanEmptyTagsCommand::class,
    ];

    protected $class =[];

    public function __construct($module =  '', $argv = '')
    {
        foreach ($this->commands as $command) {
            $this->class[$command] = new $command;
            if ($this->class[$command]->signature === $module) {
                return $this->class[$command]->handle($argv);
            }
        }
        $this->handle();
    }

    public function handle()
    {
        foreach ($this->class as $class)
        {
            echo ($class->signature . '  ' . $class->description . "\n");
        }
    }
}