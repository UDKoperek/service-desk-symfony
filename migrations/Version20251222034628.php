<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251222034628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("
            CREATE TRIGGER check_email_limit
            BEFORE INSERT ON user_emails
            FOR EACH ROW
            BEGIN
                DECLARE email_count INT;
                SELECT COUNT(*) INTO email_count FROM user_emails WHERE user_id = NEW.user_id;
                IF email_count >= 3 THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Limit of 3 emails reached';
                END IF;
            END
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("DROP TRIGGER IF EXISTS check_email_limit");
    }
}
