<?php
namespace ZenMagick\ZenCartBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Fix various issues with zencart databases
 *
 * @todo Fold this into the updater.
 *       It needs to run before fixtures import
 */
class FixDatabaseCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
        ->setName('zencart:database:fix')
        ->setDescription('Fix various database table problems.')
        ->setHelp(<<<EOT
Fix various database table problems.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine.dbal.default_connection');

        $output->writeln('<info>Fixing Database Problems: </info>');

        $conn->executeUpdate("SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO';");

        $sql = 'SELECT categories_id FROM categories WHERE categories_id = ?';
        $hasRootCat = $conn->fetchColumn($sql, array(0), 0);
        if ('0' !== $hasRootCat) {
            $conn->insert('categories', array(
                'categories_id' => 0,
                'parent_id' => NULL,
                'categories_image' => '',
                'categories_status' => 0
            ));
            $output->writeln('<info>Created root category</info>');
        }

        $result = $conn->update('zones',
            array('zone_code' => 'GR', 'zone_name' => 'Graubünden'),
            array('zone_code' => 'JU', 'zone_name' => 'Graubnden')
        );

        if ($result) {
            $output->writeln('<info>Fixed GR zone entry</info>');
        }
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
            $output->writeln('<info>Created configuration_group_id 0</info>');
        }

    }
}
