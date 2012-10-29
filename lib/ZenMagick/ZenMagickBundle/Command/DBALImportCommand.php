<?php
namespace ZenMagick\ZenMagickBundle\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Helper\HelperSet;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;

/**
 * DBAL import command.
 */
class DBALImportCommand extends \Doctrine\DBAL\Tools\Console\Command\ImportCommand
{

    /**
     * {@inheritDoc}
     */
    public function setApplication(Application $application = null)
    {
        parent::setApplication($application);
        if ($application && $application instanceof \Symfony\Bundle\FrameworkBundle\Console\Application) {
            if (!$helperSet = $this->getHelperSet()) {
                $this->setHelperSet(new HelperSet());
            }
            $helperSet->set(new ConnectionHelper($application->getKernel()->getContainer()->get('doctrine.orm.entity_manager')->getConnection()), 'db');
        }

    }
}
