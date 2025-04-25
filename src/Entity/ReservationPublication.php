<?php

namespace App\Entity;

use App\Repository\ReservationPublicationRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationPublicationRepository::class)]
class ReservationPublication
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $return_date = null;

    #[ORM\ManyToOne(inversedBy: 'reservationPublications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reservation $reservation = null;

    #[ORM\ManyToOne(inversedBy: 'reservationPublications')]
    #[ORM\JoinColumn(nullable: false)]
    private ?PublicationHasLanguage $publication_has_language = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReturnDate(): ?\DateTimeInterface
    {
        return $this->return_date;
    }

    public function setReturnDate(\DateTimeInterface $return_date): static
    {
        $this->return_date = $return_date;

        return $this;
    }

    public function getReservation(): ?Reservation
    {
        return $this->reservation;
    }

    public function setReservation(?Reservation $reservation): static
    {
        $this->reservation = $reservation;

        return $this;
    }

    public function getPublicationHasLanguage(): ?PublicationHasLanguage
    {
        return $this->publication_has_language;
    }

    public function setPublicationHasLanguage(?PublicationHasLanguage $publication_has_language): static
    {
        $this->publication_has_language = $publication_has_language;

        return $this;
    }
}
