<?php
declare(strict_types=1);

namespace Karolak\RequestHandler;

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RequestHandler implements RequestHandlerInterface
{
    final public const DEFAULT_RESPONSE_CODE = 200;

    /** @var ResponseFactoryInterface */
    private ResponseFactoryInterface $responseFactory;

    /** @var MiddlewareInterface[] */
    private array $middlewares;

    /**
     * @param ResponseFactoryInterface $responseFactory
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(
        ResponseFactoryInterface $responseFactory,
        array $middlewares = []
    )
    {
        $this->responseFactory = $responseFactory;
        $this->middlewares = $middlewares;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $middleware = $this->middlewares[0] ?? false;
        array_shift($this->middlewares);

        return $middleware ?
            $middleware->process($request, new self($this->responseFactory, $this->middlewares))
            :
            $this->responseFactory->createResponse(self::DEFAULT_RESPONSE_CODE);
    }
}
