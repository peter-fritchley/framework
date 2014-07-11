<?php

namespace Migrations\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

use Migrations\Migrator;
use Migrations\MigrationRepo;
use Symfony\Component\Finder\Finder;
use Illuminate\Database\Capsule\Manager as DB;

class RebuildCommand extends BaseCommand
{
    protected $migrator;

    protected $repository;

    public function __construct($name = null)
    {
        $finder = new Finder();

        $this->repository = new MigrationRepo(DB::schema()->getConnection(), 'laravel_migrations');
        $this->migrator = new Migrator($this->repository, $finder);

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('migrations:rebuild')
            ->setDescription('Rebuild migrations.');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->migrator->reset();

        $output->writeln($this->migrator->getNotes());

        $path = path('migrations');

        $this->migrator->run($path);

        $output->writeln($this->migrator->getNotes());
    }


}