<?php

namespace App\Command;

interface EsUseCaseInterface
{
    /**
     * @return string Index name
     */
    public function indexName(): string;

    /**
     * @return array Index definition including settings or mappings
     */
    public function indexDefinition(): array;

    /**
     * @param int $amount Amount of items to generate
     * @return iterable<array>
     */
    public function generateItems(int $amount): iterable;

    /**
     * @param string $input Some user input
     * @return array Query body
     */
    public function search(string $input): array;
}
