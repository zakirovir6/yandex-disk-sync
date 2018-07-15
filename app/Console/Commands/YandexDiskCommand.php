<?php


namespace App\Console\Commands;


use App\Console\Commands\YandexDiskCommand\ExitException;
use App\Console\Commands\YandexDiskCommand\SubcommandInterface;
use Illuminate\Console\Command;

class YandexDiskCommand extends Command
{
    private const SUBCOMMAND_DIR = __DIR__ . '/YandexDiskCommand';

    protected $name = 'yandex:disk';

    protected $description = 'interface for yandex disk';

    /** @var SubcommandInterface[] */
    private $subCommands = [];

    public function __construct()
    {
        $this->registerSubCommands();
        $this->addSubcommandUsages();
        parent::__construct();
    }

    private function addSubcommandUsages(): void
    {
        $this->addUsage('');
        $this->addUsage('Possible internal commands:');
        foreach ($this->subCommands as $subCommand) {
            $this->addUsage('> ' . $subCommand->getName() . ' - ' . $subCommand->getDescription());
        }
    }

    private function registerSubCommands(): void
    {
        foreach (new \DirectoryIterator(self::SUBCOMMAND_DIR) as $subcommandFile) {
            if (! $subcommandFile->isFile()) {
                continue;
            }

            if ($subcommandFile->getExtension() !== 'php') {
                continue;
            }

            $fileContents = file_get_contents($subcommandFile->getRealPath());
            $nsMatches = [];
            if (! preg_match('@^namespace ([^;]*);$@mU', $fileContents, $nsMatches)) {
                continue;
            }

            if (! isset($nsMatches[1])) {
                continue;
            }

            $namespace = $nsMatches[1];
            $basename = $subcommandFile->getBasename('.php');
            $className = $namespace . '\\' . $basename;

            try {
                $reflection = new \ReflectionClass($className);
            } catch (\ReflectionException $e) {
                continue;
            }

            if (! $reflection->implementsInterface(SubcommandInterface::class)) {
                continue;
            }

            if ($reflection->isInterface()) {
                continue;
            }

            $constructor = $reflection->getConstructor();

            if (!$constructor) {
                $subCommand = $reflection->newInstance();
                $this->subCommands[$subCommand->getName()] = $subCommand;
                continue;
            }
            $constructorParams = [];
            foreach ($constructor->getParameters() as $param) {
                if ($param->isOptional()) {
                    $constructorParams[] = null;
                    continue;
                }

                if ($param->isDefaultValueAvailable()) {
                    $constructorParams[] = $param->getDefaultValue();
                    continue;
                }

                $class = $param->getClass();
                if ($class->getName() === Command::class)
                {
                    $constructorParams[] = $this;
                    continue;
                }

                $paramInstance = app()->make($class->getName());
                $constructorParams[] = $paramInstance;
            }

            /** @var SubcommandInterface $subCommand */
            $subCommand = $reflection->newInstanceArgs($constructorParams);
            $this->subCommands[$subCommand->getName()] = $subCommand;
        }
    }

    public function handle(): void
    {
        while (true) {
            $command = $this->ask('Enter command');

            if (! isset($this->subCommands[$command])) {
                $this->info(implode(PHP_EOL, $this->getUsages()));
                continue;
            }

            try {
                $this->subCommands[$command]->handle();
            } catch (ExitException $e) {
                break;
            }
        }
    }
}