<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PaginaWebsiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    #[ORM\OneToMany(mappedBy: 'fonte', targetEntity: ArquivoWeb::class, orphanRemoval: true, cascade: ['all'])]
    public Collection $arquivos;

    public function __construct()
    {
        $this->arquivos = new ArrayCollection();
    }

    public function arquivos(): Collection
    {
        return $this->arquivos;
    }

    public function adicionarArquivo(ArquivoWeb $arquivo): self
    {
        if (false === $this->arquivos->contains($arquivo)) {
            $this->arquivos->add($arquivo);
            $arquivo->setFonte($this);
        }

        return $this;
    }

    public function removerArquivo(ArquivoWeb $arquivo): self
    {
        if ($this->arquivos->removeElement($arquivo)) {
            // set the owning side to null (unless already changed)
            if ($arquivo->getFonte() === $this) {
                $arquivo->setFonte(null);
            }
        }

        return $this;
    }
}
