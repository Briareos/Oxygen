<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Oxygen\Tests\Process;

use Oxygen_Process_ProcessBuilder;

class ProcessBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testInheritEnvironmentVars()
    {
        $_ENV['MY_VAR_1'] = 'foo';

        $proc = Oxygen_Process_ProcessBuilder::create()
            ->add('foo')
            ->getProcess();

        unset($_ENV['MY_VAR_1']);

        $env = $proc->getEnv();
        $this->assertArrayHasKey('MY_VAR_1', $env);
        $this->assertEquals('foo', $env['MY_VAR_1']);
    }

    public function testAddEnvironmentVariables()
    {
        $pb = new Oxygen_Process_ProcessBuilder();
        $env = array(
            'foo' => 'bar',
            'foo2' => 'bar2',
        );
        $proc = $pb
            ->add('command')
            ->setEnv('foo', 'bar2')
            ->addEnvironmentVariables($env)
            ->inheritEnvironmentVariables(false)
            ->getProcess()
        ;

        $this->assertSame($env, $proc->getEnv());
    }

    public function testProcessShouldInheritAndOverrideEnvironmentVars()
    {
        $_ENV['MY_VAR_1'] = 'foo';

        $proc = Oxygen_Process_ProcessBuilder::create()
            ->setEnv('MY_VAR_1', 'bar')
            ->add('foo')
            ->getProcess();

        unset($_ENV['MY_VAR_1']);

        $env = $proc->getEnv();
        $this->assertArrayHasKey('MY_VAR_1', $env);
        $this->assertEquals('bar', $env['MY_VAR_1']);
    }

    /**
     * @expectedException \Oxygen_Process_Exception_InvalidArgumentException
     */
    public function testNegativeTimeoutFromSetter()
    {
        $pb = new Oxygen_Process_ProcessBuilder();
        $pb->setTimeout(-1);
    }

    public function testNullTimeout()
    {
        $pb = new Oxygen_Process_ProcessBuilder();
        $pb->setTimeout(10);
        $pb->setTimeout(null);

        $r = new \ReflectionObject($pb);
        $p = $r->getProperty('timeout');
        $p->setAccessible(true);

        $this->assertNull($p->getValue($pb));
    }

    public function testShouldSetArguments()
    {
        $pb = new Oxygen_Process_ProcessBuilder(array('initial'));
        $pb->setArguments(array('second'));

        $proc = $pb->getProcess();

        $this->assertContains('second', $proc->getCommandLine());
    }

    public function testPrefixIsPrependedToAllGeneratedProcess()
    {
        $pb = new Oxygen_Process_ProcessBuilder();
        $pb->setPrefix('/usr/bin/php');

        $proc = $pb->setArguments(array('-v'))->getProcess();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" "-v"', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' '-v'", $proc->getCommandLine());
        }

        $proc = $pb->setArguments(array('-i'))->getProcess();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" "-i"', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' '-i'", $proc->getCommandLine());
        }
    }

    public function testArrayPrefixesArePrependedToAllGeneratedProcess()
    {
        $pb = new Oxygen_Process_ProcessBuilder();
        $pb->setPrefix(array('/usr/bin/php', 'composer.phar'));

        $proc = $pb->setArguments(array('-v'))->getProcess();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" "composer.phar" "-v"', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' 'composer.phar' '-v'", $proc->getCommandLine());
        }

        $proc = $pb->setArguments(array('-i'))->getProcess();
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php" "composer.phar" "-i"', $proc->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php' 'composer.phar' '-i'", $proc->getCommandLine());
        }
    }

    public function testShouldEscapeArguments()
    {
        $pb = new Oxygen_Process_ProcessBuilder(array('%path%', 'foo " bar', '%baz%baz'));
        $proc = $pb->getProcess();

        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertSame('^%"path"^% "foo \\" bar" "%baz%baz"', $proc->getCommandLine());
        } else {
            $this->assertSame("'%path%' 'foo \" bar' '%baz%baz'", $proc->getCommandLine());
        }
    }

    public function testShouldEscapeArgumentsAndPrefix()
    {
        $pb = new Oxygen_Process_ProcessBuilder(array('arg'));
        $pb->setPrefix('%prefix%');
        $proc = $pb->getProcess();

        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertSame('^%"prefix"^% "arg"', $proc->getCommandLine());
        } else {
            $this->assertSame("'%prefix%' 'arg'", $proc->getCommandLine());
        }
    }

    /**
     * @expectedException \Oxygen_Process_Exception_LogicException
     */
    public function testShouldThrowALogicExceptionIfNoPrefixAndNoArgument()
    {
        Oxygen_Process_ProcessBuilder::create()->getProcess();
    }

    public function testShouldNotThrowALogicExceptionIfNoArgument()
    {
        $process = Oxygen_Process_ProcessBuilder::create()
            ->setPrefix('/usr/bin/php')
            ->getProcess();

        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php'", $process->getCommandLine());
        }
    }

    public function testShouldNotThrowALogicExceptionIfNoPrefix()
    {
        $process = Oxygen_Process_ProcessBuilder::create(array('/usr/bin/php'))
            ->getProcess();

        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->assertEquals('"/usr/bin/php"', $process->getCommandLine());
        } else {
            $this->assertEquals("'/usr/bin/php'", $process->getCommandLine());
        }
    }

    public function testShouldReturnProcessWithDisabledOutput()
    {
        $process = Oxygen_Process_ProcessBuilder::create(array('/usr/bin/php'))
            ->disableOutput()
            ->getProcess();

        $this->assertTrue($process->isOutputDisabled());
    }

    public function testShouldReturnProcessWithEnabledOutput()
    {
        $process = Oxygen_Process_ProcessBuilder::create(array('/usr/bin/php'))
            ->disableOutput()
            ->enableOutput()
            ->getProcess();

        $this->assertFalse($process->isOutputDisabled());
    }

    /**
     * @expectedException \Oxygen_Process_Exception_InvalidArgumentException
     * @expectedExceptionMessage Oxygen_Process_ProcessBuilder::setInput only accepts strings or stream resources.
     */
    public function testInvalidInput()
    {
        $builder = Oxygen_Process_ProcessBuilder::create();
        $builder->setInput(array());
    }
}
