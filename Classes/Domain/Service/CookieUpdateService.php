<?php

namespace Wysiwyg\CookieHandling\Domain\Service;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class CookieConsentService
 * @Flow\Scope("singleton")
 */
class CookieUpdateService
{

    /**
     * @Flow\InjectConfiguration(path="cookieGroups")
     * @var array
     */
    protected $cookieGroups;

    /**
     * Get all configured cookies which have a previousCookieSettingHash configured.
     *
     * @return array
     */
    public function getCookiesWithPreviousVersion()
    {
        if (!is_array($this->cookieGroups)) {
            return null;
        }

        $cookiesWithPreviousVersions = [];
        foreach ($this->cookieGroups as $cookieGroupKey => $cookieGroupValue) {
            $cookies = (array)$cookieGroupValue['cookies'];

            $cookieSettings = array_filter($cookies, function ($cookieSettings) {
                return array_key_exists('previousCookieSettingHash', $cookieSettings);
            });


            if (!empty($cookieSettings) && is_array($cookieSettings)) {
                $hasCookieName = array_filter($cookieSettings, function($element) {
                    return array_key_exists('cookieName', $element);
                });
                if (!$hasCookieName) {
                    continue;
                }

                $cookiesWithPreviousVersions = array_merge($cookiesWithPreviousVersions, $cookieSettings);
            }
        }


        return $cookiesWithPreviousVersions;
    }

    /**
     * Get a cookie which has a previousCookieSettingHash configured by cookieName.
     *
     * Returns an empty array if no cookie was found with the given cookieName or if the cookie has no previousCookieSettingHash.
     * Otherwise an array with the configured values of a cookie will be returned.
     *
     * Example output:
     * [
     *  cookieName: ''
     *  lifetime: ''
     *  previousCookieSettingHash: ''
     * ]
     *
     * @param $cookieName
     * @return array
     */
    public function getCookieWithPreviousVersion($cookieName)
    {
        if (!is_array($this->cookieGroups)) {
            return [];
        }

        $cookieWithPreviousVersion = array_filter($this->getCookiesWithPreviousVersion(), function ($cookieSetting) use ($cookieName) {
            return $cookieSetting['cookieName'] === $cookieName;
        });

        if (!is_array($cookieWithPreviousVersion) || empty($cookieWithPreviousVersion)) {
            return [];
        }

        return reset($cookieWithPreviousVersion);
    }

    /**
     * Checks if a cookie is outdated by cookieName.
     *
     * A cookie is outdated when a calculated hash is different to the previousHash.
     * Returns false, if no hash is configured.
     *
     * @param string $cookieName
     * @return bool
     */
    public function cookieIsOutdated($cookieName)
    {
        $hashedValue = $this->buildHashForCookie($cookieName);
        $currentHash = $this->getPreviousHashForCookie($cookieName);

        if (empty($currentHash)) {
            return false;
        }

        return $hashedValue !== $currentHash;
    }

    /**
     * Builds a hash for a cookie, if previousCookieSettingHash is configured.
     *
     * Returns an empty string if the given cookie was not found in the settings.
     *
     * @param string $cookieName
     * @return string
     */
    public function buildHashForCookie($cookieName)
    {
        $cookieWithPreviousVersion = $this->getCookieWithPreviousVersion($cookieName);

        if (!$cookieWithPreviousVersion) {
            return '';
        }

        unset($cookieWithPreviousVersion['previousCookieSettingHash']);

        return sha1(json_encode($cookieWithPreviousVersion));
    }

    /**
     * Gets a configured previousCookieSettingHash for a cookie by cookieName.
     *
     * If no previousCookieSettingHash exists an empty string will be returned.
     *
     * @param string $cookieName
     * @return string
     */
    public function getPreviousHashForCookie($cookieName)
    {
        $cookieWithPreviousVersion = $this->getCookieWithPreviousVersion($cookieName);

        if (!array_key_exists('previousCookieSettingHash', $cookieWithPreviousVersion)) {
            return '';
        }

        return $cookieWithPreviousVersion['previousCookieSettingHash'];
    }

    /**
     * Updates the lifetime for a cookie which has a previousCookieSettingHash.
     *
     * Uses the configured lifetime and updates the cookie with it.
     * Returns the updated cookie.
     *
     * @param Cookie $cookie
     * @return Cookie
     */
    public function updateLifeTimeForCookie(Cookie $cookie)
    {
        $cookieValues = $this->getCookieWithPreviousVersion($cookie->getName());

        if ($cookieValues) {
            $updatedCookie = new Cookie($cookie->getName(), $cookie->getValue(), new \DateTime('+ ' . $cookieValues['lifetime']));

            return $updatedCookie;
        }

        return $cookie;
    }

    /**
     * This function will update all lifetimes for cookies which are found in the request.
     * All updated cookies will be put in the response directly.
     *
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @return array
     */
    public function updateLifetimeForAllCookies(RequestInterface $request, ResponseInterface $response)
    {
        $cookiesWithPreviousVersion = $this->getCookiesWithPreviousVersion();

        if (!is_array($cookiesWithPreviousVersion)) {
            return [];
        }

        foreach ($cookiesWithPreviousVersion as $cookieWithPreviousVersion) {

            if (!$this->cookieIsOutdated($cookieWithPreviousVersion['cookieName'])) {
                continue;
            }

            $cookieFromRequest = $request->getCookie($cookieWithPreviousVersion['cookieName']);
            $updatedCookie = $this->updateLifeTimeForCookie($cookieFromRequest);

            $response->setCookie($updatedCookie);
        }
    }

}