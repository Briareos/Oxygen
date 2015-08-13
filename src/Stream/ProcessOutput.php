<?php

class Oxygen_Stream_ProcessOutput extends Oxygen_Stream_Callable
{

    /**
     * @var Oxygen_Process_Process
     */
    private $process;

    /**
     * @var bool
     */
    private $ran = false;

    public function __construct(Oxygen_Process_Process $process)
    {
        parent::__construct(array($this, 'getIncrementalOutput'));
        $this->process = $process;
    }

    /**
     * Returns incremental process output (even if empty string) or false if the process has finished
     * successfully and all output was already returned.
     *
     * @throws Oxygen_Process_Exception_ProcessFailedException If the process did not exit successfully.
     *
     * @internal
     *
     * @return string|false
     */
    public function getIncrementalOutput()
    {
        if (!$this->ran) {
            $this->ran = true;
            try {
                $this->process->start();
            } catch (Oxygen_Process_Exception_ExceptionInterface $e) {
                throw new Oxygen_Process_Exception_ProcessFailedException($this->process);
            }
        }

        if ($this->process->isRunning()) {
            $output = $this->process->getIncrementalOutput();
            $this->process->clearOutput();

            if (strlen($output) < Oxygen_Process_Pipes_PipesInterface::CHUNK_SIZE) {
                // Don't hog the processor while waiting for incremental process output.
                usleep(100000);
            }

            // The stream will be read again because we're returning a string.
            return (string) $output;
        } else {
            if (!$this->process->isSuccessful()) {
                throw new Oxygen_Process_Exception_ProcessFailedException($this->process);
            }

            $output = $this->process->getIncrementalOutput();
            $this->process->clearOutput();

            // The process has finished and is successful. This part will probably get run twice,
            // first time we'll return final output, second time we'll return 'false' and break the loop.
            return empty($output) ? false : $output;
        }
    }
}
