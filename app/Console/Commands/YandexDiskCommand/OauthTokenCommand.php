<?php

namespace App\Console\Commands\YandexDiskCommand;


use App\Services\YandexOauthTokenService;
use Illuminate\Console\Command;

class OauthTokenCommand implements SubcommandInterface
{
    /**
     * @var YandexOauthTokenService
     */
    private $oauthTokenService;
    /**
     * @var Command
     */
    private $parentCommand;

    /**
     * OauthTokenCommand constructor.
     * @param Command $parentCommand
     * @param YandexOauthTokenService $oauthTokenService
     */
    public function __construct(Command $parentCommand, YandexOauthTokenService $oauthTokenService)
    {
        $this->oauthTokenService = $oauthTokenService;
        $this->parentCommand = $parentCommand;
    }

    public function getDescription(): string
    {
        return 'Get OAuth token from yandex';
    }

    public function getName(): string
    {
        return 'oauth:get-token';
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(): void
    {
        $confirmationCodes = $this->oauthTokenService->requestConfirmationCodes();
        if (!$this->parentCommand->confirm('Go to ' . $confirmationCodes->verification_url .
            ' and enter code ' . $confirmationCodes->user_code . '. Continue?', true)) {
            $this->parentCommand->info('Exit...');
        }
        $token = $this->oauthTokenService->requestToken($confirmationCodes->device_code);
        $this->parentCommand->info('Oauth token: ' . $token->access_token);
    }
}