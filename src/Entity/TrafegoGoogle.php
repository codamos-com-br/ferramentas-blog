<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\TrafegoGoogleRepository;
use App\Entity\Types\DateKey;
use Doctrine\ORM\Mapping as ORM;
use Google\Service\SearchConsole\ApiDataRow;

#[ORM\Entity(repositoryClass: TrafegoGoogleRepository::class)]
class TrafegoGoogle
{
    #[ORM\Id]
    #[ORM\Column(type: 'date_key', nullable: false)]
    public DateKey $data;

    #[ORM\Id]
    #[ORM\Column(type: 'string', nullable: false)]
    public string $url;

    #[ORM\Column]
    public ?int $cliques = null;

    #[ORM\Column]
    public ?float $ctr = null;

    #[ORM\Column]
    public ?int $impressoes = null;

    #[ORM\Column]
    public ?float $posicao = null;

    public static function fromGoogleSearchConsoleResultRow(ApiDataRow $row): self
    {
        $instancia = new self();
        [$url, $dataStr] = $row->getKeys();

        $instancia->url = $url;
        $instancia->data = new DateKey($dataStr);
        $instancia->ctr = $row->ctr;
        $instancia->impressoes = $row->impressions;
        $instancia->posicao = $row->position;
        $instancia->cliques = $row->clicks;

        return $instancia;
    }
}
