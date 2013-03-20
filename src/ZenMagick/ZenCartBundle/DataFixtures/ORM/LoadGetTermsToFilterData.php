<?php

namespace ZenMagick\ZenCartBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadGetTermsToFilterData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        \Nelmio\Alice\Fixtures::load(__DIR__.'/get_terms_to_filter.yml', $manager);
    }
}
