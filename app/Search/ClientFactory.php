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
namespace App\Search;

use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Hyperf\Guzzle\RingPHP\PoolHandler;

class ClientFactory
{
    /**
     * @var PoolHandler
     */
    protected $handler;

    public function create(): Client
    {
        return ClientBuilder::create()->setHosts(['127.0.0.1:9200'])
            ->setHandler($this->getHandler())
            ->build();
    }

    protected function getHandler()
    {
        if ($this->handler instanceof PoolHandler) {
            return $this->handler;
        }

        return $this->handler = make(PoolHandler::class, [
            'option' => [
                'timeout' => 2,
                'max_connections' => 32,
            ],
        ]);
    }
}
