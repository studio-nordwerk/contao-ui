<?php

declare(strict_types=1);

namespace Nordwerk\TestimonialsBundle\Command;

use Contao\CoreBundle\Framework\ContaoFramework;
use Nordwerk\TestimonialsBundle\Import\OveleonImporter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'nordwerk:testimonials:import-oveleon', description: 'Import Oveleon recommendations as unpublished testimonials (dry-run by default)')]
final class ImportOveleonCommand extends Command
{
    public function __construct(
        private readonly ContaoFramework $framework,
        private readonly OveleonImporter $importer,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('source-archive', InputArgument::REQUIRED, 'Oveleon archive ID');
        $this->addArgument('target-archive', InputArgument::REQUIRED, 'Testimonials archive ID');
        $this->addOption('execute', null, InputOption::VALUE_NONE, 'Persist the import; all entries remain unpublished');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->framework->initialize();
        $source = filter_var($input->getArgument('source-archive'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $target = filter_var($input->getArgument('target-archive'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        if (false === $source || false === $target) {
            $output->writeln('<error>Archive IDs must be positive integers.</error>');

            return self::INVALID;
        }

        try {
            $result = $this->importer->import($source, $target, (bool) $input->getOption('execute'));
        } catch (\RuntimeException $exception) {
            $output->writeln('<error>'.$exception->getMessage().'</error>');

            return self::FAILURE;
        }

        $output->writeln(\sprintf('%s: %d imported, %d skipped. All imported entries are unpublished.', $input->getOption('execute') ? 'Import' : 'Dry run', $result['imported'], $result['skipped']));

        return self::SUCCESS;
    }
}
