<?php

namespace Wysiwyg\CookieHandling\Domain\Model;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Http\Cookie;

/**
 * @Flow\Entity
 */
class LoggedCookie
{
    /**
     * @var string
     */
    protected $cookieName;

    /**
     * @var string
     * @ORM\Column(nullable=true)
     */
    protected $domain;

    /**
     * @var int
     */
    protected $counter = 1;

    /**
     * @var \DateTime
     * @ORM\Column(nullable=true)
     */
    protected $lastModified;

    /**
     * @param Cookie $cookie
     */
    public function __construct(Cookie $cookie)
    {
        $this->cookieName = $cookie->getName();
        $this->domain = $cookie->getDomain();
        $this->lastModified = new \DateTime();
    }

    /**
     * @return string
     */
    public function getCookieName()
    {
        return $this->cookieName;
    }

    /**
     * @param string $cookieName
     */
    public function setCookieName($cookieName)
    {
        $this->cookieName = $cookieName;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * @param int $counter
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified;
    }

    /**
     * @param \DateTime $lastModified
     */
    public function setLastModified($lastModified)
    {
        $this->lastModified = $lastModified;
    }
}
