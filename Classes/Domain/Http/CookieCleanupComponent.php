<?php

namespace Wysiwyg\CookieHandling\Domain\Http;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\ComponentContext as ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieCleanupComponent implements ComponentInterface
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
     * @var ServerRequestInterface
     */
    protected $request;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * Removes cookies which shouldn't be set.
     *
     * This function iterates through all cookies and removes unaccepted cookies.
     *
     * @param ComponentContext $componentContext
     */
    public function handle(ComponentContext $componentContext)
    {
        $this->request = $componentContext->getHttpRequest();
        $this->response = $componentContext->getHttpResponse();

        $requestPath = $this->request->getUri()->getPath();

        // Exclude backend
        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@user') !== false) {
            return;
        }

        $this->removeUnacceptedCookiesFromRequest();
    }

    /**
     * Removes the cookies from the request, which haven't been accepted if dryRun is false.
     * All cookies are logged.
     */
    private function removeUnacceptedCookiesFromRequest()
    {
        $cookiesFromRequest = $this->request->getCookieParams();

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
