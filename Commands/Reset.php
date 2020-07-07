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
        $this->setName('wairesetdatabase:reset');
        $this->setDescription('Drop all tables and run sql from dump files');
        $this->addOption('db', 'db', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Database filename');
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
     * Execute the command like: ./console wairesetdatabase:reset --db="filename" --db="filename"
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $input->getOption('db');

        $output->writeln("\n<comment>Check files</comment>\n");

        if (empty($options)) {
            $output->writeln("Insert at least one databse filename to import");
            return;
        }

        $files = array();
        foreach ($options as $db) {
            $filename = dirname(__DIR__, 1)."/sql/".$db;
            if (!file_exists($filename)) {
                $output->writeln("<error>ERROR</error> $db not found in the folder");
                $output->writeln("\nExit\n");
                return;
            } else {
                array_push($files, $filename);
                $output->writeln("<info>OK</info> $db found in the folder");
            }
        }

        $output->writeln("\n<comment>Start reset</comment>");
       

        Db::dropAllTables();
        $output->writeln("<info>All tables dropped</info>");
        
        foreach ($files as $file) {
            $output->writeln("<comment>Importing $file</comment>");

            // Temporary variable, used to store current query
            $query = '';
            // Read in entire file
            $lines = file($file);
            // Loop through each line
            foreach ($lines as $line) {
                // Skip it if it's a comment
                if (substr($line, 0, 2) == '--' || $line == '') {
                    continue;
                }
    
                // Add this line to the current segment
                $query .= $line;
                // If it has a semicolon at the end, it's the end of the query
                if (substr(trim($line), -1, 1) == ';') {
                    Db::exec($query);
                    $query = '';
                }
            }
           
            $output->writeln("<info>Imported</info>");
        }

        $output->writeln("\nReset done\n");
    }
}
