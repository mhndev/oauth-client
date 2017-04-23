<?php
namespace mhndev\oauthClient\Tests;

use mhndev\oauthClient\Client;
use mhndev\oauthClient\handlers\MockHandler;
use mhndev\oauthClient\interfaces\repository\iTokenRepository;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use mhndev\oauthClient\repository\TokenRepositorySqlite;
use mhndev\oauthClient\SqLiteConnection;
use mhndev\valueObjects\implementations\Token;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectTest
 * @package mhndev\oauthClient\Tests
 */
class ObjectTest extends TestCase
{

    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory virtual file system
     */
    private $root;

    /**
     * @var iTokenRepository
     */
    private $tokenRepository;

    /**
     * setup test
     */
    public function setUp()
    {
/*
        $this->root = vfsStream::setup('root');
        $filename = vfsStream::url('root').DIRECTORY_SEPARATOR."tokens.sqlite";
*/

        $filename = dirname(__DIR__).DIRECTORY_SEPARATOR.'db.sqlite';

        $this->tokenRepository = new TokenRepositorySqlite(
            (new SqLiteConnection($filename))->connect()
        );

    }


    public function test_create_a_token_info_object()
    {
        $oauthClient = new Client(new MockHandler(), $this->tokenRepository);

        $tokenInfo = $oauthClient->getTokenInfo(
            new Token('sample credentials', Token::SCHEMA_Bearer)
        );

        $this->assertInstanceOf(TokenInfo::class, $tokenInfo);
        $this->assertObjectHasAttribute('user', $tokenInfo);
        $this->assertObjectHasAttribute('scopes', $tokenInfo);

        $this->assertInstanceOf(User::class, $tokenInfo->getUser());
        $this->assertInternalType('array', $tokenInfo->getScopes() );
        $this->assertContains($tokenInfo->getType(), [ TokenInfo::TOKEN_USER, TokenInfo::TOKEN_CLIENT ]);
    }


    public function test_register_a_user()
    {
        $oauthClient = new Client(new MockHandler(), $this->tokenRepository);

        $user = $oauthClient->register(
            'Majid Abdolhosseini',
            '123456',
            ['email' => 'majid8911303@gmail.com'],
            $oauthClient->getClientToken('customer_123', '134rt3t5gr')
        );

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals('Majid Abdolhosseini', $user->getName() );

    }


}
