<?php

namespace Conway\Command;

use Conway\Entity\Board;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\ConsoleSectionOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ConwayCommand extends Command
{
    private ConsoleSectionOutput $tableSection;

    public function __construct()
    {
        parent::__construct('conway');
    }

    protected function configure()
    {
        $this
            ->setDescription("Run Conway's game of life");
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        if (!$output instanceof ConsoleOutputInterface) {
            throw new \LogicException('This command accepts only an instance of "ConsoleOutputInterface".');
        }

        $io = new SymfonyStyle($input, $output);
        $io->title("Conway's game of life");
        $io->note('CTRL+C to end the loop');

        // create the board
        $board = new Board(20, 20);

        // initialize the section for the table so we can clear it later in some consoles
        $this->tableSection = $output->section();
        $this->renderBoard($board);

        while (true) {
            sleep(1); // delay it so we see the loops easier
            $board = Board::createFromPreviousGeneration($board);
            $this->renderBoard($board);
        }

        return Command::SUCCESS; // not really needed because it's an infinite loop, but Symfony commands expect a return value
    }

    protected function renderBoard(Board $board): void
    {
        // clear the display
        $this->tableSection->clear();

        // create the new table
        $table = new Table($this->tableSection);

        // fill the table with data
        $table->setRows(\array_map(fn($row) => self::prepareRowForRendering($row), $board->rows));

        // render the table
        $table->render();
    }

    protected static function prepareRowForRendering($row): array
    {
        return \array_map(fn($tile) => $tile ? "<info>◼</info>" : '<fg=red>◻</>', $row);
    }

}