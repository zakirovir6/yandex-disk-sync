<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

/** @var \Laravel\Lumen\Routing\Router $router */
$router->get('/', function () use ($router) {
    $params = [
        'response_type' => 'code',
        //'force_confirm' => 'yes',
        'client_id' => getenv('YANDEX_OAUTH_CLIENT_ID'),
    ];

    return redirect(getenv('YANDEX_OAUTH_AUTHORIZE_PATH') . '?' . http_build_query($params), 302, [], true);
});

$router->get('/callback', ['as' => 'callback', function(\Illuminate\Http\Request $request, \GuzzleHttp\Client $httpClient) use ($router) {
    $code = $request->query->get('code');
    if (!$code) {
        $error = $request->query->get('error');
        $error_description = $request->query->get('error_description');

        echo 'Код ошибки: ' . $error . '<br/>';
        echo 'Описание ошибки: '  . $error_description;

        return;
    }

    $tokenResponse = $httpClient->request('POST', getenv('YANDEX_OAUTH_TOKEN_PATH'), [
        'form_params' => [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'client_id' => getenv('YANDEX_OAUTH_CLIENT_ID'),
            'client_secret' => getenv('YANDEX_OAUTH_CLIENT_SECRET'),

        ]
    ]);

    if (!$tokenResponse->getStatusCode() === 200) {
        echo 'Ошибка: ' . '<br/>';
        echo $tokenResponse->getBody();

        return;
    }

    $tokenJson = json_decode((string) $tokenResponse->getBody(), true);
    echo '<pre>'; var_export($tokenJson); echo '</pre>';

}]);
