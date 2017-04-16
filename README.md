
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
 Sample Usage 

```php

require_once 'vendor/autoload.php';

$client_id = 1;
$client_secret = 'client secret';
$client_token = 'sample client token';
$user_token = 'sample user token';



$tokenRepository = new \mhndev\oauthClient\repository\TokenRepositorySqlite(
    (new \mhndev\oauthClient\SqLiteConnection(__DIR__.DIRECTORY_SEPARATOR.'db.sqlite'))->connect()
);

$oauth_client = new \mhndev\oauthClient\Client(
    new \GuzzleHttp\Client(),
    'http://dev.digipeyk.com:8030',
    $client_id,
    $client_secret,
    $tokenRepository
);

$token = $oauth_client->getClientToken($client_id,$client_secret);

echo $token->getCredentials();

$whoIs = $oauth_client->getWhois(
    \mhndev\oauthClient\Objects\Identifier::EMAIL,
    'majid8911303@gmail.com',
    $token
);


$tokenInfo = $oauth_client->getTokenInfo(
    new \mhndev\valueObjects\implementations\Token(
        $user_token,
        'Bearer'
    )
);

$oauth_client->register('mj', '123456', ['mobile' => '09124971706' ]);


var_dump($tokenInfo->getUser());
var_dump($whoIs);
die();


```