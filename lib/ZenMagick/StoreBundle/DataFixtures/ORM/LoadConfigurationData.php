<?php

namespace ZenMagick\StoreBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Nelmio\Alice\Fixtures;

class LoadConfigurationData implements FixtureInterface, ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $conn = $this->container->get('doctrine.dbal.default_connection');
        $conn->executeUpdate("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO';");
        $sql = 'SELECT configuration_group_id  FROM configuration_group WHERE configuration_group_id = ?';
        $hasGroupId0 = $conn->fetchColumn($sql, array(0), 0);
        if ('0' !== $hasGroupId0) {
            $conn->insert('configuration_group', array(
                'configuration_group_id' => 0,
                'configuration_group_title' => 'PLACEHOLDER0',
                'configuration_group_description' => '',
                'sort_order' => 0,
                'visible' => 0
            ));
        }
        Fixtures::load(__DIR__.'/configuration_group.yml', $manager);
        Fixtures::load(__DIR__.'/configuration.yml', $manager);
    }
}
