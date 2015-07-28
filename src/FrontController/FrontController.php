<?php

namespace Aveiv\JsonRpc\FrontController;

use Aveiv\JsonRpc\Exception\InvalidRequestException;
use Aveiv\JsonRpc\Exception\JsonRpcException;
use Aveiv\JsonRpc\Exception\ParseErrorException;
use Aveiv\JsonRpc\MethodCaller\MethodCallerInterface;
use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class FrontController
{
    /**
     * @var MethodCallerInterface
     */
    private $methodCaller;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var SerializationContext
     */
    private $serializationContext;

    /**
     * @codeCoverageIgnore
     *
     * @param MethodCallerInterface $methodCaller
     * @param SerializerInterface|null $serializer
     * @param SerializationContext $serializationContext
     */
    public function __construct(
        MethodCallerInterface $methodCaller,
        SerializerInterface $serializer = null,
        SerializationContext $serializationContext = null)
    {
        $this->methodCaller = $methodCaller;
        $this->serializer = $serializer;
        $this->serializationContext = $serializationContext;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return MethodCallerInterface
     */
    public function getMethodCaller()
    {
        return $this->methodCaller;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param MethodCallerInterface $methodCaller
     */
    public function setMethodCaller(MethodCallerInterface $methodCaller)
    {
        $this->methodCaller = $methodCaller;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return SerializerInterface
     */
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param SerializerInterface $serializer
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @codeCoverageIgnore
     *
     * @return SerializationContext
     */
    public function getSerializationContext()
    {
        return $this->serializationContext;
    }

    /**
     * @codeCoverageIgnore
     *
     * @param SerializationContext $serializationContext
     */
    public function setSerializationContext(SerializationContext $serializationContext)
    {
        $this->serializationContext = $serializationContext;
    }

    /**
     * @param string $json
     * @return string
     */
    public function handle($json)
    {
        try {
            $normalizedJson = $this->normalizeJson($json);
        } catch (JsonRpcException $e) {
            return $this->errorResponse($e->getCode(), $e->getMessage(), $e->getData());
        }
        try {
            $result = $this->methodCaller->call($normalizedJson['method'], $normalizedJson['params'], $normalizedJson['id']);
        } catch (JsonRpcException $e) {
            return $this->errorResponse($e->getCode(), $e->getMessage(), $e->getData(), $normalizedJson['id']);
        }
        return $this->successResponse($result, $normalizedJson['id']);
    }

    /**
     * @param string $json
     * @return array
     * @throws InvalidRequestException
     * @throws ParseErrorException
     */
    private function normalizeJson($json)
    {
        $normalizedJson = json_decode($json, true);
        if (!$normalizedJson) {
            throw new ParseErrorException();
        }
        if (!isset($normalizedJson['jsonrpc']) || $normalizedJson['jsonrpc'] != '2.0') {
            throw new InvalidRequestException();
        }
        if (!isset($normalizedJson['method']) || !is_string($normalizedJson['method'])) {
            throw new InvalidRequestException();
        }
        if (isset($normalizedJson['params'])) {
            if (!is_array($normalizedJson['params'])) {
                throw new InvalidRequestException();
            }
        } else {
            $normalizedJson['params'] = [];
        }
        if (isset($normalizedJson['id'])) {
            if (!is_null($normalizedJson['id']) && !is_string($normalizedJson['id']) && !is_numeric($normalizedJson['id'])) {
                throw new InvalidRequestException();
            }
        } else {
            $normalizedJson['id'] = null;
        }
        return $normalizedJson;
    }

    /**
     * @param string $message
     * @param int $code
     * @param mixed $data
     * @param null|string|int|float $id
     * @return string
     */
    private function errorResponse($code, $message, $data, $id = null)
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'result' => null,
            'error' => [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ],
            'id' => $id,
        ]);
    }

    /**
     * @param mixed $result
     * @param null|string|int|float $id
     * @return string
     */
    private function successResponse($result, $id)
    {
        if ($this->serializer) {
            $result = $this->serializer->serialize($result, 'json', $this->serializationContext);
            $result = json_decode($result, true);
        }
        return json_encode([
            'jsonrpc' => '2.0',
            'result' => $result,
            'id' => $id,
        ]);
    }
}
