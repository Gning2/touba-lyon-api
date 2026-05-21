<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521081914 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uq_category_name ON category');
        $this->addSql('ALTER TABLE category CHANGE color color VARCHAR(7) DEFAULT NULL, CHANGE icon icon VARCHAR(50) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_loan_date ON loan');
        $this->addSql('DROP INDEX idx_loan_status ON loan');
        $this->addSql('ALTER TABLE loan CHANGE status status VARCHAR(20) NOT NULL, CHANGE purpose purpose VARCHAR(100) DEFAULT NULL, CHANGE notes notes LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE loan_item DROP FOREIGN KEY `fk_loan_item_loan`');
        $this->addSql('ALTER TABLE loan_item DROP FOREIGN KEY `fk_loan_item_material`');
        $this->addSql('ALTER TABLE loan_item CHANGE returned_quantity returned_quantity INT DEFAULT NULL, CHANGE notes notes LONGTEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_loan_item_material ON loan_item');
        $this->addSql('CREATE INDEX IDX_CEB65F6AE308AC6F ON loan_item (material_id)');
        $this->addSql('DROP INDEX idx_loan_item_loan ON loan_item');
        $this->addSql('CREATE INDEX IDX_CEB65F6ACE73868F ON loan_item (loan_id)');
        $this->addSql('ALTER TABLE loan_item ADD CONSTRAINT `fk_loan_item_loan` FOREIGN KEY (loan_id) REFERENCES loan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loan_item ADD CONSTRAINT `fk_loan_item_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY `fk_material_category`');
        $this->addSql('ALTER TABLE material CHANGE total_stock total_stock INT NOT NULL, CHANGE available_stock available_stock INT NOT NULL, CHANGE unit unit VARCHAR(50) DEFAULT NULL, CHANGE notes notes VARCHAR(255) DEFAULT NULL');
        $this->addSql('DROP INDEX idx_material_category ON material');
        $this->addSql('CREATE INDEX IDX_7CBE759512469DE2 ON material (category_id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT `fk_material_category` FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE material_condition CHANGE color color VARCHAR(7) NOT NULL, CHANGE is_available is_available TINYINT NOT NULL, CHANGE position position INT NOT NULL');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY `fk_mch_material`');
        $this->addSql('DROP INDEX idx_mch_changed ON material_condition_history');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY `fk_mch_condition`');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY `fk_mch_material`');
        $this->addSql('ALTER TABLE material_condition_history CHANGE comment comment LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT FK_956AA1AFE308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('DROP INDEX idx_mch_material ON material_condition_history');
        $this->addSql('CREATE INDEX IDX_956AA1AFE308AC6F ON material_condition_history (material_id)');
        $this->addSql('DROP INDEX fk_mch_condition ON material_condition_history');
        $this->addSql('CREATE INDEX IDX_956AA1AF887793B6 ON material_condition_history (condition_id)');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT `fk_mch_condition` FOREIGN KEY (condition_id) REFERENCES material_condition (id)');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT `fk_mch_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY `fk_mcs_material`');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY `fk_mcs_condition`');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY `fk_mcs_material`');
        $this->addSql('ALTER TABLE material_condition_stock CHANGE quantity quantity INT NOT NULL');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT FK_4C1E0475E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('DROP INDEX fk_mcs_condition ON material_condition_stock');
        $this->addSql('CREATE INDEX IDX_4C1E0475887793B6 ON material_condition_stock (condition_id)');
        $this->addSql('DROP INDEX uq_material_condition ON material_condition_stock');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_4C1E0475E308AC6F887793B6 ON material_condition_stock (material_id, condition_id)');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT `fk_mcs_condition` FOREIGN KEY (condition_id) REFERENCES material_condition (id)');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT `fk_mcs_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material_photo DROP FOREIGN KEY `fk_photo_material`');
        $this->addSql('ALTER TABLE material_photo DROP FOREIGN KEY `fk_photo_material`');
        $this->addSql('ALTER TABLE material_photo ADD CONSTRAINT FK_42502856E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('DROP INDEX idx_photo_material ON material_photo');
        $this->addSql('CREATE INDEX IDX_42502856E308AC6F ON material_photo (material_id)');
        $this->addSql('ALTER TABLE material_photo ADD CONSTRAINT `fk_photo_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY `fk_movement_loan`');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY `fk_movement_material`');
        $this->addSql('DROP INDEX idx_movement_created ON stock_movement');
        $this->addSql('DROP INDEX idx_movement_type ON stock_movement');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY `fk_movement_loan`');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY `fk_movement_material`');
        $this->addSql('ALTER TABLE stock_movement CHANGE type type VARCHAR(20) NOT NULL, CHANGE reason reason LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5CE73868F FOREIGN KEY (loan_id) REFERENCES loan (id)');
        $this->addSql('DROP INDEX idx_movement_material ON stock_movement');
        $this->addSql('CREATE INDEX IDX_BB1BC1B5E308AC6F ON stock_movement (material_id)');
        $this->addSql('DROP INDEX idx_movement_loan ON stock_movement');
        $this->addSql('CREATE INDEX IDX_BB1BC1B5CE73868F ON stock_movement (loan_id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT `fk_movement_loan` FOREIGN KEY (loan_id) REFERENCES loan (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT `fk_movement_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category CHANGE color color VARCHAR(7) DEFAULT NULL COMMENT \'Couleur hex ex: #3B82F6\', CHANGE icon icon VARCHAR(50) DEFAULT NULL COMMENT \'Nom icône Material Icons\'');
        $this->addSql('CREATE UNIQUE INDEX uq_category_name ON category (name)');
        $this->addSql('ALTER TABLE loan CHANGE status status VARCHAR(20) DEFAULT \'active\' NOT NULL COMMENT \'active|returned|partial|overdue\', CHANGE notes notes TEXT DEFAULT NULL, CHANGE purpose purpose VARCHAR(100) DEFAULT NULL COMMENT \'Motif de l emprunt\'');
        $this->addSql('CREATE INDEX idx_loan_date ON loan (loan_date)');
        $this->addSql('CREATE INDEX idx_loan_status ON loan (status)');
        $this->addSql('ALTER TABLE loan_item DROP FOREIGN KEY FK_CEB65F6AE308AC6F');
        $this->addSql('ALTER TABLE loan_item DROP FOREIGN KEY FK_CEB65F6ACE73868F');
        $this->addSql('ALTER TABLE loan_item CHANGE returned_quantity returned_quantity INT DEFAULT 0 NOT NULL, CHANGE notes notes TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_ceb65f6ace73868f ON loan_item');
        $this->addSql('CREATE INDEX idx_loan_item_loan ON loan_item (loan_id)');
        $this->addSql('DROP INDEX idx_ceb65f6ae308ac6f ON loan_item');
        $this->addSql('CREATE INDEX idx_loan_item_material ON loan_item (material_id)');
        $this->addSql('ALTER TABLE loan_item ADD CONSTRAINT FK_CEB65F6AE308AC6F FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE loan_item ADD CONSTRAINT FK_CEB65F6ACE73868F FOREIGN KEY (loan_id) REFERENCES loan (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE material DROP FOREIGN KEY FK_7CBE759512469DE2');
        $this->addSql('ALTER TABLE material CHANGE total_stock total_stock INT DEFAULT 0 NOT NULL, CHANGE available_stock available_stock INT DEFAULT 0 NOT NULL, CHANGE unit unit VARCHAR(50) DEFAULT NULL COMMENT \'ex: unité, carton, lot\', CHANGE notes notes TEXT DEFAULT NULL');
        $this->addSql('DROP INDEX idx_7cbe759512469de2 ON material');
        $this->addSql('CREATE INDEX idx_material_category ON material (category_id)');
        $this->addSql('ALTER TABLE material ADD CONSTRAINT FK_7CBE759512469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE material_condition CHANGE color color VARCHAR(7) DEFAULT \'#10B981\' NOT NULL, CHANGE is_available is_available TINYINT DEFAULT 1 NOT NULL, CHANGE position position INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY FK_956AA1AFE308AC6F');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY FK_956AA1AFE308AC6F');
        $this->addSql('ALTER TABLE material_condition_history DROP FOREIGN KEY FK_956AA1AF887793B6');
        $this->addSql('ALTER TABLE material_condition_history CHANGE comment comment TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT `fk_mch_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_mch_changed ON material_condition_history (changed_at)');
        $this->addSql('DROP INDEX idx_956aa1af887793b6 ON material_condition_history');
        $this->addSql('CREATE INDEX fk_mch_condition ON material_condition_history (condition_id)');
        $this->addSql('DROP INDEX idx_956aa1afe308ac6f ON material_condition_history');
        $this->addSql('CREATE INDEX idx_mch_material ON material_condition_history (material_id)');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT FK_956AA1AFE308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE material_condition_history ADD CONSTRAINT FK_956AA1AF887793B6 FOREIGN KEY (condition_id) REFERENCES material_condition (id)');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY FK_4C1E0475E308AC6F');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY FK_4C1E0475E308AC6F');
        $this->addSql('ALTER TABLE material_condition_stock DROP FOREIGN KEY FK_4C1E0475887793B6');
        $this->addSql('ALTER TABLE material_condition_stock CHANGE quantity quantity INT DEFAULT 0 NOT NULL');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT `fk_mcs_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_4c1e0475887793b6 ON material_condition_stock');
        $this->addSql('CREATE INDEX fk_mcs_condition ON material_condition_stock (condition_id)');
        $this->addSql('DROP INDEX uniq_4c1e0475e308ac6f887793b6 ON material_condition_stock');
        $this->addSql('CREATE UNIQUE INDEX uq_material_condition ON material_condition_stock (material_id, condition_id)');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT FK_4C1E0475E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE material_condition_stock ADD CONSTRAINT FK_4C1E0475887793B6 FOREIGN KEY (condition_id) REFERENCES material_condition (id)');
        $this->addSql('ALTER TABLE material_photo DROP FOREIGN KEY FK_42502856E308AC6F');
        $this->addSql('ALTER TABLE material_photo DROP FOREIGN KEY FK_42502856E308AC6F');
        $this->addSql('ALTER TABLE material_photo ADD CONSTRAINT `fk_photo_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX idx_42502856e308ac6f ON material_photo');
        $this->addSql('CREATE INDEX idx_photo_material ON material_photo (material_id)');
        $this->addSql('ALTER TABLE material_photo ADD CONSTRAINT FK_42502856E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5E308AC6F');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5CE73868F');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5E308AC6F');
        $this->addSql('ALTER TABLE stock_movement DROP FOREIGN KEY FK_BB1BC1B5CE73868F');
        $this->addSql('ALTER TABLE stock_movement CHANGE type type VARCHAR(20) NOT NULL COMMENT \'in|out|adjustment\', CHANGE reason reason TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT `fk_movement_loan` FOREIGN KEY (loan_id) REFERENCES loan (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT `fk_movement_material` FOREIGN KEY (material_id) REFERENCES material (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX idx_movement_created ON stock_movement (created_at)');
        $this->addSql('CREATE INDEX idx_movement_type ON stock_movement (type)');
        $this->addSql('DROP INDEX idx_bb1bc1b5e308ac6f ON stock_movement');
        $this->addSql('CREATE INDEX idx_movement_material ON stock_movement (material_id)');
        $this->addSql('DROP INDEX idx_bb1bc1b5ce73868f ON stock_movement');
        $this->addSql('CREATE INDEX idx_movement_loan ON stock_movement (loan_id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5E308AC6F FOREIGN KEY (material_id) REFERENCES material (id)');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B5CE73868F FOREIGN KEY (loan_id) REFERENCES loan (id)');
    }
}
