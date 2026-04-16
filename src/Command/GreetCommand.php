<?php

declare(strict_types=1);

namespace Acme\Starter\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Example CLI command.
 *
 * Run with: tcms acme:greet --name=World
 */
class GreetCommand extends Command
{
	protected function configure(): void
	{
		$this
			->setName('acme:greet')
			->setDescription('A greeting from the starter extension')
			->addOption('name', null, InputOption::VALUE_REQUIRED, 'Who to greet', 'World');
	}

	protected function execute(InputInterface $input, OutputInterface $output): int
	{
		$name = (string) $input->getOption('name');
		$output->writeln("Hello, {$name}! This is the starter extension.");

		return Command::SUCCESS;
	}
}
