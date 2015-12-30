<?php
namespace CedricBlondeau\CatalogImportCommand\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Class ImportCommand
 *
 * @package CedricBlondeau\CatalogImportCommand\Console\Command
 */
class ImportCommand extends Command
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\State $state
    ) {
        $this->objectManager = $objectManager;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('catalog:import')
            ->setDescription('Import catalog')
            ->addArgument('filename', InputArgument::REQUIRED, "CSV file path")
            ->addOption('images_path', "i", InputOption::VALUE_OPTIONAL, "Images path")
            ->addOption('behavior', "b", InputOption::VALUE_OPTIONAL, "Behavior");
        parent::configure();
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $import = $this->getImportModel();
        if ($input->getOption('images_path')) {
            $import->setImagesPath($input->getOption('images_path'));
        }
        if ($input->getOption('behavior')) {
            $import->setBehavior($input->getOption('behavior'));
        }

        try {
            $import->setFile($input->getArgument('filename'));
            $result = $import->execute();
            if ($result) {
                $output->writeln('<info>The import was successful.</info>');
            }
            $output->writeln("Log trace:");
            $output->writeln($import->getFormattedLogTrace());

        } catch (FileNotFoundException $e) {
            $output->writeln('<error>File not found.</error>');
        } catch (\InvalidArgumentException $e) {
            $output->writeln('<error>Invalid source.</error>');
        }
    }

    /**
     * @return \CedricBlondeau\CatalogImportCommand\Model\Import
     */
    private function getImportModel()
    {
        /**
         * @todo:
         * Using Object Manager manually here because we need area code to be set first :/
         * Not sure how to deal with this. Any better idea would be very welcomed!
         */
        $this->state->setAreaCode('adminhtml');
        return $this->objectManager->create('CedricBlondeau\CatalogImportCommand\Model\Import');
    }

}
