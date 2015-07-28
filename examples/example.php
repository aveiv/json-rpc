<?php

require __DIR__ . '/../vendor/autoload.php';

use Aveiv\JsonRpc\Exception\InvalidParamsException;
use Aveiv\JsonRpc\Exception\JsonRpcException;
use Aveiv\JsonRpc\Exception\MethodNotFoundException;
use Aveiv\JsonRpc\FrontController\FrontController;
use Aveiv\JsonRpc\MethodCaller\MethodCallerInterface;

class MethodCaller implements MethodCallerInterface
{
    /**
     * @param string $method
     * @param array $params
     * @param null|string|int|float $id
     * @return mixed
     * @throws InvalidParamsException
     * @throws JsonRpcException
     * @throws MethodNotFoundException
     */
    public function call($method, array $params = [], $id = null)
    {
        // your method call implementation here

        // for example:
        switch ($method) {
            case 'hello':
                return 'hello world';
            case 'sum':
                if (!isset($params['a']) || !isset($params['b'])) {
                    throw new InvalidParamsException();
                }
                $a = intval($params['a']);
                $b = intval($params['b']);
                return intval($a) + intval($b);
            case 'error':
                throw new JsonRpcException('Your error message', 999, 'Error data...');
            default:
                throw new MethodNotFoundException();
        }
    }
}

$methodCaller = new MethodCaller();
$frontController = new FrontController($methodCaller);
// if you need use jms/serializer for serialize result:
//$frontController = new FrontController($methodCaller, \JMS\Serializer\SerializerBuilder::create()->build());

$results = [];
$results[] = $frontController->handle('{"jsonrpc":"2.0","method":"hello","id":1}');
$results[] = $frontController->handle('{"jsonrpc":"2.0","method":"sum","id":2}');
$results[] = $frontController->handle('{"jsonrpc":"2.0","method":"sum","params":{"a":10,"b":20},"id":3}');
$results[] = $frontController->handle('{"jsonrpc":"2.0","method":"error","id":4}');
$results[] = $frontController->handle('{"jsonrpc":"2.0","method":"not_existing_method","id":5}');

print_r($results);

// prints:
//
// Array
// (
//     [0] => {"jsonrpc":"2.0","result":"hello world","id":1}
//     [1] => {"jsonrpc":"2.0","result":null,"error":{"code":-32602,"message":"Invalid params","data":null},"id":2}
//     [2] => {"jsonrpc":"2.0","result":30,"id":3}
//     [3] => {"jsonrpc":"2.0","result":null,"error":{"code":999,"message":"Your error message","data":"Error data..."},"id":4}
//     [4] => {"jsonrpc":"2.0","result":null,"error":{"code":-32601,"message":"Method not found","data":null},"id":5}
// )
