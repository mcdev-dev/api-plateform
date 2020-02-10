<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\GenreRepository")
 * @UniqueEntity(
 *     fields = {"libelle"},
 *     message = "Le libelle existe déjà"
 * )
 */
class Genre
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     * @Groups({"listeGenreSimple", "listeGenreComplete"})
     * @Groups({"listeAuteurSimple", "listeAuteurComplete"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"listeGenreSimple", "listeGenreComplete"})
     * @Groups({"listeAuteurSimple", "listeAuteurComplete"})
     * @Assert\Length(
     *     min = 2,
     *     max = 20,
     *     minMessage = "Le libelle doit comporter au minimum {{ limit }} caractéres",
     *     maxMessage = "Le libelle doit comporter au maximum {{ limit }} caractéres "
     * )
     */
    private $libelle;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Livre", mappedBy="genre")
     * @Groups({"listeGenreComplete"})
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

    public function getlibelle(): ?string
    {
        return $this->libelle;
    }

    public function setlibelle(string $libelle): self
    {
        $this->libelle = $libelle;

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
            $livre->setGenre($this);
        }

        return $this;
    }

    public function removeLivre(Livre $livre): self
    {
        if ($this->livres->contains($livre)) {
            $this->livres->removeElement($livre);
            // set the owning side to null (unless already changed)
            if ($livre->getGenre() === $this) {
                $livre->setGenre(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->libelle;
    }
}
