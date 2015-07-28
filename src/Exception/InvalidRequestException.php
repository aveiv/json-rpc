<?php

namespace Aveiv\JsonRpc\Exception;

/**
 * @codeCoverageIgnore
 */
class InvalidRequestException extends JsonRpcException
{
    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = 'Invalid Request', $code = -32600, $data = null)
    {
        parent::__construct($message, $code, $data);
    }
}
