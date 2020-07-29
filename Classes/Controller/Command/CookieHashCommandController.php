<?php

namespace Wysiwyg\CookieHandling\Controller\Command;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Cli\CommandController;
use Wysiwyg\CookieHandling\Domain\Service\CookieUpdateService;

class CookieHashCommandController extends CommandController
{

    /**
     * @Flow\Inject
     * @var CookieUpdateService
     */
    protected $cookieUpdateService;

    /**
     * Prints a hash for a cookie which can be used for updating your configuration
     *
     * Usage: cookiehandling:forcookie
     *
     * @param string $cookieName CookieName to generate hash
     */
    public function forCookieCommand(string $cookieName)
    {
        if (empty($cookieName)) {
            $this->outputLine('No cookie name provided!');
            return;
        }

        $cookieHash = $this->cookieUpdateService->buildHashForCookie($cookieName);
        if (!$cookieHash) {
            $this->outputLine('No cookie by the name ' . $cookieName . ' was found!');
            return;
        }

        $rows = [
            ['CookieName', $cookieName],
            ['Hash', $cookieHash]
        ];

        $this->output->outputTable($rows);
    }

    /**
     * Prints information for a cookie.
     * The following information will be provided:
     * - CookieName
     * - Hash
     * - BuildHash
     * - Outdated?
     *
     * Usage: cookiehandling:cookiehashinformation
     *
     * @param string $cookieName
     */
    public function cookieHashInformationCommand($cookieName)
    {
        if (empty($cookieName)) {
            $this->outputLine('No cookie name provided.');
            return;
        }

        $cookieHash = $this->cookieUpdateService->getPreviousHashForCookie($cookieName);
        if (!$cookieHash) {
            $this->outputLine('No cookie by the name ' . $cookieName . ' was found!');
            return;
        }
        $calculatedHash = $this->cookieUpdateService->buildHashForCookie($cookieName);
        $outdated = $this->cookieUpdateService->cookieIsOutdated($cookieName);


        $rows = [
            ['CookieName', $cookieName],
            ['Hash', $cookieHash],
            ['BuildHash', $calculatedHash],
            ['Outdated?', $outdated ? 'YES' : 'NO']
        ];

        $this->output->outputTable($rows);
    }
}