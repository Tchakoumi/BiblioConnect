<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Publication;
use App\Entity\Author;
use App\Entity\Category;
use App\Entity\Theme;
use App\Entity\PublicationHasLanguage;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Publication entity
 */
class PublicationTest extends TestCase
{
    /**
     * Test creating a new publication and setting properties
     */
    public function testCreatePublication(): void
    {
        // Create a new publication
        $publication = new Publication();

        // Initially all properties should be null
        $this->assertNull($publication->getId());
        $this->assertNull($publication->getAuthor());
        $this->assertNull($publication->getCategory());
        $this->assertNull($publication->getTheme());

        // The collection of publication languages should be empty
        $this->assertCount(0, $publication->getPublicationHasLanguages());

        // Create related entities
        $author = new Author();
        $author->setFullname('Jane Austen');

        $category = new Category();
        $category->setName('Classic Literature');

        $theme = new Theme();
        $theme->setTitle('Romance');

        // Set properties
        $publication->setAuthor($author);
        $publication->setCategory($category);
        $publication->setTheme($theme);

        // Check that getters return the values we set
        $this->assertSame($author, $publication->getAuthor());
        $this->assertSame($category, $publication->getCategory());
        $this->assertSame($theme, $publication->getTheme());

        // Test fluent interface (method chaining)
        $this->assertSame($publication, $publication->setAuthor($author));
        $this->assertSame($publication, $publication->setCategory($category));
        $this->assertSame($publication, $publication->setTheme($theme));
    }

    /**
     * Test adding and removing language editions
     */
    public function testAddRemovePublicationLanguages(): void
    {
        // Create a publication
        $publication = new Publication();

        // Create a publication language
        $publicationLanguage = new PublicationHasLanguage();
        $publicationLanguage->setTitle('Pride and Prejudice');

        // Add the language edition to the publication
        $publication->addPublicationHasLanguage($publicationLanguage);

        // Check that it was added
        $this->assertCount(1, $publication->getPublicationHasLanguages());
        $this->assertTrue($publication->getPublicationHasLanguages()->contains($publicationLanguage));
        $this->assertSame($publication, $publicationLanguage->getPublication());

        // Check that adding the same language edition again doesn't duplicate it
        $publication->addPublicationHasLanguage($publicationLanguage);
        $this->assertCount(1, $publication->getPublicationHasLanguages());

        // Remove the language edition
        $publication->removePublicationHasLanguage($publicationLanguage);

        // Check that it was removed
        $this->assertCount(0, $publication->getPublicationHasLanguages());
        $this->assertFalse($publication->getPublicationHasLanguages()->contains($publicationLanguage));
    }
}