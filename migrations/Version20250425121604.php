<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425121604 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE rating (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, publication_has_language_id INT NOT NULL, comment LONGTEXT NOT NULL, rating INT NOT NULL, INDEX IDX_D8892622A76ED395 (user_id), INDEX IDX_D8892622F8AF446F (publication_has_language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rating ADD CONSTRAINT FK_D8892622A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rating ADD CONSTRAINT FK_D8892622F8AF446F FOREIGN KEY (publication_has_language_id) REFERENCES publication_has_language (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE rating DROP FOREIGN KEY FK_D8892622A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE rating DROP FOREIGN KEY FK_D8892622F8AF446F
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rating
        SQL);
    }
}
