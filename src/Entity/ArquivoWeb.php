<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ArquivoWebRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArquivoWebRepository::class)]
class ArquivoWeb
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $urlCanonica = null;

    #[ORM\Column]
    public ?int $respostaHttp = null;

    #[ORM\Column]
    public ?int $tamanhoBytes = null;

    #[ORM\Column]
    public ?int $tempoDownloadMs = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    public ?string $conteudoGzip = null;

    #[ORM\ManyToOne(inversedBy: 'arquivos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PaginaWebsite $fonte = null;

    public function getFonte(): ?PaginaWebsite
    {
        return $this->fonte;
    }

    public function setFonte(?PaginaWebsite $fonte): self
    {
        $this->fonte = $fonte;

        return $this;
    }
}
