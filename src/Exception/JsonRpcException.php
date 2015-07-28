<?php

namespace Aveiv\JsonRpc\Exception;

use Exception;

/**
 * @codeCoverageIgnore
 */
class JsonRpcException extends \Exception
{
    /**
     * @var mixed
     */
    private $data;

    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     */
    public function __construct($message = "", $code = 0, $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
}
