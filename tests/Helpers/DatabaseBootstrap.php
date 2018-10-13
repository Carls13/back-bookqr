<?php

namespace App\Tests\Helpers;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class DatabaseBootstrap extends KernelTestCase
{
    protected $upCommands = [
        ['command' => 'doctrine:database:create'],
        ['command' => 'doctrine:migrations:migrate'],
        ['command' => 'doctrine:fixtures:load'],
    ];

    protected $downCommands = [
        ['command' => 'doctrine:database:drop', '--force' => true],
    ];

    /**
     * @var Application
     */
    protected $app;

    public function up()
    {
        $kernel = static::createKernel();
        $this->app = new Application($kernel);
        $this->app->setAutoExit(false);

        //run commands for each database fixture load task
        foreach ($this->upCommands as $command) {
            self::runCommand($command);
        }
    }

    public function down()
    {
        //run commands for each database fixture load task
        foreach ($this->downCommands as $command) {
            self::runCommand($command);
        }
    }

    public function runCommand($command)
    {
        $input = new ArrayInput($command);

        $output = new NullOutput();
        $this->app->run($input, $output);
    }

}
