<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250401144218 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seance CHANGE burned_calories burned_calories INT DEFAULT NULL, CHANGE body_temperature_delta body_temperature_delta VARCHAR(10) DEFAULT NULL, CHANGE muscle_fatigue muscle_fatigue INT DEFAULT NULL, CHANGE heart_recovery_rate heart_recovery_rate INT DEFAULT NULL, CHANGE heartbeat_variation heartbeat_variation VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE seance CHANGE burned_calories burned_calories INT NOT NULL, CHANGE body_temperature_delta body_temperature_delta VARCHAR(10) NOT NULL, CHANGE muscle_fatigue muscle_fatigue INT NOT NULL, CHANGE heart_recovery_rate heart_recovery_rate INT NOT NULL, CHANGE heartbeat_variation heartbeat_variation VARCHAR(255) NOT NULL');
    }
}
