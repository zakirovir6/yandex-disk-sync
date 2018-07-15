<?php


namespace App\Console\Commands\YandexDiskCommand;


class ExitCommand implements SubcommandInterface
{
    public function getDescription(): string
    {
        return 'exit program';
    }

    public function getName(): string
    {
        return 'exit';
    }

    /**
     * @throws ExitException
     */
    public function handle(): void
    {
        throw new ExitException('Exit command');
    }

}