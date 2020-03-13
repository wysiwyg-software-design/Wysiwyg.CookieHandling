<?php

namespace Wysiwyg\CookieHandling\Domain\Http;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Component\ComponentContext as ComponentContext;
use Neos\Flow\Http\Component\ComponentInterface;
use Neos\Flow\Http\Cookie;
use Neos\Flow\Http\Request;
use Neos\Flow\Http\Response;
use Psr\Http\Message\ResponseInterface;
use Wysiwyg\CookieHandling\Domain\Service\CookieConsentService;

class CookieConsentHandlerComponent implements ComponentInterface
{
    /**
     * @Flow\Inject
     * @var CookieConsentService
     */
    protected $cookieConsentService;

    /**
     * Adds allowed and to be deleted cookies to the response.
     *
     * @param ComponentContext $componentContext
     */
    public function handle(ComponentContext $componentContext)
    {
        $request = $componentContext->getHttpRequest();
        $response = $componentContext->getHttpResponse();

        // Exclude backend
        $requestPath = $request->getUri()->getPath();
        if (strpos($requestPath, '/neos') === 0 || strstr($requestPath, '@user')) {
            return;
        }

        $cookieHandledResponse = $this->handleCookiesInResponse($response);
        $componentContext->replaceHttpResponse($cookieHandledResponse);
    }

    /**
     * This functions adds all cookies from the cookieJar to the response.
     * These cookies can be accepted cookies or to be deleted cookies.
     *
     * @param ResponseInterface $response
     *
     * @return ResponseInterface
     */
    protected function handleCookiesInResponse($response)
    {
        $cookieJar = $this->cookieConsentService->getCookieJar();

        if (empty($cookieJar)) {
            return $response;
        }

        /** @var Cookie $cookieInJar */
        foreach ($cookieJar as $cookieInJar) {
            // Handle Non-PSR7 and PSR7 cookie methods
            if (method_exists($response, 'setCookie')) {
                $response->setCookie($cookieInJar);
            } else {
                $response = $response->withAddedHeader('Set-Cookie', (string)$cookieInJar);
            }
        }

        return $response;
    }
}
