
## Php OAuth Client (Sdk) 

tokens table structure : 

```
    CREATE TABLE tokens (
        client_id TEXT NOT NULL PRIMARY KEY,
        client_secret TEXT NOT NULL,
        credentials TEXT NOT NULL,
        type TEXT NOT NULL,
        expires_at datetime
    );

```


```php
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once 'vendor/autoload.php';


$client_id = 1;
$client_secret = 'DONjJxACnkVqzHPoHOoUmfEmUjnDnXJiT0PuqCzO';

$tokenRepository = new \mhndev\oauthClient\repository\TokenRepositorySqlite(
    (new \mhndev\oauthClient\SqLiteConnection(__DIR__.DIRECTORY_SEPARATOR.'db.sqlite'))->connect()
);

$guzzleClient = new \GuzzleHttp\Client();

$guzzleHandler = new \mhndev\oauthClient\handlers\GuzzleHandler(
    $guzzleClient,
    'http://dev.digipeyk.com:8030'
);

// if you want to pass api ednpoints and you are not going to use default endpoints please pass the third argument as follow:
// consider you can just override as many endpoint as you want, and you are not forced to override all endpoints
$guzzleHandler = new \mhndev\oauthClient\handlers\GuzzleHandler(
    $guzzleClient,
    'http://dev.digipeyk.com:8030',
    ['removeIdentifier' => '/api/removeUserIdentifier']
);

$oauth_client = new \mhndev\oauthClient\Client($guzzleHandler, $tokenRepository);

$token = $oauth_client->getClientToken($client_id, $client_secret);

//register endpoint
$user_register = $oauth_client->register(
    'hamid',
    '123456',
    ['email'=>'ma2jid8911303@gmail.com'],
    $token
);

var_dump($user_register);

// whois endpoint

$user_whoIs = $oauth_client->getWhois(
    'email',
    'majid8911303@gmail.com',
    $token
);


var_dump($user_whoIs);

// get Token Info

$tokenValueObject = new \mhndev\valueObjects\implementations\Token(
    $token->getCredentials(), $token->getType()
);

$tokenInfo = $oauth_client->getTokenInfo($tokenValueObject);

var_dump($tokenInfo);



echo '<br><br><br><br><br>';

// now using mock handler instead as handler

$mockHandler = new \mhndev\oauthClient\handlers\MockHandler();

$oauth_client2 = new \mhndev\oauthClient\Client($mockHandler, $tokenRepository);


$tokenFromMock = $oauth_client2->getClientToken('wefwergderf', 'werwrgfer');

var_dump($tokenFromMock);

$result = $oauth_client2->register(
    'majid',
    '123456',
    ['email' => 'majid@gmail.com'],
    new \mhndev\oauthClient\entity\common\Token(
        'Bearer',
        '34r3t354t54tr',
        $client_id,
        $client_secret
    )
);


var_dump($result);


```



todo :
toArray for oauth objects
