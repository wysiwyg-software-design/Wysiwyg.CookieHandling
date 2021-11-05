<?php

namespace Wysiwyg\CookieHandling\Domain\Repository;

use Neos\Flow\Annotations as Flow;
use Wysiwyg\CookieHandling\Domain\Model\LoggedCookie;

/**
 * @Flow\Scope("singleton")
 */
class LoggedCookieRepository extends \Neos\Flow\Persistence\Repository
{
    /**
     * Updates a LoggedCookie object and sets the lastModified date.
     *
     * @param LoggedCookie $object
     *
     * @throws \Neos\Flow\Persistence\Exception\IllegalObjectTypeException
     */
    public function update($object): void
    {
        $object->setLastModified(new \DateTime());
        parent::update($object);
    }
}
