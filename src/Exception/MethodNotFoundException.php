<?php

namespace Aveiv\JsonRpc\Exception;

/**
 * @codeCoverageIgnore
 */
class MethodNotFoundException extends JsonRpcException
{
    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = 'Method not found', $code = -32601, $data = null)
    {
        parent::__construct($message, $code, $data);
    }
}
