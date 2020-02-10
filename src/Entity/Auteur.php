<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="App\Repository\AuteurRepository")
 */
class Auteur
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"listeAuteurSimple"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"listeGenreComplete"})
     * @Groups({"listeAuteurSimple", "listeAuteurComplete"})
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"listeGenreComplete"})
     * @Groups({"listeAuteurSimple", "listeAuteurComplete"})
     */
    private $prenom;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Nationalite", inversedBy="auteurs")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"listeGenreComplete"})
     * @Groups({"listeAuteurComplete"})
     */
    private $nationalite;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Livre", mappedBy="auteur")
     * @Groups({"listeAuteurComplete"})
     */
    private $livres;

    public function __construct()
    {
        $this->livres = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getnationalite(): ?Nationalite
    {
        return $this->nationalite;
    }

    public function setnationalite(?Nationalite $relation): self
    {
        $this->nationalite = $relation;

        return $this;
    }

    /**
     * @return Collection|Livre[]
     */
    public function getLivres(): Collection
    {
        return $this->livres;
    }

    public function addLivre(Livre $livre): self
    {
        if (!$this->livres->contains($livre)) {
            $this->livres[] = $livre;
            $livre->setAuteur($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): self
    {
        if ($this->livres->contains($livre)) {
            $this->livres->removeElement($livre);
            // set the owning side to null (unless already changed)
            if ($livre->getAuteur() === $this) {
                $livre->setAuteur(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->nom . " " . $this->prenom;
    }
}
