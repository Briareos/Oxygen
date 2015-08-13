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

use Oxygen_Process_PhpExecutableFinder;
use Oxygen_Process_PhpProcess;

class PhpProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testNonBlockingWorks()
    {
        $expected = 'hello world!';
        $process = new Oxygen_Process_PhpProcess(<<<PHP
<?php echo '$expected';
PHP
        );
        $process->start();
        $process->wait();
        $this->assertEquals($expected, $process->getOutput());
    }

    public function testCommandLine()
    {
        $process = new Oxygen_Process_PhpProcess(<<<PHP
<?php echo 'foobar';
PHP
        );

        $f = new Oxygen_Process_PhpExecutableFinder();
        $commandLine = $f->find();

        $this->assertSame($commandLine, $process->getCommandLine(), '::getCommandLine() returns the command line of PHP before start');

        $process->start();
        $this->assertSame($commandLine, $process->getCommandLine(), '::getCommandLine() returns the command line of PHP after start');

        $process->wait();
        $this->assertSame($commandLine, $process->getCommandLine(), '::getCommandLine() returns the command line of PHP after wait');
    }
}
