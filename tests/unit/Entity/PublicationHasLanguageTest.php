<?php

namespace App\Tests\Unit\Entity;

use App\Entity\PublicationHasLanguage;
use App\Entity\Publication;
use App\Entity\Language;
use App\Entity\Rating;
use App\Entity\ReservationPublication;
use PHPUnit\Framework\TestCase;
use Doctrine\Common\Collections\Collection;

/**
 * Unit test for PublicationHasLanguage entity (represents a book in a specific language)
 */
class PublicationHasLanguageTest extends TestCase
{
    /**
     * Test creating a book and setting basic properties
     */
    public function testCreateBook(): void
    {
        // Create a new book (PublicationHasLanguage)
        $book = new PublicationHasLanguage();

        // Initially properties should be null
        $this->assertNull($book->getId());
        $this->assertNull($book->getTitle());
        $this->assertNull($book->getQuantity());
        $this->assertNull($book->getSalesPrice());
        $this->assertNull($book->getAcquisitionCost());
        $this->assertNull($book->getPublication());
        $this->assertNull($book->getLanguage());

        // Set basic properties
        $book->setTitle('Pride and Prejudice');
        $book->setQuantity(10);
        $book->setSalesPrice(1499); // 14.99 stored as integer cents
        $book->setAcquisitionCost(899); // 8.99 stored as integer cents

        // Check that getters return the values we set
        $this->assertEquals('Pride and Prejudice', $book->getTitle());
        $this->assertEquals(10, $book->getQuantity());
        $this->assertEquals(1499, $book->getSalesPrice());
        $this->assertEquals(899, $book->getAcquisitionCost());

        // Test fluent interface (method chaining)
        $this->assertSame($book, $book->setTitle('New Title'));
        $this->assertSame($book, $book->setQuantity(5));
        $this->assertSame($book, $book->setSalesPrice(1999));
        $this->assertSame($book, $book->setAcquisitionCost(999));
    }

    /**
     * Test setting a book's publication and language
     */
    public function testBookPublicationAndLanguage(): void
    {
        // Create entities
        $book = new PublicationHasLanguage();
        $publication = new Publication();
        $language = new Language();
        $language->setName('English');

        // Set publication and language
        $book->setPublication($publication);
        $book->setLanguage($language);

        // Check that getters return the values we set
        $this->assertSame($publication, $book->getPublication());
        $this->assertSame($language, $book->getLanguage());

        // The book is NOT automatically added to the publication's collection
        // in the setPublication method. We need to use addPublicationHasLanguage
        // from the Publication class to establish the bidirectional relationship.
        $publication->addPublicationHasLanguage($book);

        // Now we can test if the book is in the collection
        $this->assertTrue($publication->getPublicationHasLanguages()->contains($book));

        // Test fluent interface
        $this->assertSame($book, $book->setPublication($publication));
        $this->assertSame($book, $book->setLanguage($language));
    }

    /**
     * Test that ratings collection is initialized
     */
    public function testRatingsCollection(): void
    {
        $book = new PublicationHasLanguage();

        // The ratings collection should be empty but initialized
        $this->assertInstanceOf(Collection::class, $book->getRatings());
        $this->assertCount(0, $book->getRatings());

        // Test adding a rating
        $rating = new Rating();
        $book->addRating($rating);

        $this->assertCount(1, $book->getRatings());
        $this->assertTrue($book->getRatings()->contains($rating));
        $this->assertSame($book, $rating->getPublicationHasLanguage());
    }
}