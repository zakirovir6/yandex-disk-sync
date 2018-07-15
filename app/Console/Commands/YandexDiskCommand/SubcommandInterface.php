<?php


namespace App\Console\Commands\YandexDiskCommand;


interface SubcommandInterface
{
    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * @return string
     */
    public function getName(): string;


    public function handle(): void;
}