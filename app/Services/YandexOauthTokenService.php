<?php


namespace App\Services;


use App\Services\YandexOauthTokenService\ConfirmationCodes;
use App\Services\YandexOauthTokenService\Token;
use GuzzleHttp\Client;

class YandexOauthTokenService
{
    private const HOST = 'oauth.yandex.ru';
    private const PATH_DEVICE_CODE = '/device/code';
    private const PATH_TOKEN = '/token';

    /**
     * @var Client
     */
    private $httpClient;
    /**
     * @var string
     */
    private $clientId;
    /**
     * @var string
     */
    private $clientSecret;
    /**
     * @var string
     */
    private $deviceId;
    /**
     * @var string
     */
    private $deviceName;

    public function __construct(Client $client, string $clientId, string $clientSecret, string $deviceId, string $deviceName)
    {
        $this->httpClient = $client;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->deviceId = $deviceId;
        $this->deviceName = $deviceName;
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getConfirmationCodes(): ConfirmationCodes
    {
        $url = 'https://' . self::HOST . self::PATH_DEVICE_CODE;

        $result = $this->httpClient->request('POST', $url, [
            'form_params' => [
                'client_id' => $this->clientId,
                'device_id' => $this->deviceId,
                'device_name' => $this->deviceName
            ]
        ]);

        if ($result->getStatusCode() !== 200) {
            throw new \LogicException('Invalid response with code ' . $result->getStatusCode() . ', message: ' . (string) $result->getBody(), $result->getStatusCode());
        }

        $codesArray = json_decode((string)$result->getBody(), true);

        $confirmationCodes = new ConfirmationCodes();
        $confirmationCodes->device_code = $codesArray['device_code'];
        $confirmationCodes->expires_in = $codesArray['expires_in'];
        $confirmationCodes->interval = $codesArray['interval'];
        $confirmationCodes->user_code = $codesArray['user_code'];
        $confirmationCodes->verification_url = $codesArray['verification_url'];

        return $confirmationCodes;
    }

    /**
     * @param string $deviceCode
     * @return Token
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getToken(string $deviceCode): Token
    {
        $url = 'https://' . self::HOST . self::PATH_TOKEN;

        $result = $this->httpClient->request('POST', $url, [
            'form_params' => [
                'grant_type' => 'device_code',
                'code' => $deviceCode,
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret
            ]
        ]);

        if ($result->getStatusCode() !== 200) {
            throw new \LogicException('Invalid response with code ' . $result->getStatusCode() . ', message: ' . (string) $result->getBody(), $result->getStatusCode());
        }

        $arrayToken = json_decode((string) $result->getBody(), true);

        $token = new Token();
        $token->expires_in = $arrayToken['expires_in'];
        $token->access_token = $arrayToken['access_token'];
        $token->refresh_token = $arrayToken['refresh_token'];
        $token->token_type = $arrayToken['token_type'];

        return $token;
    }
}