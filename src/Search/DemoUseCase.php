<?php

namespace App\Search;

use App\Command\EsUseCaseInterface;
use Faker\Factory as FakerFactory;

class DemoUseCase implements EsUseCaseInterface
{
    public function indexName(): string
    {
        return 'demo';
    }

    public function indexDefinition(): array
    {
        $properties = [
            'user' => ['type' => 'keyword'],
            'message' => ['type' => 'text'],
            'timestamp' => ['type' => 'long'],
        ];

        return [
            // 'settings' => [],
            'mappings' => [
                "dynamic" => false,
                'properties' => $properties,
            ],
        ];
    }

    public function generateItems(int $amount): iterable
    {
        $faker = FakerFactory::create();

        for ($i = 0; $i < $amount; $i++) {
            yield [
                'user' => $faker->userName(),
                'message' => $faker->realText(),
                'timestamp' => $faker->unixTime(),
            ];
        }
    }

    public function search(string $input): array
    {
        $query = [
            'bool' => [
                'should' => [
                    ['match_phrase' => ['message' => $input]],
                    ['term' => ['user' => $input]],
                ],
                'minimum_should_match' => 1,
            ]
        ];

        $sort = [
            'timestamp' => 'desc',
        ];

        return [
            'query' => $query,
            'sort' => $sort,
        ];
    }
}