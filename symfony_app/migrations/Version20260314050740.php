<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260314050740 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE delivery (id INT AUTO_INCREMENT NOT NULL, customer VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE delivery_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, delivery_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_CE87ED8412136921 (delivery_id), INDEX IDX_CE87ED844584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, sku VARCHAR(100) NOT NULL, unit VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, category_id INT NOT NULL, UNIQUE INDEX UNIQ_D34A04ADF9038C4 (sku), INDEX IDX_D34A04AD12469DE2 (category_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE receipt (id INT AUTO_INCREMENT NOT NULL, supplier VARCHAR(255) NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE receipt_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, receipt_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_89601E922B5CA896 (receipt_id), INDEX IDX_89601E924584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, product_id INT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_4B3656604584665A (product_id), INDEX IDX_4B3656605080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_adjustment (id INT AUTO_INCREMENT NOT NULL, old_quantity INT NOT NULL, new_quantity INT NOT NULL, difference INT NOT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_27B08FBA4584665A (product_id), INDEX IDX_27B08FBA5080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE stock_ledger (id INT AUTO_INCREMENT NOT NULL, movement_type VARCHAR(50) NOT NULL, quantity_change INT NOT NULL, reference_id INT DEFAULT NULL, created_at DATETIME NOT NULL, product_id INT NOT NULL, warehouse_id INT NOT NULL, INDEX IDX_A5D762A4584665A (product_id), INDEX IDX_A5D762A5080ECDE (warehouse_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transfer (id INT AUTO_INCREMENT NOT NULL, status VARCHAR(50) NOT NULL, created_at DATETIME NOT NULL, source_location_id INT NOT NULL, destination_location_id INT NOT NULL, INDEX IDX_4034A3C03A32712E (source_location_id), INDEX IDX_4034A3C0237FCAB5 (destination_location_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE transfer_item (id INT AUTO_INCREMENT NOT NULL, quantity INT NOT NULL, transfer_id INT NOT NULL, product_id INT NOT NULL, INDEX IDX_27EE097C537048AF (transfer_id), INDEX IDX_27EE097C4584665A (product_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE `users` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, name VARCHAR(255) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, otp_code VARCHAR(10) DEFAULT NULL, otp_expires_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_1483A5E9E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE warehouse (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, location VARCHAR(255) DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE delivery_item ADD CONSTRAINT FK_CE87ED8412136921 FOREIGN KEY (delivery_id) REFERENCES delivery (id)');
        $this->addSql('ALTER TABLE delivery_item ADD CONSTRAINT FK_CE87ED844584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('ALTER TABLE receipt_item ADD CONSTRAINT FK_89601E922B5CA896 FOREIGN KEY (receipt_id) REFERENCES receipt (id)');
        $this->addSql('ALTER TABLE receipt_item ADD CONSTRAINT FK_89601E924584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656604584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock ADD CONSTRAINT FK_4B3656605080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_27B08FBA4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock_adjustment ADD CONSTRAINT FK_27B08FBA5080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE stock_ledger ADD CONSTRAINT FK_A5D762A4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE stock_ledger ADD CONSTRAINT FK_A5D762A5080ECDE FOREIGN KEY (warehouse_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C03A32712E FOREIGN KEY (source_location_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE transfer ADD CONSTRAINT FK_4034A3C0237FCAB5 FOREIGN KEY (destination_location_id) REFERENCES warehouse (id)');
        $this->addSql('ALTER TABLE transfer_item ADD CONSTRAINT FK_27EE097C537048AF FOREIGN KEY (transfer_id) REFERENCES transfer (id)');
        $this->addSql('ALTER TABLE transfer_item ADD CONSTRAINT FK_27EE097C4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_item DROP FOREIGN KEY FK_CE87ED8412136921');
        $this->addSql('ALTER TABLE delivery_item DROP FOREIGN KEY FK_CE87ED844584665A');
        $this->addSql('ALTER TABLE product DROP FOREIGN KEY FK_D34A04AD12469DE2');
        $this->addSql('ALTER TABLE receipt_item DROP FOREIGN KEY FK_89601E922B5CA896');
        $this->addSql('ALTER TABLE receipt_item DROP FOREIGN KEY FK_89601E924584665A');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656604584665A');
        $this->addSql('ALTER TABLE stock DROP FOREIGN KEY FK_4B3656605080ECDE');
        $this->addSql('ALTER TABLE stock_adjustment DROP FOREIGN KEY FK_27B08FBA4584665A');
        $this->addSql('ALTER TABLE stock_adjustment DROP FOREIGN KEY FK_27B08FBA5080ECDE');
        $this->addSql('ALTER TABLE stock_ledger DROP FOREIGN KEY FK_A5D762A4584665A');
        $this->addSql('ALTER TABLE stock_ledger DROP FOREIGN KEY FK_A5D762A5080ECDE');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C03A32712E');
        $this->addSql('ALTER TABLE transfer DROP FOREIGN KEY FK_4034A3C0237FCAB5');
        $this->addSql('ALTER TABLE transfer_item DROP FOREIGN KEY FK_27EE097C537048AF');
        $this->addSql('ALTER TABLE transfer_item DROP FOREIGN KEY FK_27EE097C4584665A');
        $this->addSql('DROP TABLE category');
        $this->addSql('DROP TABLE delivery');
        $this->addSql('DROP TABLE delivery_item');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE receipt');
        $this->addSql('DROP TABLE receipt_item');
        $this->addSql('DROP TABLE stock');
        $this->addSql('DROP TABLE stock_adjustment');
        $this->addSql('DROP TABLE stock_ledger');
        $this->addSql('DROP TABLE transfer');
        $this->addSql('DROP TABLE transfer_item');
        $this->addSql('DROP TABLE `users`');
        $this->addSql('DROP TABLE warehouse');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
