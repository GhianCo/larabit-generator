<?php

namespace App\Factory;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class LarabitGeneratorCommand extends Command
{
    const COMMAND_VERSION = '0.0.1';

    public function __construct($app)
    {
        parent::__construct();
        $this->container = $app->getContainer();
    }

    protected function configure()
    {
        $this->setName('generate:resources:database')
            ->setDescription('Al ingresar el nombre de alguna base de datos se generan los recursos.')
            ->setHelp('Este comando crea la recursos necesarios para exponer la informacion de cada base de datos. V- ' . self::COMMAND_VERSION)
            ->addArgument(
                'database',
                InputOption::VALUE_REQUIRED,
                'Cual es la base de datos de conexión?.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dbConn = $this->container->get('db');
        $database = $input->getArgument('database');
        $generator = new LarabitGeneratorService($dbConn, $database);
        $generator->generateStructure();
        $output->writeln('Success - Se generaron los recursos de la base de datos: ' . $database);
        return 0;
    }
}
