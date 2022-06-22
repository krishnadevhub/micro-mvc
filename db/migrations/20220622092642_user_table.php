<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UserTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
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
    }
}
