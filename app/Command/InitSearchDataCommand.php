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
namespace App\Command;

use App\Constants\Indices;
use App\Search\ClientFactory;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class InitSearchDataCommand extends HyperfCommand
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        parent::__construct('init:search');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('初始化数据');
    }

    public function handle()
    {
        $client = di()->get(ClientFactory::class)->create();

        $info = $client->info();
        $version = $info['version']['number'];
        $this->output->writeln('Version: ' . $version);

        $params = [
            'index' => Indices::INDEX,
        ];
        $indices = $client->indices();
        if ($indices->exists($params)) {
            $indices->delete($params);
        }

        $indices->create($params);

        $mapping = [
            'id' => ['type' => 'long'],
            'title' => ['type' => 'keyword'],
            'type' => ['type' => 'byte'],
            'body' => ['type' => 'text'],
        ];

        $params = [
            'index' => Indices::INDEX,
            'body' => [
                'properties' => $mapping,
            ],
        ];

        if (version_compare($version, '7.0.0', '<')) {
            $params['type'] = Indices::TYPE;
        }

        $indices->putMapping($params);

        $docs = [
            [
                'id' => 1,
                'title' => 'Hyperf',
                'type' => 0,
                'body' => 'Hyperf 是基于 `Swoole 4.5+` 实现的高性能、高灵活性的 PHP 持久化框架',
            ],
            [
                'id' => 2,
                'title' => 'Phalcon',
                'type' => 0,
                'body' => 'Phalcon 是基于 Zephir 实现的 C 扩展 PHP 框架',
            ],
            [
                'id' => 3,
                'title' => 'Laravel',
                'type' => 0,
                'body' => 'Laravel 是坐拥 50k star 的 PHP 框架',
            ],
            [
                'id' => 4,
                'title' => 'PHP',
                'type' => 1,
                'body' => 'PHP 是世界上最好的语言',
            ],
            [
                'id' => 5,
                'title' => 'Java',
                'type' => 1,
                'body' => 'Java 是生态最好的语言',
            ],
        ];

        $params = [
            'index' => Indices::INDEX,
        ];

        if (version_compare($version, '7.0.0', '<')) {
            $params['type'] = Indices::TYPE;
        }

        foreach ($docs as $doc) {
            $client->update(
                $params + [
                    'id' => $doc['id'],
                    'body' => [
                        'doc' => $doc,
                        'doc_as_upsert' => true,
                    ],
                    'refresh' => true,
                    'retry_on_conflict' => 5,
                ]
            );
        }
    }
}
