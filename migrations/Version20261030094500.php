<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261030094500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create the initial Symfony third-party registry.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE app_third_party (
                id INT AUTO_INCREMENT NOT NULL,
                name VARCHAR(180) NOT NULL,
                category VARCHAR(20) NOT NULL,
                email VARCHAR(180) DEFAULT NULL,
                phone VARCHAR(40) DEFAULT NULL,
                created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
                INDEX idx_third_party_category_name (category, name),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
            SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE app_third_party');
    }
}
