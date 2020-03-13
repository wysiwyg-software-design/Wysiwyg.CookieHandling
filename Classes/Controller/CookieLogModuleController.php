<?php

namespace Wysiwyg\CookieHandling\Controller;

use Neos\Flow\Annotations as Flow;

use Wysiwyg\CookieHandling\Domain\Repository\LoggedCookieRepository;

class CookieLogModuleController extends \Neos\Neos\Controller\Module\AbstractModuleController
{
    /**
     * @Flow\Inject
     * @var LoggedCookieRepository
     */
    protected $loggedCookieRepository;

    public function indexAction()
    {
        $loggedCookies = $this->loggedCookieRepository->findAll();

        $this->view->assign('loggedCookies', $loggedCookies);
    }
}
