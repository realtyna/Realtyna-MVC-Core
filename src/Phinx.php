<?php

namespace Realtyna\MvcCore;

use Phinx\Console\PhinxApplication;
use Realtyna\MvcCore\Utilities\BufferedOutput;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;

class Phinx
{


    public BufferedOutput $output;
    protected PhinxApplication $phinx;
    private StartUp $main;

    public function __construct($main)
    {
        $this->output = new BufferedOutput();
        $this->phinx = new PhinxApplication();
        $this->main = $main;
    }

    /**
     * migrate the database
     * @return int
     * @throws ExceptionInterface
     * @since 0.0.1
     */
    public function migrate(): int
    {
        $command = $this->phinx->find('migrate');
        $arguments = [
            'command' => 'migrate',
            '--environment' => 'production',
            '--configuration' => $this->main->config->get('path.phinx.conf')
        ];

        return $command->run(new ArrayInput($arguments), $this->output);
    }


    /**
     * seed the database
     * @return int
     * @throws ExceptionInterface
     * @since 0.0.1
     */
    public function seed(): int
    {
        $command = $this->phinx->find('seed:run');
        $arguments = [
            'command' => 'seed:run',
            '--environment' => 'production',
            '--configuration' => $this->main->config->get('path.phinx.conf')
        ];

        return $command->run(new ArrayInput($arguments), $this->output);
    }

    /**
     * rollback all migrations
     * @return int
     * @throws ExceptionInterface
     * @since 0.0.1
     */
    public function rollback(): int
    {
        $command = $this->phinx->find('rollback');
        $arguments = [
            'command' => 'rollback',
            '-t' => '0',
            '--environment' => 'production',
            '--configuration' => $this->main->config->get('path.phinx.conf')
        ];

        return $command->run(new ArrayInput($arguments), $this->output);
    }

}