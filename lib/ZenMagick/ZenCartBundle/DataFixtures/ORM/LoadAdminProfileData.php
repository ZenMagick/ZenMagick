<?php

namespace ZenMagick\ZenCartBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadAdminProfileData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        \Nelmio\Alice\Fixtures::load(__DIR__.'/admin_profile.yml', $manager);
    }
}
