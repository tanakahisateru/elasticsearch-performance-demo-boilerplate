<?php

namespace App\Command;

use Elastic\Elasticsearch\Client as EsClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:es-fill',
    description: 'Fill data into ES index',
)]
class EsFillCommand extends Command
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
            ->addOption('amount', 'a', InputOption::VALUE_OPTIONAL, 'number of amount', 1000)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $amount = $input->getOption('amount');

        $indexName = $this->case->indexName();

        $indices = $this->client->indices();
        if ($indices->exists(['index' => $indexName])->asBool()) {
            $indices->delete(['index' => $indexName]);
        }
        $indices->create([
            'index' => $indexName,
            'body' => $this->case->indexDefinition(),
        ]);

        $this->fill($amount, $io);

        return Command::SUCCESS;
    }

    private function fill(int $amount, OutputStyle $io)
    {
        $io->progressStart($amount);

        $buffer = [];
        foreach ($this->case->generateItems($amount) as $item) {
            $buffer[] = [
                'index' => [
                    '_index' => $this->case->indexName(),
                ]
            ];
            $buffer[] = $item;

            if (count($buffer) > 200) {
                $this->client->bulk(['body' => $buffer]);
                $buffer = [];
            }

            $io->progressAdvance();
        }
        if (!empty($buffer)) {
            $this->client->bulk(['body' => $buffer]);
        }

        $io->progressFinish();
    }
}
