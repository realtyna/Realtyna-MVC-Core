<?php

namespace Unit;

use Realtyna\MvcCore\Config;
use Realtyna\MvcCore\Phinx;

class PhinxTest extends \WP_UnitTestCase
{
    private Phinx $phinx;
    protected Config $config;
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|\Realtyna\MvcCore\StartUp|(\Realtyna\MvcCore\StartUp&\PHPUnit\Framework\MockObject\MockObject)
     */
    protected $main;

    public function set_up()
    {
        parent::set_up();
        $configsArray = [
            'namespace' => 'test',
            'path'      => [
                'phinx' => [
                    'conf'  => __DIR__ . '/../../phinx.php'
                ]
            ]
        ];

        $this->config = new Config($configsArray);
        $this->main = $this->getMockForAbstractClass('Realtyna\MvcCore\StartUp', [$this->config]);

    }

    public function testMigrateMethod()
    {
        $this->phinx = new Phinx($this->main);

        $this->phinx->migrate();
        $this->assertStringContainsString('All Done.', $this->phinx->output->getBuffer());
    }

    public function testsSeedMethod()
    {
        $this->phinx = new Phinx($this->main);

        $this->phinx->seed();
        $this->assertStringContainsString('All Done.', $this->phinx->output->getBuffer());
    }

    public function testsRollbackMethod()
    {
        $this->phinx = new Phinx($this->main);

        $this->phinx->rollback();
        $this->assertStringContainsString('No migrations to rollback', $this->phinx->output->getBuffer());
    }

}