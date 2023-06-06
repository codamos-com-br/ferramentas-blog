<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UrlWebsiteRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UrlWebsiteRepository::class)]
class UrlWebsite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(length: 255)]
    public ?string $urlCanonica = null;

    #[ORM\Column(length: 255)]
    public ?string $dominio = null;

    #[ORM\Column(nullable: true)]
    public ?\DateTimeImmutable $ultimaVisita = null;

    #[ORM\Column]
    public ?int $prioridade = null;
}
