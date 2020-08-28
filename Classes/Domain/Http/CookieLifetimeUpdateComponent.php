<?php

namespace Wysiwyg\CookieHandling\Domain\Http;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\ComponentContext as ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;
use Wysiwyg\CookieHandling\Domain\Service\CookieUpdateService;

/**
 * Class CookieCleanupComponent
 * @package Wysiwyg\CookieHandling\Domain\Http
 */
class CookieLifetimeUpdateComponent implements ComponentInterface
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
     * @param ComponentContext $componentContext
     */
    public function handle(ComponentContext $componentContext)
    {
        $request = $componentContext->getHttpRequest();

        if (!method_exists($request, 'getCookies')) {
            // @todo we should find a possible way to update cookies in PSR-7 aswell.
            return;
        }

        $requestPath = $request->getUri()->getPath();

        // Exclude backend
        if (strpos($requestPath, '/neos') === 0 || strpos($requestPath, '@user') !== false) {
            return;
        }

        /**
         * @var Cookie $cookie
         */
        foreach ($request->getCookies() as $cookie) {
            $cookieIsOutdated = $this->cookieUpdateService->cookieIsOutdated($cookie->getName());
            if ($cookieIsOutdated) {
                $updatedCookie = $this->cookieUpdateService->updateLifeTimeForCookie($cookie);
                $this->cookieConsentService->tryAddCookie($updatedCookie);
            }
        }

    }

}
