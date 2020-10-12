<?php

namespace Wysiwyg\CookieHandling\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Neos\Flow\Persistence\PersistenceManagerInterface;
use Wysiwyg\CookieHandling\Domain\Model\LoggedCookie;
use Wysiwyg\CookieHandling\Domain\Repository\LoggedCookieRepository;

/**
 * @Flow\Scope("singleton")
 */
class CookieConsentService
{
    /**
     * @Flow\InjectConfiguration(path="cookieGroups")
     * @var array
     */
    protected $cookieGroups;

    /**
     * JSON decoded cookie consent settings from frontend interaction.
     * @var array
     */
    protected $acceptedCookies = [];

    /**
     * @var Cookie
     */
    protected $consentCookie;

    /**
     * @Flow\Inject
     * @var LoggedCookieRepository
     */
    protected $loggedCookieRepository;

    /**
     * @Flow\Inject
     * @var PersistenceManagerInterface
     */
    protected $persistenceManager;

    /**
     * @var array
     */
    protected $cookieJar;

    /**
     * This function checks if a cookie has been accepted and then adds it to the cookieJar.
     *
     * @param Cookie $cookie
     * @return boolean
     */
    public function tryAddCookie(Cookie $cookie)
    {
        if ($this->cookieIsAccepted($cookie->getName())) {
            $this->cookieJar[$cookie->getName()] = $cookie;

            return true;
        }

        return false;
    }

    /**
     * Directly add a cookie to CookieJar without check.
     *
     * @param Cookie $cookie
     * @return boolean
     */
    public function forceAddCookie(Cookie $cookie)
    {
        $this->cookieJar[$cookie->getName()] = $cookie;
        return true;
    }

    /**
     * Checks if the user did accept the given cookie group.
     *
     * @param string $cookieGroupName
     * @return bool
     */
    public function cookieGroupIsAccepted(string $cookieGroupName)
    {
        if (is_array($this->acceptedCookies) && !array_key_exists($cookieGroupName, $this->acceptedCookies)) {
            return false;
        }

        return $this->acceptedCookies[$cookieGroupName]['accepted'] ?? false;
    }

    /**
     * Find a group that a cookie belongs to.
     *
     * If a cookie has been found in a cookieGroup, the associated group will be returned as string.
     * If no group has been found <null> will be returned.
     *
     * @param $cookieName
     * @return string|null
     */
    public function getGroupForCookie($cookieName)
    {
        if (!is_array($this->cookieGroups)) {
            return null;
        }

        $groupForCookie = null;
        foreach ($this->cookieGroups as $cookieGroupKey => $cookieGroupValue) {
            $cookies = (array)$cookieGroupValue['cookies'];

            array_walk($cookies, function ($cookieSettings) use ($cookieName, $cookieGroupKey, &$groupForCookie) {
                if (array_key_exists('subCookies', $cookieSettings) && in_array($cookieName, $cookieSettings['subCookies'])) {
                    $groupForCookie = $cookieSettings['cookieName'];
                }

                if ($cookieSettings['cookieName'] == $cookieName) {
                    $groupForCookie = $cookieGroupKey;
                }
            });
        }

        return $groupForCookie;
    }

    /**
     * Checks if a cookie has been accepted.
     *
     * @param string $cookieName
     * @return bool
     */
    public function cookieIsAccepted(string $cookieName)
    {
        $result = array_filter($this->acceptedCookies, function ($element) use ($cookieName) {
            if (in_array($cookieName, $element['cookies'])) {
                return true;
            }

            $possibleSubGroup = $this->getGroupForCookie($cookieName);

            return in_array($possibleSubGroup, $element['cookies']);
        });

        return !empty($result);
    }

    /**
     * Removes a cookie by adding it to the cookieJar as an expired cookie.
     *
     * @param Cookie $cookie
     */
    public function removeCookie(Cookie $cookie)
    {
        $cookie->expire();
        $this->cookieJar[$cookie->getName()] = $cookie;
    }

    /**
     * @param Cookie $consentCookie
     */
    public function setConsentCookie(Cookie $consentCookie)
    {
        $this->consentCookie = $consentCookie;
        $this->acceptedCookies = json_decode($consentCookie->getValue(), true);
    }

    /**
     * @return Cookie
     */
    public function getConsentCookie()
    {
        return $this->consentCookie;
    }

    /**
     * Logs a cookie which has been found in the request.
     *
     * @param Cookie $cookie
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function logCookie(Cookie $cookie)
    {
        /** @var LoggedCookie $foundCookie */
        $foundCookie = $this->loggedCookieRepository->findOneByCookieName($cookie->getName());

        if ($foundCookie) {
            $foundCookie->setCounter($foundCookie->getCounter() + 1);
            $this->loggedCookieRepository->update($foundCookie);
        } else {
            $loggedCookie = new LoggedCookie($cookie);
            $this->loggedCookieRepository->add($loggedCookie);
        }

        $this->persistenceManager->persistAll();
    }

    /**
     * Returns an array of all accepted and expired cookies.
     *
     * This array consists of all cookies which have been added with "tryAddCookie()" or
     * "removeCookie()".
     *
     * @return array
     */
    public function getCookieJar()
    {
        return $this->cookieJar;
    }

    /**
     * Function to check if a cookie is already in the cookieJar, to prevent override.
     *
     * @param $cookieName
     * @return bool
     */
    public function cookieIsInJar($cookieName)
    {
        return is_array($this->cookieJar) && array_key_exists($cookieName, $this->cookieJar);
    }


    /**
     * Checks if a user has a cookie consent.
     *
     * return bool
     */
    public function userHasCookieConsent()
    {
        return !is_null($this->consentCookie);
    }

    /**
     * Returns a sha1 hash from the cookie settings.
     *
     * @return string
     */
    public function getCookieSettingsHash()
    {
        return sha1(json_encode($this->cookieGroups));
    }
}
