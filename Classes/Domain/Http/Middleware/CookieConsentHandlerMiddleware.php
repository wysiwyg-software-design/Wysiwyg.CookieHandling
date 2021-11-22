<?php

namespace Wysiwyg\CookieHandling\Domain\Http\Middleware;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieConsentHandlerMiddleware implements MiddlewareInterface
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    /**
     * Adds allowed and to be deleted cookies to the response.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Exclude backend
        $requestPath = $request->getUri()->getPath();
        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@user') !== false) {
            return $handler->handle($request);
        }

        return $this->handleCookiesInResponse($handler->handle($request));
    }

    /**
     * This functions adds all cookies from the cookieJar to the response.
     * These cookies can be accepted cookies or to be deleted cookies.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function handleCookiesInResponse(ResponseInterface $response): ResponseInterface
    {
        $cookieJar = $this->cookieConsentService->getCookieJar();

        if (empty($cookieJar)) {
            return $response;
        }

        /** @var Cookie $cookieInJar */
        foreach ($cookieJar as $cookieInJar) {
            $response = $response->withAddedHeader('Set-Cookie', (string)$cookieInJar);
        }

        return $response;
    }
}
