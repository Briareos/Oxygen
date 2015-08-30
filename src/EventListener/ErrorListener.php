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
     * @param int $reservedMemorySize
     */
    public function __construct($reservedMemorySize)
    {
        $this->reservedMemorySize = $reservedMemorySize;
    }

    public function onMasterRequest(Oxygen_Event_MasterRequestEvent $event)
    {
        $this->request = $event->getRequest();

        $this->responseId = str_rot13($event->getRequestData()->oxygenRequestId);

        set_error_handler(array($this, 'handleError'));
        register_shutdown_function(array($this, 'handleFatalError'));

        $this->reservedMemory = str_repeat(' ', 1024 * $this->reservedMemorySize);
    }

    public function onException(Oxygen_Event_ExceptionEvent $event)
    {
        $exception = $event->getException();

        $exceptionData = $this->getExceptionData($exception, $event->getRequest()->isAuthenticated());


        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $this->responseId,
            'exception'        => $exceptionData,
            'errorLog'         => $this->errorLog,
        ));

        $event->setResponse($response);
    }

    private function getExceptionData(Exception $exception, $verbose)
    {
        $exceptionData = array(
            'class'   => get_class($exception),
            'message' => $exception->getMessage(),
            'code'    => $exception->getCode(),
        );

        if ($exception instanceof Oxygen_Exception) {
            $exceptionData['type'] = $exception->getType();

            if ($exception->getPreviousException()) {
                $exceptionData['previous'] = $this->getExceptionData($exception->getPreviousException(), $verbose);
            }

            if ($verbose) {
                $exceptionData['context'] = $exception->getContext();
            }
        }

        if ($verbose) {
            $exceptionData['file']        = $exception->getFile();
            $exceptionData['line']        = $exception->getLine();
            $exceptionData['traceString'] = $exception->getTraceAsString();
        }

        return $exceptionData;
    }

    /**
     * @internal
     */
    public function handleError(/** @noinspection PhpDocSignatureInspection */
        $code, $message, $file = 'Unknown', $line = 0, $context = array())
    {
        $this->errorLog[] = array(
            'code'    => $code,
            'message' => $message,
            'file'    => $file,
            'line'    => $line,
            'context' => $context,
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

        $exception = new Oxygen_Exception(Oxygen_Exception::FATAL_ERROR, $lastError);

        $response = new Oxygen_Http_JsonResponse(array(
            'oxygenResponseId' => $this->responseId,
            'exception'        => $this->getExceptionData($exception, $this->request->isAuthenticated()),
            'errorLog'         => $this->errorLog,
        ));

        $response->send();
        exit;
    }

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
