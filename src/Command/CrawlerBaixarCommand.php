<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\PaginaWebsite;
use App\Repository\PaginaWebsiteRepository;
use App\Repository\UrlWebsiteRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'crawler:baixar',
    description: '',
)]
class CrawlerBaixarCommand extends Command
{
    public function __construct(
        private UrlWebsiteRepository $urlWebsiteRepository,
        private PaginaWebsiteRepository $paginaWebsiteRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('idade', InputArgument::OPTIONAL, 'Limite de idade em dias', 5);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $idade = (int) $input->getArgument('idade');
        $agora = new \DateTimeImmutable("today -{$idade} days");

        $urls = $this->urlWebsiteRepository->findUrlsOlderThan($agora);
        foreach ($urls as $url) {
            $baseUri = $url->toUri();
            $http = HttpClient::createForBaseUri((string) $baseUri);
            $res = $http->request('GET', (string) $url->urlCanonica);

            $content = $res->getContent();
            $contentGz = gzcompress($content, 9);

            $url->ultimaVisita = new \DateTimeImmutable();
            $this->urlWebsiteRepository->save($url, true);

            $pagina = $this->paginaWebsiteRepository->findOneBy([
                'urlCanonica' => $url->urlCanonica,
            ]) ?? new PaginaWebsite();
            $pagina->urlCanonica = $url->urlCanonica;
            $pagina->ultimaVisita = $url->ultimaVisita;
            $pagina->dominio = $url->dominio;
            $pagina->conteudoGzip = $contentGz;
            $this->paginaWebsiteRepository->save($pagina, true);
        }

        return self::SUCCESS;
    }
}
