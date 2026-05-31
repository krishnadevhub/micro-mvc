<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateUserTable extends AbstractMigration
{
    public function change(): void
    {
        $this->execute(
            "CREATE TABLE `user` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `first_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `surname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
                `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
                `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
                `created_at` datetime NOT NULL,
                `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `UNIQ_USER_EMAIL` (`email`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;"
        );
    }
}
