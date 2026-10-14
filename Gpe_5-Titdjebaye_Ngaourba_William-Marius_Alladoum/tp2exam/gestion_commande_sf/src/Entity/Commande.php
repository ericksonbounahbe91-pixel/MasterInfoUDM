<?php

namespace App\Entity;

use App\Repository\CommandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CommandeRepository::class)]
#[ORM\Table(name: 'cmd_commande')]
// Index sur la date : accélère le filtre par période du tableau de bord
#[ORM\Index(name: 'idx_commande_date', columns: ['date_commande'])]
class Commande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: false)]
    #[Assert\NotNull(message: 'Choisissez une date.')]
    private ?\DateTimeImmutable $dateCommande = null;

    #[ORM\ManyToOne(inversedBy: 'commandes')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: 'Choisissez un client.')]
    private ?Client $client = null;

    /** @var Collection<int, LigneCommande> */
    #[ORM\OneToMany(targetEntity: LigneCommande::class, mappedBy: 'commande', orphanRemoval: true)]
    #[ORM\OrderBy(['id' => 'ASC'])]
    private Collection $lignes;

    public function __construct()
    {
        $this->lignes = new ArrayCollection();
    }

    public function getId(): ?int { return $this->id; }

    public function getDateCommande(): ?\DateTimeImmutable { return $this->dateCommande; }
    public function setDateCommande(?\DateTimeImmutable $dateCommande): static { $this->dateCommande = $dateCommande; return $this; }

    public function getClient(): ?Client { return $this->client; }
    public function setClient(?Client $client): static { $this->client = $client; return $this; }

    /** @return Collection<int, LigneCommande> */
    public function getLignes(): Collection { return $this->lignes; }

    public function getTotal(): int
    {
        $total = 0;
        foreach ($this->lignes as $ligne) {
            $total += $ligne->getSousTotal();
        }
        return $total;
    }
}
