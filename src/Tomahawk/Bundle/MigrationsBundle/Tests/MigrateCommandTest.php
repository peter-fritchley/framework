<?php

namespace Tomahawk\Bundle\MirgrationsBundle\Tests;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Tomahawk\Console\Application;
use Tomahawk\HttpKernel\TestKernel;
use Tomahawk\Test\TestCase;
use Tomahawk\Bundle\MigrationsBundle\Command\MigrateCommand;

class MigrateCommandTest extends TestCase
{
    public function testMigrateUpRuns()
    {
        $migrator = $this->getMigrator();

        $migrator->expects($this->once())
            ->method('run');

        $migrator->expects($this->once())
            ->method('getNotes')
            ->will($this->returnValue(array(
                '<info>Migrated</info>'
            )));

        $command = new MigrateCommand();
        $commandTester = $this->getCommandTester($command, $migrator);

        $commandTester->execute(array('command' => $command->getName()));

        $this->assertRegExp('/Migrated/', $commandTester->getDisplay());
    }

    protected function getMigrator()
    {
        $repo = $this->getMockBuilder('Tomahawk\Bundle\MigrationsBundle\Migrator\Migrator')
            ->disableOriginalConstructor()
            ->getMock();

        return $repo;
    }
    /**
     * @param \Symfony\Component\Console\Command\Command $command
     * @param $migrator
     * @return CommandTester
     */
    protected function getCommandTester(Command $command, $migrator)
    {
        $app = new TestKernel('prod', false);
        $app->boot();
        $application = new Application($app);
        $application->setAutoExit(false);

        $container = $application->getKernel()->getContainer();

        $container->set('migrator', $migrator);

        $application->add($command);

        $command = $application->find('migration:migrate');
        $command->setContainer($container);
        $commandTester = new CommandTester($command);

        return $commandTester;
    }
}