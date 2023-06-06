<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\ArquivoWeb;
use App\Entity\PaginaWebsite;
use App\Entity\UrlWebsite;
use App\Repository\ArquivoWebRepository;
use App\Repository\PaginaWebsiteRepository;
use App\Repository\UrlWebsiteRepository;
use League\Uri\Contracts\UriInterface;
use League\Uri\Uri;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DomCrawler\Crawler;
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
        private ArquivoWebRepository $arquivoWebRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('idade', InputArgument::OPTIONAL, 'Limite de idade em dias', 5)
            ->addOption('force', 'f', InputOption::VALUE_NEGATABLE, 'Baixar todas as páginas, independentemente de sua idade', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $obterTodasPaginas = (bool) $input->getOption('force');
        $idade = (int) $input->getArgument('idade');
        $agora = new \DateTimeImmutable("today -{$idade} days");

        $urls = $obterTodasPaginas
            ? $this->urlWebsiteRepository->findAll()
            : $this->urlWebsiteRepository->findUrlsOlderThan($agora);

        foreach ($urls as $url) {
            $this->baixarPagina($url);
        }

        return self::SUCCESS;
    }

    public function baixarPagina(UrlWebsite $url): void
    {
        $baseUri = $url->toUri();
        $http = HttpClient::createForBaseUri((string) $baseUri);

        // Baixar página
        $res = $http->request('GET', (string) $url->urlCanonica);
        $conteudo = $res->getContent();

        $url->ultimaVisita = new \DateTimeImmutable();
        $this->urlWebsiteRepository->save($url, true);

        $pagina = $this->paginaWebsiteRepository->findOneBy([
            'urlCanonica' => $url->urlCanonica,
        ]) ?? new PaginaWebsite();
        $pagina->urlCanonica = $url->urlCanonica;
        $pagina->ultimaVisita = $url->ultimaVisita;
        $pagina->dominio = $url->dominio;

        // Apagar todos arquivos anteriores
        /** @var ArquivoWeb $arq */
        foreach ($pagina->arquivos() as $arq) {
            $pagina->removerArquivo($arq);
        }

        // Descobrir arquivos
        $urlsArquivos = array_merge(
            [$baseUri],
            $this->descobrirUrls($conteudo),
        );
        foreach ($urlsArquivos as $url) {
            $inicioDownload = microtime(true);
            $res = $http->request('GET', (string) $url);
            $conteudo = $res->getContent();
            $fimDownload = microtime(true);
            $downloadMs = (int) floor(($fimDownload - $inicioDownload) * 1000);

            $conteudoGz = gzcompress($conteudo, 9);

            $arq = $this->arquivoWebRepository->findOneBy([
                'urlCanonica' => (string) $url,
            ]) ?? new ArquivoWeb();
            $arq->urlCanonica = (string) $url;
            $arq->respostaHttp = $res->getStatusCode();
            $arq->conteudoGzip = $conteudoGz;
            $arq->tamanhoBytes = strlen($conteudo);
            $arq->tempoDownloadMs = $downloadMs;

            $pagina->adicionarArquivo($arq);
        }

        $this->paginaWebsiteRepository->save($pagina, true);
    }

    /** @return list<UriInterface> */
    private function descobrirUrls(string $conteudo): array
    {
        $crawler = new Crawler($conteudo);
        $urls = [];
        foreach ($crawler->filter('img[src]') as $img) {
            /** @var \DOMNamedNodeMap $attrs */
            $attrs = $img->attributes;
            $imageUrl = trim((string) $attrs->getNamedItem('src')?->textContent);

            if (str_starts_with($imageUrl, 'data:image')) {
                continue;
            }

            $urls[] = Uri::createFromString($imageUrl);
        }

        return $urls;
    }
}
