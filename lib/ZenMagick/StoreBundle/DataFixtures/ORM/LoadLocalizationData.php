<?php

namespace ZenMagick\StoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Nelmio\Alice\Fixtures;

class LoadLocalizationData implements FixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        Fixtures::load(__DIR__.'/language.yml', $manager);
        Fixtures::load(__DIR__.'/address_format.yml', $manager);
        Fixtures::load(__DIR__.'/country.yml', $manager);
        Fixtures::load(__DIR__.'/currency.yml', $manager);
        Fixtures::load(__DIR__.'/zone.yml', $manager);
    }
}
