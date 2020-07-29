<?php
namespace Wysiwyg\CookieHandling\Controller;

use Neos\Flow\Annotations as Flow;
use Neos\Flow\Mvc\Controller\ActionController;
use Neos\Flow\Mvc\View\JsonView;
use Wysiwyg\CookieHandling\Domain\Service\CookieUpdateService;

class CookieUpdateController extends ActionController
{

    /**
     * @Flow\Inject
     * @var CookieUpdateService
     */
    protected $cookieUpdateService;

    /**
     * @var string
     */
    protected $defaultViewObjectName = JsonView::class;

    /**
     * Updates lifetime of all outdated cookies.
     */
    public function updateLifetimeForAllOutdatedCookiesAction()
    {
        $request = $this->request->getHttpRequest();
        $response = $this->getControllerContext()->getResponse();

        $this->cookieUpdateService->updateLifetimeForAllCookies($request, $response);

        $this->view->assign('value', 'OK');
    }

    /**
     * Updates a cookie by cookieName if the given cookie is outdated.
     * @param string $cookieName
     */
    public function updateLifetimeForOutdatedCookieAction($cookieName = '')
    {
        $cookieFromRequest = $this->request->getHttpRequest()->getCookie($cookieName);

        if ($cookieFromRequest && $this->cookieUpdateService->cookieIsOutdated($cookieName)) {
            $updatedCookie = $this->cookieUpdateService->updateLifeTimeForCookie($cookieFromRequest);
            $this->response->setCookie($updatedCookie);
        }
        $this->view->assign('value', 'OK');
    }

}