<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ProductsTable extends AbstractMigration
{
    /**
     * Change Method.
     */
    public function change(): void
    {
        $table = $this->table('products');
        $table->create();

        $this->execute(
            "CREATE TABLE `user` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
                `roles` longtext COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '(DC2Type:json)',
                `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                PRIMARY KEY (`id`),
                UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`)
            ) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

        $this->execute(
            "CREATE TABLE `tag` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );

    }
}
