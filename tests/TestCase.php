<?php
/**
 *
 * @author forecho <caizhenghai@gmail.com>
 * @link https://cashwarden.com/
 * @copyright Copyright (c) 2020-2022 forecho
 * @license https://github.com/cashwarden/api/blob/master/LICENSE.md
 * @version 1.0.0
 */

namespace tests;

use GuzzleHttp\Client;
use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected ?Client $client;

    public function setUp(): void
    {
        $this->client = new Client([
            'base_uri' => 'http://localhost:8080',
            'timeout' => 5.0,
        ]);
    }

    public function tearDown(): void
    {
        $this->client = null;
    }
}
