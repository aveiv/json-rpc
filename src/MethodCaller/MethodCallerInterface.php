<?php

namespace Aveiv\JsonRpc\MethodCaller;

use Aveiv\JsonRpc\Exception\InvalidParamsException;
use Aveiv\JsonRpc\Exception\MethodNotFoundException;

interface MethodCallerInterface
{
    /**
     * @param string $method
     * @param array $params
     * @param null|string|int|float $id
     * @return mixed
     * @throws MethodNotFoundException
     * @throws InvalidParamsException
     */
    public function call($method, array $params = [], $id = null);
}
