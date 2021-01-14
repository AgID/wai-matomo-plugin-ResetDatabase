<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ResetDatabase\Commands;

use Piwik\Db;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class lets you define a new command. To read more about commands have a look at our Piwik Console guide on
 * http://developer.piwik.org/guides/piwik-on-the-command-line
 *
 * As Piwik Console is based on the Symfony Console you might also want to have a look at
 * http://symfony.com/doc/current/components/console/index.html
 */
class Reset extends ConsoleCommand
{
    /**
     * This methods allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {
        $this->setName('reset-database');
        $this->setDescription('Drop all tables and run sql from dump files');
        $this->addOption('dump', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Database dump filename');
    }

    /**
     * The actual task is defined in this method. Here you can access any option or argument that was defined on the
     * command line via $input and write anything to the console via $output argument.
     * In case anything went wrong during the execution you should throw an exception to make sure the user will get a
     * useful error message and to make sure the command does not exit with the status code 0.
     *
     * Ideally, the actual command is quite short as it acts like a controller. It should only receive the input values,
     * execute the task by calling a method of another class and output any useful information.
     *
     * Execute the command like: ./console reset-database --dump="/path/to/dump.filename.first.sql" --dump="../path/to/dump.filename.second.sql"
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('dump');

        $output->writeln("\n<comment>Check files</comment>\n");

        if (empty($options)) {
            $output->writeln("Insert at least one database filename to import");
            return;
        }

        $files = array();
        foreach ($options as $dump) {
            $dump = trim($dump);
            $filename = $this->path_is_absolute($dump) ? $dump : $filename = __DIR__ . DIRECTORY_SEPARATOR . $dump;
            if (!file_exists($filename)) {
                $output->writeln("<error>ERROR</error> $filename not found");
                $output->writeln("\nExit\n");
                return;
            } else {
                array_push($files, $filename);
                $output->writeln("<info>OK</info> $filename found");
            }
        }

        $output->writeln("\n<comment>Start reset</comment>");
       
        Db::dropAllTables();
        $output->writeln("<info>All tables dropped</info>");
        
        foreach ($files as $file) {
            $output->writeln("<comment>Importing $file</comment>");
            $sql = file_get_contents($file);
            Db::exec($sql);
            $output->writeln("<info>Imported</info>");
        }

        $output->writeln("\nReset done\n");
    }

    public function path_is_absolute($path)
    {
        return (DIRECTORY_SEPARATOR === $path[0]);
    }
}
