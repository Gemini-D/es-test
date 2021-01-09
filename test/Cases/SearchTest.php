<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace HyperfTest\Cases;

use App\Constants\Indices;
use App\Search\ClientFactory;
use Hyperf\Utils\Codec\Json;
use HyperfTest\HttpTestCase;

/**
 * @internal
 * @coversNothing
 */
class SearchTest extends HttpTestCase
{
    public function testSearch()
    {
        $client = di()->get(ClientFactory::class)->create();

        $params = [
            'index' => Indices::INDEX,
            'type' => Indices::TYPE,
            'body' => [
                'query' => [
                    'bool' => [
                        'must' => [
                            'match' => [
                                'body' => '最好',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $res = $client->search($this->format($client, $params));
        echo 'Search:' . PHP_EOL;
        echo Json::encode($res) . PHP_EOL;
        if (version_compare($this->version, '7.0', '>=')) {
            $this->assertSame(2, $res['hits']['total']['value']);
        } else {
            $this->assertSame(2, $res['hits']['total']);
        }
    }
}
