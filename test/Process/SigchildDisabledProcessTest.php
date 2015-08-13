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

class SigchildDisabledProcessTest extends AbstractProcessTest
{
    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testGetExitCode()
    {
        parent::testGetExitCode();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testGetExitCodeIsNullOnStart()
    {
        parent::testGetExitCodeIsNullOnStart();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testGetExitCodeIsNullOnWhenStartingAgain()
    {
        parent::testGetExitCodeIsNullOnWhenStartingAgain();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testExitCodeCommandFailed()
    {
        parent::testExitCodeCommandFailed();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testMustRun()
    {
        parent::testMustRun();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testSuccessfulMustRunHasCorrectExitCode()
    {
        parent::testSuccessfulMustRunHasCorrectExitCode();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     */
    public function testMustRunThrowsException()
    {
        parent::testMustRunThrowsException();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     */
    public function testProcessIsSignaledIfStopped()
    {
        parent::testProcessIsSignaledIfStopped();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.
     */
    public function testProcessWithTermSignal()
    {
        parent::testProcessWithTermSignal();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.
     */
    public function testProcessIsNotSignaled()
    {
        parent::testProcessIsNotSignaled();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.
     */
    public function testProcessWithoutTermSignal()
    {
        parent::testProcessWithoutTermSignal();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testCheckTimeoutOnStartedProcess()
    {
        parent::testCheckTimeoutOnStartedProcess();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. The process identifier can not be retrieved.
     */
    public function testGetPid()
    {
        parent::testGetPid();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. The process identifier can not be retrieved.
     */
    public function testGetPidIsNullBeforeStart()
    {
        parent::testGetPidIsNullBeforeStart();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. The process identifier can not be retrieved.
     */
    public function testGetPidIsNullAfterRun()
    {
        parent::testGetPidIsNullAfterRun();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testExitCodeText()
    {
        $process = $this->getProcess('qdfsmfkqsdfmqmsd');
        $process->run();

        $process->getExitCodeText();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testExitCodeTextIsNullWhenExitCodeIsNull()
    {
        parent::testExitCodeTextIsNullWhenExitCodeIsNull();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testIsSuccessful()
    {
        parent::testIsSuccessful();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testIsSuccessfulOnlyAfterTerminated()
    {
        parent::testIsSuccessfulOnlyAfterTerminated();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testIsNotSuccessful()
    {
        parent::testIsNotSuccessful();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.
     */
    public function testTTYCommandExitCode()
    {
        parent::testTTYCommandExitCode();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. The process can not be signaled.
     */
    public function testSignal()
    {
        parent::testSignal();
    }

    /**
     * @expectedException \Oxygen_Process_Exception_RuntimeException
     * @expectedExceptionMessage This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.
     */
    public function testProcessWithoutTermSignalIsNotSignaled()
    {
        parent::testProcessWithoutTermSignalIsNotSignaled();
    }

    public function testStopWithTimeoutIsActuallyWorking()
    {
        $this->markTestSkipped('Stopping with signal is not supported in sigchild environment');
    }

    public function testProcessThrowsExceptionWhenExternallySignaled()
    {
        $this->markTestSkipped('Retrieving Pid is not supported in sigchild environment');
    }

    public function testExitCodeIsAvailableAfterSignal()
    {
        $this->markTestSkipped('Signal is not supported in sigchild environment');
    }

    public function testRunProcessWithTimeout()
    {
        $this->markTestSkipped('Signal (required for timeout) is not supported in sigchild environment');
    }

    public function provideStartMethods()
    {
        return array(
            array('start', 'Oxygen_Process_Exception_LogicException', 'Output has been disabled, enable it to allow the use of a callback.'),
            array('run', 'Oxygen_Process_Exception_LogicException', 'Output has been disabled, enable it to allow the use of a callback.'),
            array('mustRun', 'Oxygen_Process_Exception_RuntimeException', 'This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.'),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getProcess($commandline, $cwd = null, array $env = null, $input = null, $timeout = 60, array $options = array())
    {
        $process = new ProcessInSigchildEnvironment($commandline, $cwd, $env, $input, $timeout, $options);
        $process->setEnhanceSigchildCompatibility(false);

        return $process;
    }
}
