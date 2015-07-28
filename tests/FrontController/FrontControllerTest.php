<?php

namespace Aveiv\JsonRpc\Tests\FrontController;

use Aveiv\JsonRpc\Exception\JsonRpcException;
use Aveiv\JsonRpc\FrontController\FrontController;
use Aveiv\JsonRpc\MethodCaller\MethodCallerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class FrontControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $methodCaller;

    /**
     * @var FrontController
     */
    private $frontController;

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testInvalidJson()
    {
        $resultJson = $this->frontController->handle('invalid_json');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32700,"message":"Parse error","data":null},"id":null}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testInvalidJsonRpcVersion()
    {
        $resultJson = $this->frontController->handle('{"method":"method_name"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');

        $resultJson = $this->frontController->handle('{"jsonrpc":"2.1","method":"method_name"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testInvalidMethod()
    {
        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');

        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":["not_string"]}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testInvalidParams()
    {
        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name","params":"invalid_type"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testInvalidId()
    {
        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name","id":["invalid_type"]}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":-32600,"message":"Invalid Request","data":null},"id":null}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::errorResponse
     */
    public function testJsonRpcExceptionInMethod()
    {
        $this->methodCaller
            ->expects($this->once())
            ->method('call')
            ->with('method_name', [], 999)
            ->willThrowException(new JsonRpcException('Test message', 999, ['key' => 'value']));
        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name","id":999}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":null,"error":{"code":999,"message":"Test message","data":{"key":"value"}},"id":999}');
    }

    /**
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::handle
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::normalizeJson
     * @covers \Aveiv\JsonRpc\FrontController\FrontController::successResponse
     */
    public function testValidRequest()
    {
        $this->methodCaller
            ->expects($this->exactly(3))
            ->method('call')
            ->withConsecutive(
                ['method_name'],
                ['method_name', ['test_param'], 999],
                ['method_name']
            )
            ->willReturn(['key' => 'value']);

        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":{"key": "value"},"id":null}');

        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name","params":["test_param"],"id":999}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":{"key": "value"},"id":999}');

        $serializationContext = $this->getMock(SerializationContext::class);
        $serializer = $this->getMock(SerializerInterface::class);
        $serializer->expects($this->once())
            ->method('serialize')
            ->with(['key' => 'value'], 'json', $serializationContext)
            ->willReturn('{"key":"value"}');
        /** @noinspection PhpParamsInspection */
        $this->frontController->setSerializer($serializer);
        /** @noinspection PhpParamsInspection */
        $this->frontController->setSerializationContext($serializationContext);
        $resultJson = $this->frontController->handle('{"jsonrpc":"2.0","method":"method_name"}');
        $this->assertJsonStringEqualsJsonString($resultJson, '{"jsonrpc":"2.0","result":{"key": "value"},"id":null}');
    }

    protected function setUp()
    {
        parent::setUp();
        $this->methodCaller = $this->getMock(MethodCallerInterface::class);
        /** @noinspection PhpParamsInspection */
        $this->frontController = new FrontController($this->methodCaller);
    }
}
