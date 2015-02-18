<?php
/**
 * Created by PhpStorm.
 * User: mike
 * Date: 30/07/14
 * Time: 16:48
 */

namespace Doctrine\Bundle\DoctrineBundle\Tests\Command;

use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class CreateDatabaseDoctrineTest extends \PHPUnit_Framework_TestCase
{
    public function testExecute()
    {
        $connectionName = 'default';
        $dbName = 'test';
        $params = array(
            'dbname' => $dbName,
            'memory' => true,
            'driver' => 'pdo_sqlite'
        );

        $application = new Application();
        $application->add(new CreateDatabaseDoctrineCommand());

        $command = $application->find('doctrine:database:create');
        $command->setContainer($this->getMockContainer($connectionName, $params));

        $commandTester = new CommandTester($command);
        $commandTester->execute(
            array_merge(array('command' => $command->getName()))
        );

        $this->assertRegExp("/Created database \"$dbName\" for connection named $connectionName/", $commandTester->getDisplay());
    }

    protected function getMockContainer($connectionName, $params=null)
    {
        // Mock the container and everything you'll need here
        $mockDoctrine = $this->getMockBuilder('Doctrine\Common\Persistence\ConnectionRegistry')
            ->getMock();

        $mockDoctrine->expects($this->any())
            ->method('getDefaultConnectionName')
            ->withAnyParameters()
            ->willReturn($connectionName)
        ;


        $mockConnection = $this->getMockBuilder('Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->setMethods(array('getParams'))
            ->getMockForAbstractClass();

        $mockConnection->expects($this->any())
            ->method('getParams')
            ->withAnyParameters()
            ->willReturn($params);

        
        $mockDoctrine->expects($this->any())
            ->method('getConnection')
            ->withAnyParameters()
            ->willReturn($mockConnection);
        ;


        $mockContainer = $this->getMockBuilder('Symfony\Component\DependencyInjection\Container')
            ->setMethods(array('get'))
            ->getMock();

        $mockContainer->expects($this->any())
            ->method('get')
            ->with('doctrine')
            ->willReturn($mockDoctrine);

        return $mockContainer;
    }
}
