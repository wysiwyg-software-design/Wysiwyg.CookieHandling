<?php

namespace Wysiwyg\CookieHandling\Domain\Http\Middleware;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieCleanupMiddleware implements MiddlewareInterface
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    /**
     * @Flow\InjectConfiguration(path="cleanUp.dryrun")
     * @var bool
     */
    protected $dryRun;

    /**
     * Removes the cookies from the request, which haven't been accepted.
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
            return  $handler->handle($request);
        }

        $response = $handler->handle($request);

        $this->removeUnacceptedCookiesFromRequest($request);

        return $response;
    }

    /**
     * This function iterates through all cookies and removes unaccepted cookies if dryRun is false.
     * All cookies are logged.
     *
     * @param ServerRequestInterface $request
     */
    private function removeUnacceptedCookiesFromRequest(ServerRequestInterface $request): void
    {
        $cookiesFromRequest = $request->getCookieParams();

        foreach (array_keys($cookiesFromRequest) as $cookieName) {
            if ($this->cookieConsentService->cookieIsAccepted($cookieName)) {
                continue;
            }

            try {
                $cookieFromRequest = new Cookie($cookieName);
                $this->cookieConsentService->logCookie($cookieFromRequest);

                if (!$this->dryRun) {
                    $this->cookieConsentService->removeCookie($cookieFromRequest);
                }
            } catch (\Exception $e) {

            }
        }
    }
}
