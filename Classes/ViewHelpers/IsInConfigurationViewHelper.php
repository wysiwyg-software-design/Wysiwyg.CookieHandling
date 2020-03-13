<?php
namespace Wysiwyg\CookieHandling\ViewHelpers;

use Neos\Flow\Annotations as Flow;
use Neos\FluidAdaptor\Core\ViewHelper\AbstractViewHelper;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

/**
 * This ViewHelper is used to display the configured state of a cookie in the backend.
 */
class IsInConfigurationViewHelper extends AbstractViewHelper
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('cookieName', 'string', 'CookieName', true);
    }

    /**
     * @return bool
     */
    public function render()
    {
        $cookieName = $this->arguments['cookieName'];
        return !is_null($this->cookieConsentService->getGroupForCookie($cookieName));
    }
}
