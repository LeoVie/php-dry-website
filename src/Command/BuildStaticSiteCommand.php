<?php
declare(strict_types=1);

namespace App\Command;

use App\Build\StaticSiteBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class BuildStaticSiteCommand extends Command
{
    protected static $defaultName = 'app:build-static-site';

    public function __construct(
        private StaticSiteBuilder $staticSiteBuilder
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $this->staticSiteBuilder->build();
        } catch (\Exception $e) {
            $output->writeln('Error');
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}