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
     * Removes cookies which should not be set.
     *
     * This function iterates through all configured cookies and deactivates either
     * a whole cookie group or a single cookie.
     * A whole group will be disabled, if no cookie was found for the given group.
     * A single cookie will be disabled if the cookieName was not found in a cookieGroup.
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