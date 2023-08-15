<?php

namespace Realtyna\MvcCore\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SetupPluginCommands extends Command
{

    public function __construct(string $name = null)
    {
        parent::__construct(null);

        $this->path = $name;
    }

    protected static $defaultName = 'plugin:setup';

    protected static $defaultDescription = 'Setup the Plugin';

    protected function configure(): void
    {
        $this->setHelp('This command will setup the plugin for you.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $helper = $this->getHelper('question');
        $pluginNameQuestion = new Question('What is your plugin name(For Example: Realtyna Home Valuation): ');
        $pluginNamespaceQuestion = new Question(
            'What is your plugin namespace(Insert CamelCase, for example: HomeValuation): '
        );
        $apiNamespaceQuestion = new Question('What is your plugin API namespace(for example: home-valuation): ');


        $output->writeln([
            'Plugin Setup',
            '============',
            '',
        ]);
        $pluginName = $helper->ask($input, $output, $pluginNameQuestion);
        while (!$pluginName) {
            $pluginName = $helper->ask($input, $output, $pluginNameQuestion);
        }
        $this->replaceInAllFiles('Base Structure Plugin', $pluginName);


        $pluginNamespace = $helper->ask($input, $output, $pluginNamespaceQuestion);
        while (!$pluginNamespace) {
            $pluginNamespace = $helper->ask($input, $output, $pluginNamespaceQuestion);
        }
        $this->replaceInAllFiles('MustRename', $pluginNamespace);

        $pluginNameWithDash = strtolower(str_replace(' ', '_', $pluginName));
        $pluginNameWithDash = str_replace('realtyna_', '', $pluginNameWithDash);
        $this->replaceInAllFiles('must_rename', $pluginNameWithDash);

        $this->replaceInAllFiles('MUST_RENAME', strtoupper($pluginNameWithDash));


        $apiNamespace = $helper->ask($input, $output, $apiNamespaceQuestion);
        while (!$apiNamespace) {
            $apiNamespace = $helper->ask($input, $output, $apiNamespaceQuestion);
        }
        $this->replaceInAllFiles("'namespace' => 'wpl'", "'namespace' => '" . strtolower($apiNamespace) . "'");

        return Command::SUCCESS;
    }


    function getDirContents($dir, $filter = '', &$results = array())
    {
        $files = scandir($dir);

        foreach ($files as $key => $value) {
            $path = realpath($dir . DIRECTORY_SEPARATOR . $value);
            if ($value == 'vendor') {
                continue;
            }

            if ($value == '.git') {
                continue;
            }

            if ($value == '.gitkeep') {
                continue;
            }
            if (is_file($value) && !preg_match('/^.*\.(php)$/i', $value)) {
                continue;
            }
            if (!is_dir($path)) {
                if (empty($filter) || preg_match($filter, $path)) {
                    $results[] = $path;
                }
            } elseif ($value != "." && $value != "..") {
                $this->getDirContents($path, $filter, $results);
            }
        }

        return $results;
    }

    public function replaceInAllFiles($serach, $replace)
    {
        $path = realpath($this->path);
        $allFiles = $this->getDirContents($path);

        foreach ($allFiles as $file) {
            $str = file_get_contents($file);
            $str = str_replace($serach, $replace, $str);
            file_put_contents($file, $str);
        }
    }


}