<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240716200534 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_friend_requests (sender_id INT NOT NULL, receiver_id INT NOT NULL, INDEX IDX_FEBFDC94F624B39D (sender_id), INDEX IDX_FEBFDC94CD53EDB6 (receiver_id), PRIMARY KEY(sender_id, receiver_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_friend_requests ADD CONSTRAINT FK_FEBFDC94F624B39D FOREIGN KEY (sender_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_friend_requests ADD CONSTRAINT FK_FEBFDC94CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_friend_requests DROP FOREIGN KEY FK_FEBFDC94F624B39D');
        $this->addSql('ALTER TABLE user_friend_requests DROP FOREIGN KEY FK_FEBFDC94CD53EDB6');
        $this->addSql('DROP TABLE user_friend_requests');
    }
}
