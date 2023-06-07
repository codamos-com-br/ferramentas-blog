<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ArquivoWebRepository;
use App\Repository\PaginaWebsiteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'relatorio:paginas-pesadas',
    description: '',
)]
class RelatorioPaginasPesadasCommand extends Command
{
    public function __construct(
        private PaginaWebsiteRepository $paginaWebsiteRepository,
        private ArquivoWebRepository $arquivoWebRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('threshold', 't', InputOption::VALUE_OPTIONAL, 'Valor mínimo considerado pesado (em Kb)', 100)
            ->addOption('format', null, InputOption::VALUE_OPTIONAL, 'Formato de saída: CSV ou PRETTY', 'pretty')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $threshold = (int) $input->getOption('threshold') * 1024; // 100kb é o valor padrão
        $arquivos = $this->arquivoWebRepository->findHeavyFiles($threshold);

        $csv = [];
        $csv[] = ['url', 'tamanho_bytes', 'tamanho_bytes_gz', 'pct_compressao', 'tempo_download_ms', 'url_fonte'];
        foreach ($arquivos as $arquivo) {
            $tamanhoGz = strlen($arquivo->conteudoGzip ?? '');
            $tamanhoBytes = $arquivo->tamanhoBytes ?? 0;
            $pctCompressao = number_format(100 - ($tamanhoGz / $tamanhoBytes * 100), 2, '.');
            $csv[] = [
                $arquivo->urlCanonica,
                $tamanhoBytes,
                $tamanhoGz,
                $pctCompressao,
                $arquivo->tempoDownloadMs,
                $arquivo->getFonte()?->urlCanonica,
            ];
        }

        $format = (string) $input->getOption('format');

        return match ($format) {
            'pretty' => $this->printPretty($io, $csv),
            'csv' => $this->printCsv($io, $csv),
            default => $this->printInvalidFormatError($io, $format),
        };
    }

    /**
     * @param array<array-key, array<string|int|float|null>> $csv
     */
    private function printPretty(SymfonyStyle $io, array $csv): int
    {
        $cabecalho = array_shift($csv);
        $io->table($cabecalho, $csv);

        return self::SUCCESS;
    }

    /**
     * @param array<array-key, array<string|int|float|null>> $csv
     */
    private function printCsv(SymfonyStyle $io, array $csv): int
    {
        $cabecalho = array_shift($csv);
        $io->writeln(implode('; ', $cabecalho));
        foreach ($csv as $row) {
            $row = array_map('strval', $row);
            $row = array_map([$this, 'adicionarAspas'], $row);
            $io->writeln(implode('; ', $row));
        }

        return self::SUCCESS;
    }

    private function printInvalidFormatError(SymfonyStyle $io, string $format): int
    {
        $io->error("Formato {$format} inválido.");

        return self::FAILURE;
    }

    private function adicionarAspas(string $texto): string
    {
        $texto = str_replace('"', '\"', $texto);

        return "\"{$texto}\"";
    }
}
