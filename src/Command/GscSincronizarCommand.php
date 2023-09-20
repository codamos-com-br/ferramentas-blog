<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\TrafegoGoogle;
use App\Entity\Types\DateKey;
use App\Repository\TrafegoGoogleRepository;
use DateTimeImmutable;
use Symfony\Component\Console\Input\InputOption;
use Google\Client;
use Google\Service\SearchConsole;
use Google\Service\SearchConsole\SearchAnalyticsQueryRequest;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'gsc:sincronizar',
    description: '',
)]
class GscSincronizarCommand extends Command
{
    private SearchConsole $searchConsoleClient;

    public function __construct(
        private Client $client,
        private TrafegoGoogleRepository $trafegoGoogleRepository,
    ) {
        parent::__construct();

        $this->searchConsoleClient = new SearchConsole($this->client);
    }

    protected function configure(): void
    {
        $this
            ->addOption('data_inicio', 'i', InputOption::VALUE_OPTIONAL, 'Desde quando buscar dados', 'today -3 months')
            ->addOption('data_fim', 'f', InputOption::VALUE_OPTIONAL, 'Até quando buscar dados', 'today')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $domain = 'sc-domain:codamos.com.br';

        $inicio = (string) $input->getOption('data_inicio');
        $fim = (string) $input->getOption('data_fim');

        $req = new SearchAnalyticsQueryRequest();
        $req->setStartDate((new DateTimeImmutable($inicio))->format('Y-m-d'));
        $req->setEndDate((new DateTimeImmutable($fim))->format('Y-m-d'));
        $req->setDimensions([
            'page',
            'date',
        ]);

        $res = $this->searchConsoleClient
            ->searchanalytics
            ->query($domain, $req)
            ->getRows();

        foreach ($res as $row) {
            [$url, $dataStr] = $row->getKeys();
            $data = new DateTimeImmutable($dataStr);
            $obj = $this->trafegoGoogleRepository->findOneBy([
                'url' => $url,
                'data' => $data,
            ]) ?? TrafegoGoogle::fromGoogleSearchConsoleResultRow($row);

            $this->trafegoGoogleRepository->save($obj, true);
        }

        return self::SUCCESS;
    }
}
