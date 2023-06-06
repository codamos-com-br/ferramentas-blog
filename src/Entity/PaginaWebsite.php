<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaginaWebsiteRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PaginaWebsiteRepository::class)]
class PaginaWebsite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $urlCanonica = null;

    #[ORM\Column(length: 255)]
    public ?string $dominio = null;

    #[ORM\Column]
    public ?\DateTimeImmutable $ultimaVisita = null;

    #[ORM\Column(type: Types::TEXT)]
    public ?string $conteudoGzip = null;
}
