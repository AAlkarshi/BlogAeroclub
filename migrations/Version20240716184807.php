<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716184807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_friends (user_id INT NOT NULL, friend_user_id INT NOT NULL, INDEX IDX_79E36E63A76ED395 (user_id), INDEX IDX_79E36E6393D1119E (friend_user_id), PRIMARY KEY(user_id, friend_user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E63A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_friends ADD CONSTRAINT FK_79E36E6393D1119E FOREIGN KEY (friend_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E63A76ED395');
        $this->addSql('ALTER TABLE user_friends DROP FOREIGN KEY FK_79E36E6393D1119E');
        $this->addSql('DROP TABLE user_friends');
    }
}
