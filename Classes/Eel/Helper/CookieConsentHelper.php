<?php

namespace Wysiwyg\CookieHandling\Eel\Helper;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Flow\Annotations as Flow;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieConsentHelper implements ProtectedContextAwareInterface
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    /**
     * Checks if a cookie has been accepted.
     *
     * @param $cookieName
     *
     * @return bool
     */
    public function cookieIsAccepted($cookieName)
    {
        return $this->cookieConsentService->cookieIsAccepted($cookieName);
    }

    /**
     * Checks if the user did accept the given cookie group.
     *
     * @param $cookieGroupName
     *
     * @return bool
     */
    public function cookieGroupIsAccepted($cookieGroupName)
    {
        return $this->cookieConsentService->cookieGroupIsAccepted($cookieGroupName);
    }

    /**
     * Returns a unique string of current cookie consent.
     * This changes when the user modifies the cookie selection.
     *
     * @return string
     */
    public function getConsentCookieHash()
    {
        $consentCookie = $this->cookieConsentService->getConsentCookie();
        if ($consentCookie !== null) {
            return $this->cookieConsentService->getCookieSettingsHash();
        }

        return null;
    }

    /**
     * Checks if a user has a cookie consent.
     *
     * @return bool
     */
    public function userHasCookieConsent()
    {
        return $this->cookieConsentService->userHasCookieConsent();
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
