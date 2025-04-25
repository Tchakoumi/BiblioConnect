<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $created_at = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    /**
     * @var Collection<int, ReservationPublication>
     */
    #[ORM\OneToMany(targetEntity: ReservationPublication::class, mappedBy: 'reservation', orphanRemoval: true)]
    private Collection $reservationPublications;

    public function __construct()
    {
        $this->reservationPublications = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

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
            $reservationPublication->setReservation($this);
        }

        return $this;
    }

    public function removeReservationPublication(ReservationPublication $reservationPublication): static
    {
        if ($this->reservationPublications->removeElement($reservationPublication)) {
            // set the owning side to null (unless already changed)
            if ($reservationPublication->getReservation() === $this) {
                $reservationPublication->setReservation(null);
            }
        }

        return $this;
    }
}
