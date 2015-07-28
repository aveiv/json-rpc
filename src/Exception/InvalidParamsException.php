<?php

namespace Aveiv\JsonRpc\Exception;

/**
 * @codeCoverageIgnore
 */
class InvalidParamsException extends JsonRpcException
{
    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = 'Invalid params', $code = -32602, $data = null)
    {
        parent::__construct($message, $code, $data);
    }
}
