<?php
namespace ZenMagick\StoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command to clean out specified store datatypes.
 *
 */
class CleanDataCommand extends ContainerAwareCommand {

    private $rootDir;

    /**
     * {@inheritDoc}
     */
    protected function configure() {
        $this->rootDir = dirname(__DIR__).'/Resources/cleaners';
        $this
            ->setName('zm:clean-data')
            ->setDescription('Clean Store Data')
            ->addOption('connection', null, InputOption::VALUE_OPTIONAL, 'The connection to use for this command')
            ->addArgument('types', InputArgument::REQUIRED | InputArgument::IS_ARRAY, 'Type(s) of data to clean')
            ->setHelp(<<<EOT
The <info>zm:clean-data</info> Command cleans out various bits of store data.

A List of types is available in:
{$this->rootDir}

You can also optionally specify the name of a connection.

<info>php app/console zm:clean-data --connection=default</info>
EOT
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $conn = $this->getContainer()->get('doctrine.dbal.default_connection');
        if (($types = $input->getArgument('types')) !== null) {
            foreach ((array) $types as $type) {
                $file = $this->rootDir.'/'.$type.'.sql';
                if (!file_exists($file)) {
                    throw new \InvalidArgumentException(
                        sprintf("Type '<info>%s</info>' does not exist.", $type)
                    );
                }
                $output->write(sprintf("Cleaning Type '<info>%s</info>'... ", $type));
                $sql = file_get_contents($file);
                try {
                    $affectedRows = $conn->exec($sql);
                    $output->write(sprintf('%d affected rows', $affectedRows) . PHP_EOL);
                } catch (\Exception $e) {
                    $output->write('error!' . PHP_EOL);
                    throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
                }
            }
        }
    }
}
