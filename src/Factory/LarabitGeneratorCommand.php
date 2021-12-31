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
            )->addOption(
                'host',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cual es el host de conexión?.'
            )->addOption(
                'user',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cual es el usuario de conexión?.'
            )->addOption(
                'pass',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cual es la clave de conexión?.'
            )->addOption(
                'port',
                null,
                InputOption::VALUE_OPTIONAL,
                'Cual es la puerto de conexión?.'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->validateOptionsAndArgs($input);
        $dbConn = $this->container->get('db');
        $database = $input->getArgument('database');
        $generator = new LarabitGeneratorService($dbConn, $database);
        $generator->generateStructure();
        $output->writeln('Success - Se generaron los recursos de la base de datos: ' . $database);
        return 0;
    }

    protected function validateOptionsAndArgs(InputInterface $input)
    {
        if ($input->getArgument('database')) $_SERVER['DB_NAME'] = $input->getArgument('database');
        if ($input->getOption('host')) $_SERVER['DB_HOST'] = $input->getOption('host');
        if ($input->getOption('user')) $_SERVER['DB_USER'] = $input->getOption('user');
        if ($input->getOption('pass')) $_SERVER['DB_PASS'] = $input->getOption('pass');
        if ($input->getOption('port')) $_SERVER['DB_PORT'] = $input->getOption('port');
    }
}
