<?php


class YandexOauthTokenServiceTest extends TestCase
{
    /** @var \App\Services\YandexOauthTokenService */
    private $service;

    /** @var \App\Services\YandexOauthTokenService */
    private $fakeService;

    public function setUp()
    {
        $client = new \GuzzleHttp\Client();
        $this->service = new \App\Services\YandexOauthTokenService(
            $client,
            '4a0d7f7010624150adf4d37536d7d893',
            'b6252da520c0438796cc656371aef434',
            'DD761D68-E2BE-4988-9226-AA8B9195F5FF',
            'testdevice');

        $this->fakeService = new \App\Services\YandexOauthTokenService(
            $client,
            '4a0d7f7010624150adf4d37536d7d893_fake',
            'b6252da520c0438796cc656371aef434_fake',
            'DD761D68-E2BE-4988-9226-AA8B9195F5FF',
            'testdevice');
    }

    /**
     * @expectedException \GuzzleHttp\Exception\ClientException
     * @expectedExceptionCode 400
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testFakeGetConfirmationCodes()
    {
        $this->fakeService->requestConfirmationCodes();
    }

    /**
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testGetConfirmationCodes()
    {
        $codes = $this->service->requestConfirmationCodes();
        $this->assertTrue(true);

        $this->expectExceptionCode(400); //because user should confirm the code
        $token = $this->service->requestToken($codes->device_code);
    }

}