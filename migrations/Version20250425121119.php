<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250425121119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE publication_has_language (id INT AUTO_INCREMENT NOT NULL, publication_id INT NOT NULL, language_id INT NOT NULL, title VARCHAR(255) NOT NULL, quantity INT NOT NULL, sales_price INT NOT NULL, acquisition_cost INT NOT NULL, INDEX IDX_846E924438B217A7 (publication_id), INDEX IDX_846E924482F1BAF4 (language_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publication_has_language ADD CONSTRAINT FK_846E924438B217A7 FOREIGN KEY (publication_id) REFERENCES publication (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publication_has_language ADD CONSTRAINT FK_846E924482F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE publication_has_language DROP FOREIGN KEY FK_846E924438B217A7
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE publication_has_language DROP FOREIGN KEY FK_846E924482F1BAF4
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE publication_has_language
        SQL);
    }
}
