<?php

namespace Aveiv\JsonRpc\Exception;

/**
 * @codeCoverageIgnore
 */
class ParseErrorException extends JsonRpcException
{
    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = 'Parse error', $code = -32700, $data = null)
    {
        parent::__construct($message, $code, $data);
    }
}
