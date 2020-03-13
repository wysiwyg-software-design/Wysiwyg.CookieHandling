<?php

namespace Wysiwyg\CookieHandling\Domain\Http;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\ComponentContext as ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Neos\Flow\Http\Cookie;
use Neos\Flow\Http\Request;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieConsentComponent implements ComponentInterface
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
     * @param ComponentContext $componentContext
     */
    public function handle(ComponentContext $componentContext)
    {
        $request = $componentContext->getHttpRequest();

        $requestPath = $request->getUri()->getPath();

        // Exclude backend
        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@user') !== false) {
            return;
        }

        // Non PSR-7 handling
        if (method_exists($request, 'hasCookie') && method_exists($request, 'getCookie') && $request->hasCookie($this->consentCookieName)) {
            $this->cookieConsentService->setConsentCookie($request->getCookie($this->consentCookieName));

            return;
        }

        // PSR-7 handling
        $cookies = $request->getCookieParams();

        if (array_key_exists($this->consentCookieName, $cookies)) {
            $consentCookie = new Cookie($this->consentCookieName, $cookies[$this->consentCookieName]);
            $this->cookieConsentService->setConsentCookie($consentCookie);
        }
    }
}
