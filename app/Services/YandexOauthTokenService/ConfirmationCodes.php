<?php


namespace App\Services\YandexOauthTokenService;


class ConfirmationCodes
{
    public $device_code;
    public $user_code;
    public $verification_url;
    public $interval;
    public $expires_in;
}