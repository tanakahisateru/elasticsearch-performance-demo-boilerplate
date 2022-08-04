<?php

namespace App\Command;

use Elastic\Elasticsearch\Client as EsClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:es-query',
    description: 'Query ES by text',
)]
class EsQueryCommand extends Command
{
    public function __construct(
        private readonly EsClient $client,
        private readonly EsUseCaseInterface $case
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('query', InputArgument::REQUIRED, 'search word')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $input = $input->getArgument('query');

        $searchBody = $this->case->search($input);
        $io->writeln(json_encode($searchBody));

        $r = $this->client->search([
            'index' => $this->case->indexName(),
            'body' => $searchBody,
        ])->asArray();

        // $io->info(json_encode($r));
        $io->writeln("Took:" . $r['took'] . 'ms');
        $io->writeln("Hits:" . $r['hits']['total']['value']);

        return Command::SUCCESS;
    }
}
