<?php
namespace ZenMagick\ZenMagickBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * DBAL import command.
 */
class PrefixRemoveCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
        ->setName('zm:database:remove-prefix')
        ->setDescription('Remove database table prefix.')
        ->setDefinition(array(
            new InputArgument(
                'prefix', InputArgument::REQUIRED , 'Prefix to remove.'
            )
        ))
        ->setHelp(<<<EOT
Remove database table prefix.
EOT
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $conn = $this->getContainer()->get('doctrine.dbal.default_connection');

        $prefix = $input->getArgument('prefix');
        $output->writeln('<info>Removing Prefix: </info>'.$prefix);

        $sm = $conn->getSchemaManager();
        foreach ($sm->listTables() as $table) {
            if (0 === ($pos = strpos($table->getName(), $prefix))) {
                $name = substr($table->getName(), strlen($prefix));
                $sm->renameTable($table->getName(), $name);
                $output->writeln($table->getName().' -> '.$name);
            }
        }

    }

}
