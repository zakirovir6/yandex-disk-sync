<?php


namespace App\Console\Commands;


use App\Services\YandexOauthTokenService;
use Illuminate\Console\Command;

class OauthTokenCommand extends Command
{
    protected $name = 'yandex:oauth:get-token';
    protected $description = 'Get OAuth token from yandex';

    /**
     * @var YandexOauthTokenService
     */
    private $oauthTokenService;

    public function __construct(YandexOauthTokenService $oauthTokenService)
    {
        $this->oauthTokenService = $oauthTokenService;

        parent::__construct();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        $confirmationCodes = $this->oauthTokenService->getConfirmationCodes();
        if (!$this->confirm('Go to ' . $confirmationCodes->verification_url .
            ' and enter code ' . $confirmationCodes->user_code . '. Continue?', true)) {
            $this->info('Exit...');
        }
        $token = $this->oauthTokenService->getToken($confirmationCodes->device_code);
        $this->info('Oauth token: ' . $token->access_token);
    }
}