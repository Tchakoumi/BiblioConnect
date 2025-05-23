<?php

namespace App\Entity;

use App\Repository\PublicationHasLanguageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PublicationHasLanguageRepository::class)]
class PublicationHasLanguage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?int $sales_price = null;

    #[ORM\Column]
    private ?int $acquisition_cost = null;

    #[ORM\ManyToOne(inversedBy: 'publicationHasLanguages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Publication $publication = null;

    #[ORM\ManyToOne(inversedBy: 'publicationHasLanguages')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Language $language = null;

    /**
     * @var Collection<int, Rating>
     */
    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'publicationHasLanguage', orphanRemoval: true)]
    private Collection $ratings;

    /**
     * @var Collection<int, ReservationPublication>
     */
    #[ORM\OneToMany(targetEntity: ReservationPublication::class, mappedBy: 'publication_has_language', orphanRemoval: true)]
    private Collection $reservationPublications;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->reservationPublications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getSalesPrice(): ?int
    {
        return $this->sales_price;
    }

    public function setSalesPrice(int $sales_price): static
    {
        $this->sales_price = $sales_price;

        return $this;
    }

    public function getAcquisitionCost(): ?int
    {
        return $this->acquisition_cost;
    }

    public function setAcquisitionCost(int $acquisition_cost): static
    {
        $this->acquisition_cost = $acquisition_cost;

        return $this;
    }

    public function getPublication(): ?Publication
    {
        return $this->publication;
    }

    public function setPublication(?Publication $publication): static
    {
        $this->publication = $publication;

        return $this;
    }

    public function getLanguage(): ?Language
    {
        return $this->language;
    }

    public function setLanguage(?Language $language): static
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): static
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setPublicationHasLanguage($this);
        }

        return $this;
    }

    public function removeRating(Rating $rating): static
    {
        if ($this->ratings->removeElement($rating)) {
            // set the owning side to null (unless already changed)
            if ($rating->getPublicationHasLanguage() === $this) {
                $rating->setPublicationHasLanguage(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, ReservationPublication>
     */
    public function getReservationPublications(): Collection
    {
        return $this->reservationPublications;
    }

    public function addReservationPublication(ReservationPublication $reservationPublication): static
    {
        if (!$this->reservationPublications->contains($reservationPublication)) {
            $this->reservationPublications->add($reservationPublication);
            $reservationPublication->setPublicationHasLanguage($this);
        }

        return $this;
    }

    public function removeReservationPublication(ReservationPublication $reservationPublication): static
    {
        if ($this->reservationPublications->removeElement($reservationPublication)) {
            // set the owning side to null (unless already changed)
            if ($reservationPublication->getPublicationHasLanguage() === $this) {
                $reservationPublication->setPublicationHasLanguage(null);
            }
        }

        return $this;
    }
}
