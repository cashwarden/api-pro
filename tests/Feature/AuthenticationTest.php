<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace tests\Feature;

use tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function testBasicTest()
    {
        $response = $this->client->post('/v1/join', [
            'json' => [
                'name' => 'test',
                'email' => 'test@mail.com',
                'password' => 'test',
                'base_currency_code' => 'USD',
            ],
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
