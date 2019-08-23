<?php declare(strict_types=1);


namespace Jeekens\Console\Command;


use Jeekens\Console\Command;
use Jeekens\Console\CommandInterface;
use Jeekens\Console\Output\Table;
use function sprintf;

/**
 * Class HelpCommand
 * @package Jeekens\Console\Command
 */
class HelpCommand implements CommandInterface
{

    public $name = 'help';

    public $usage = 'Used to output command help content.';

    public $describe = 'Help command.';

    public $arguments = [
        'Command name.',
    ];

    public $bindOpts = [
        '-h, --help'
    ];

    /**
     * @return string
     *
     * @throws \Jeekens\Console\Exception\Exception
     * @throws \Jeekens\Console\Exception\UnknownColorException
     */
    public function getExample()
    {
        return sprintf('php %s help "commandName"', Command::getScript());
    }


    public function handle()
    {
        $commandName = Command::getFullCommandName();

        if ($commandName === 'help' || empty($commandName)) {
            $commandName = Command::getParam(1) ?? Command::getParam('command');
        }

        Command::clear();
        Command::line();

        if (empty($commandName)) {
            $this->noCommand();
        } else {
            $this->command($commandName);
        }

        return false;
    }

    protected function noCommand()
    {
        $commandInfos = Command::getCommandInfos();
        $table = new Table();
        $table->addHeader('<yellow>Command List</yellow>')
            ->addTable()
            ->addHeader('Command Name')
            ->addHeader('Describe')
            ->addHeader('Usage');

        foreach ($commandInfos as $commandName => $item) {
            $table->addRow()
                ->addColumn(sprintf('%s', $commandName))
                ->addColumn(class_get($item['command'], 'describe') ?? '')
                ->addColumn(class_get($item['command'], 'usage') ?? '');
        }

        $table->showAllBorders()->display();
    }

    /**
     * @param string $commandName
     *
     * @throws \Jeekens\Console\Exception\Exception
     * @throws \Jeekens\Console\Exception\UnknownColorException
     */
    protected function command(string $commandName)
    {
        $commandInfos = Command::getCommandInfos();
        if (isset($commandInfos[$commandName]) && $command = $commandInfos[$commandName]['command']) {
            $group = Command::getCommandGroup();
            $table = new Table();
            $table->addHeader($commandName);
            $globalOpts = Command::getBindOpts();
            $groupOpts = Command::getGroupBindOpts();

            if (($describe = class_get($command, 'describe'))
                && is_string($describe) && $describe != '') {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Describe</yellow>:')
                    ->addRow()
                    ->addColumn('  '.$describe);
            }

            if (($usage = class_get($command, 'usage'))
                && is_string($usage) && $usage != '') {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Usage</yellow>:')
                    ->addRow()
                    ->addColumn('  '.$usage);
            }

            if (($arguments = class_get($command, 'arguments'))) {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Arguments</yellow>:');

                $i = 1;

                foreach ($arguments as $des) {
                    $table->addRow()
                        ->addColumn(sprintf(' <green>arg%s</green>', $i))
                        ->addColumn('  '.$des);
                }
            }

            if (! empty($globalOpts)) {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Global Options</yellow>:');

                foreach ($globalOpts as $opt => $des) {
                    $table->addRow()
                        ->addColumn(sprintf(' <green>%s</green>', $opt))
                        ->addColumn($des);
                }

            }

            if (!empty($group) && isset($groupOpts[$group]) && ($go = $groupOpts[$group])) {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Group Global Options</yellow>:');

                foreach ($go as $opt => $des) {
                    $table->addRow()
                        ->addColumn(sprintf(' <green>%s</green>', $opt))
                        ->addColumn($des);
                }
            }

            if (($options = class_get($command, 'options'))) {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Options</yellow>:');

                foreach ($options as $opt => $des) {
                    $table->addRow()
                        ->addColumn(sprintf(' <green>%s</green>', $opt))
                        ->addColumn($des);
                }
            }

            if (($example = class_get($command, 'example'))
                && is_string($example) && $example != '') {
                $table->addTable()
                    ->addRow(null, true)
                    ->addColumn('<yellow>Example</yellow>:')
                    ->addRow()
                    ->addColumn('  '.$example);
            }

            $table->hideBorder()
                ->display();
        } else {
            Command::error(sprintf('Command "%s" not found.', $commandName), true);
        }
    }

}