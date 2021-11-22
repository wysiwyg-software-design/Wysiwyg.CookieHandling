<?php

namespace Wysiwyg\CookieHandling\Domain\Http\Middleware;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;
use Wysiwyg\CookieHandling\Domain\Service\CookieUpdateService;

class CookieLifetimeUpdateMiddleware implements MiddlewareInterface
{

    /**
     * @var CookieUpdateService
     * @Flow\Inject
     */
    protected $cookieUpdateService;

    /**
     * @var CookieConsentService
     * @Flow\Inject
     */
    protected $cookieConsentService;


    /**
     * Reduces lifetime of cookies which should be updated.
     *
     * This function iterates through all cookies in the Request and checks
     * if they are outdated by checking the cookie's hash settings.
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

        foreach ($request->getCookieParams() as $cookieName => $cookieValue) {
            $cookie = new Cookie($cookieName, $cookieValue);
            $cookieIsOutdated = $this->cookieUpdateService->cookieIsOutdated($cookieName);
            if (!$this->cookieConsentService->cookieIsInJar($cookieName) && $cookieIsOutdated) {
                $updatedCookie = $this->cookieUpdateService->updateLifeTimeForCookie($cookie);
                $this->cookieConsentService->tryAddCookie($updatedCookie);
            }
        }

        return $handler->handle($request);
    }

}
