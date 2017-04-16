<?php
namespace mhndev\oauthClient\Tests;

use mhndev\oauthClient\Objects\Identifier;
use mhndev\oauthClient\Objects\TokenInfo;
use mhndev\oauthClient\Objects\User;
use PHPUnit\Framework\TestCase;

/**
 * Class ObjectTest
 * @package mhndev\oauthClient\Tests
 */
class ObjectTest extends TestCase
{


    public function test_create_a_token_info_object()
    {
        $info = TokenInfo::fromArray([
            'scopes' => ['*'],
            'user' => [
                'id' => 2,
                'name' => 'hamid',
                'created_at' => '2017-04-04 10:54:55',
                'updated_at' => '2017-04-04 10:54:55',
                'identifiers' => [
                    [
                        'id' => 3,
                        'type' => 'email',
                        'value' => 'hamid.a85@gmail.com',
                        'verified' => true,
                    ],
                    [
                        'id' => 4,
                        'type' => 'mobile',
                        'value' => '09134107672',
                        'verified' => true,
                    ],
                ],
            ],
        ]);

        $this->assertEquals(['*'], $info->getScopes());
        $this->assertInstanceOf(User::class, $info->getUser());
        $this->assertInstanceOf(Identifier::class, $info->getUser()->getIdentifiers()[0]);
        $this->assertEquals(4, $info->getUser()->getIdentifiers()[1]->id);
    }


}
