<?php

namespace Wysiwyg\CookieHandling\Domain\Http\Middleware;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieConsentMiddleware implements MiddlewareInterface
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    /**
     * @Flow\InjectConfiguration(path="consentCookieName")
     * @var string
     */
    protected $consentCookieName;

    /**
     * Loads the consent cookie, which has been set via the frontend.
     *
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     * @return ResponseInterface
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestPath = $request->getUri()->getPath();

        // Exclude backend
        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@user') !== false) {
            return $handler->handle($request);
        }

        $cookies = $request->getCookieParams();

        if (array_key_exists($this->consentCookieName, $cookies)) {
            $consentCookie = new Cookie($this->consentCookieName, $cookies[$this->consentCookieName]);
            $this->cookieConsentService->setConsentCookie($consentCookie);
        }

        return $handler->handle($request);
    }
}
