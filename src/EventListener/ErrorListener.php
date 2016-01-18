<?php

class Oxygen_EventListener_ErrorListener
{
    /**
     * @var string|null
     */
    private $reservedMemory;

    /**
     * @var int
     */
    private $reservedMemorySize;

    /**
     * @var Oxygen_Http_Request
     */
    private $request;

    /**
     * @var string
     */
    private $responseId;

    /**
     * @var array
     */
    private $errorLog = array();

    private static $fatalErrors = array(E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR);

    /**
     * @param int $reservedMemorySize In kilobytes.
     */
    public function __construct($reservedMemorySize)
    {
        $this->reservedMemorySize = $reservedMemorySize;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $this->request = $event->getRequest();

        // Kind of like str_rot8, for hexadecimal strings.
        $this->responseId = strtr($event->getRequestData()->oxygenRequestId, 'abcdef0123456789', '23456789abcdef01');

        set_exception_handler(array($this, 'handleException'));
        set_error_handler(array($this, 'handleError'));
        register_shutdown_function(array($this, 'handleFatalError'));

        $this->reservedMemory = str_repeat(' ', 1024 * $this->reservedMemorySize);
    }

    public function onException(Oxygen_Event_ExceptionEvent $event)
    {
        $exceptionData = $this->getExceptionData($event->getException());

        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $this->responseId,
            'exception'        => $exceptionData,
            'errorLog'         => $this->errorLog,
        ));

        $event->setResponse($response);
    }

    /**
     * @param Exception|Throwable $exception
     */
    public function handleException($exception)
    {
        $exceptionData = $this->getExceptionData($exception);

        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $this->responseId,
            'exception'        => $exceptionData,
            'errorLog'         => $this->errorLog,
        ));

        $response->send();
        exit;
    }

    /**
     * @param Exception|Error $exception
     *
     * @return array
     */
    private function getExceptionData($exception)
    {
        $exceptionData = array(
            'class'       => get_class($exception),
            'message'     => $exception->getMessage(),
            'code'        => (int)$exception->getCode(),
            'type'        => null,
            'previous'    => null,
            'file'        => $exception->getFile(),
            'line'        => $exception->getLine(),
            'traceString' => $exception->getTraceAsString(),
            'context'     => array(),
        );

        if ($exception instanceof Oxygen_Exception) {
            $exceptionData['type'] = $exception->getType();

            if ($exception->getPreviousException()) {
                $exceptionData['previous'] = $this->getExceptionData($exception->getPreviousException());
            }
            $exceptionData['context'] = $exception->getContext();
        }

        return $exceptionData;
    }

    /**
     * @internal
     *
     * @param int    $code
     * @param string $message
     * @param string $file
     * @param int    $line
     */
    public function handleError($code, $message, $file = 'Unknown', $line = 0)
    {
        // The context (fifth argument, array) can be recursive, so don't save it.
        $this->errorLog[] = array(
            'time'    => microtime(true),
            'code'    => $this->codeToString($code),
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
        );
    }

    /**
     * @internal
     */
    public function handleFatalError()
    {
        $this->reservedMemory = null;

        $lastError = error_get_last();
        if (!$lastError || !in_array($lastError['type'], self::$fatalErrors)) {
            return;
        }

        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $this->responseId,
            'exception'        => $this->getFatalErrorData($lastError),
            'errorLog'         => $this->errorLog,
        ));

        $response->send();
        exit;
    }

    /**
     * @param array $error
     *
     * @return array
     */
    private function getFatalErrorData(array $error)
    {
        return array(
            'class'       => 'Oxygen_Exception',
            'message'     => $error['message'],
            'file'        => $error['file'],
            'line'        => $error['line'],
            'context'     => array(),
            'code'        => Oxygen_Exception::FATAL_ERROR,
            'type'        => 'FATAL_ERROR',
            'traceString' => '',
            'previous'    => null,
        );
    }

    /**
     * @param string $code
     *
     * @return string
     */
    private static function codeToString($code)
    {
        switch ($code) {
            case E_ERROR:
                return 'E_ERROR';
            case E_WARNING:
                return 'E_WARNING';
            case E_PARSE:
                return 'E_PARSE';
            case E_NOTICE:
                return 'E_NOTICE';
            case E_CORE_ERROR:
                return 'E_CORE_ERROR';
            case E_CORE_WARNING:
                return 'E_CORE_WARNING';
            case E_COMPILE_ERROR:
                return 'E_COMPILE_ERROR';
            case E_COMPILE_WARNING:
                return 'E_COMPILE_WARNING';
            case E_USER_ERROR:
                return 'E_USER_ERROR';
            case E_USER_WARNING:
                return 'E_USER_WARNING';
            case E_USER_NOTICE:
                return 'E_USER_NOTICE';
            case E_STRICT:
                return 'E_STRICT';
            case E_RECOVERABLE_ERROR:
                return 'E_RECOVERABLE_ERROR';
            case E_DEPRECATED:
                return 'E_DEPRECATED';
            case E_USER_DEPRECATED:
                return 'E_USER_DEPRECATED';
        }

        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            switch ($code) {
                case E_DEPRECATED:
                    return 'E_DEPRECATED';
                case E_USER_DEPRECATED:
                    return 'E_USER_DEPRECATED';
            }
        }

        return 'Unknown PHP error';
    }
}
