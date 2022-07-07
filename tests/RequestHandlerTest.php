<?php
declare(strict_types=1);

namespace Karolak\RequestHandler\Tests;

use Karolak\RequestHandler\RequestHandler;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RequestHandlerTest extends TestCase
{
    /**
     * @return void
     */
    public function test_should_return_default_response_code_when_no_middlewares(): void
    {
        // Arrange
        $defaultResponseCode = 200;
        $request = $this->createMock(ServerRequestInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);

        $responseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willReturnCallback(
                function (int $status) {
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($status);

                    return $response;
                });

        // Act
        $result = (new RequestHandler($responseFactory, []))->handle($request);

        // Assert
        $this->assertEquals($defaultResponseCode, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_middleware_should_be_able_to_modify_response(): void
    {
        // Arrange
        $originalResponseCode = 200;
        $modifiedResponseCode = 404;

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);

        $response
            ->method('getStatusCode')
            ->willReturn($originalResponseCode);
        $response
            ->method('withStatus')
            ->willReturnCallback(
                function (int $status, string $reasonPhrase = '') {
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($status);

                    return $response;
                });

        $responseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willReturn($response);

        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($modifiedResponseCode) {
                    $response = $handler->handle($request);

                    return $response->withStatus($modifiedResponseCode);
                });

        // Act
        $result = (new RequestHandler($responseFactory, [$middleware1]))->handle($request);

        // Assert
        $this->assertEquals($modifiedResponseCode, $result->getStatusCode());
    }

    /**
     * @return void
     */
    public function test_should_process_every_middleware(): void
    {
        // Arrange
        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $responseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willReturn($response);

        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                });

        $middleware2
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) {
                    return $handler->handle($request);
                });

        // Act
        (new RequestHandler($responseFactory, [
            $middleware1,
            $middleware2
        ]))->handle($request);
    }

    /**
     * @return void
     */
    public function test_first_middleware_should_be_executed_last(): void
    {
        // Arrange
        $startResponseCode = 200;
        $firstResponseCode = 201;
        $secondResponseCode = 202;

        $request = $this->createMock(ServerRequestInterface::class);
        $response = $this->createMock(ResponseInterface::class);
        $responseFactory = $this->createMock(ResponseFactoryInterface::class);
        $middleware1 = $this->createMock(MiddlewareInterface::class);
        $middleware2 = $this->createMock(MiddlewareInterface::class);

        $response
            ->method('getStatusCode')
            ->willReturn($startResponseCode);

        $responseFactory
            ->expects($this->once())
            ->method('createResponse')
            ->willReturn($response);

        $middleware1
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($firstResponseCode) {
                    $handler->handle($request);
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($firstResponseCode);

                    return $response;
                });

        $middleware2
            ->expects($this->once())
            ->method('process')
            ->willReturnCallback(
                function (ServerRequestInterface $request, RequestHandlerInterface $handler) use ($secondResponseCode) {
                    $handler->handle($request);
                    $response = $this->createMock(ResponseInterface::class);
                    $response->method('getStatusCode')->willReturn($secondResponseCode);

                    return $response;
                });

        // Act
        $result = (new RequestHandler($responseFactory, [
            $middleware1,
            $middleware2
        ]))->handle($request);

        // Assert
        $this->assertEquals($firstResponseCode, $result->getStatusCode());
    }
}
