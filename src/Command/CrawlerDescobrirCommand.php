<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\UrlWebsite;
use App\Repository\UrlWebsiteRepository;
use League\Uri\Uri;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'crawler:descobrir',
    description: '',
)]
class CrawlerDescobrirCommand extends Command
{
    public function __construct(
        private UrlWebsiteRepository $urlWebsiteRepository,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        foreach ($this->obterUrlsDoSitemap() as $url) {
            $this->urlWebsiteRepository->save($url, true);
        }

        return self::SUCCESS;
    }

    /** @return \Generator<UrlWebsite> */
    private function obterUrlsDoSitemap(): \Generator
    {
        $http = HttpClient::createForBaseUri('https://codamos.com.br');

        $res = $http->request('GET', '/sitemap.xml');
        if (200 !== $res->getStatusCode()) {
            throw new \RuntimeException('Não foi possível carregar o sitemap.');
        }

        $xml = $res->getContent();
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        foreach ($dom->getElementsByTagName('url') as $urlTag) {
            $urlCanonica = trim($urlTag->getElementsByTagName('loc')->item(0)->textContent);
            $uri = Uri::createFromString($urlCanonica);

            $url = $this->urlWebsiteRepository->findOneBy(['urlCanonica' => $urlCanonica]) ?? new UrlWebsite();
            $url->urlCanonica = $uri->toString();
            $url->dominio = $uri->getHost();
            $url->prioridade ??= 0;

            yield $url;
        }
    }
}
