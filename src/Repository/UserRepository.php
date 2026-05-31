<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Factory\PdoFactory;
use DateTimeImmutable;

class UserRepository
{
    public function __construct(
        private readonly \PDO $pdo,
    ) {
    }

    public static function create(): self
    {
        return new self(PdoFactory::create());
    }

    public function find(string $id): ?User
    {
        $sql = 'SELECT * FROM `user` WHERE `id` = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrateEntity($row);
    }

    public function findByEmail(string $email): ?User
    {
        $sql = 'SELECT * FROM `user` WHERE `email` = :email LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':email', $email, \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrateEntity($row);
    }

    public function insert(User $entity): User
    {
        $now = new DateTimeImmutable();
        $entity->setCreatedAt($now);
        $entity->setUpdatedAt($now);

        $sql = 'INSERT INTO `user` (`first_name`, `surname`, `email`, `password`, `created_at`, `updated_at`) VALUES (:first_name, :surname, :email, :password, :created_at, :updated_at)';
        $statement = $this->pdo->prepare($sql);
        $firstNameValue = $entity->getFirstName();
        $statement->bindValue(':first_name', $firstNameValue, $firstNameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $surnameValue = $entity->getSurname();
        $statement->bindValue(':surname', $surnameValue, $surnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $emailValue = $entity->getEmail();
        $statement->bindValue(':email', $emailValue, $emailValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $passwordValue = $entity->getPassword();
        $statement->bindValue(':password', $passwordValue, $passwordValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $statement->bindValue(':created_at', $entity->getCreatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $statement->bindValue(':updated_at', $entity->getUpdatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $statement->execute();

        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setValue($entity, (string) $this->pdo->lastInsertId());

        return $entity;
    }

    public function update(User $entity): User
    {
        $entity->setUpdatedAt(new DateTimeImmutable());

        $sql = 'UPDATE `user` SET `first_name` = :first_name, `surname` = :surname, `email` = :email, `password` = :password, `updated_at` = :updated_at WHERE `id` = :id';
        $statement = $this->pdo->prepare($sql);
        $firstNameValue = $entity->getFirstName();
        $statement->bindValue(':first_name', $firstNameValue, $firstNameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $surnameValue = $entity->getSurname();
        $statement->bindValue(':surname', $surnameValue, $surnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $emailValue = $entity->getEmail();
        $statement->bindValue(':email', $emailValue, $emailValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $passwordValue = $entity->getPassword();
        $statement->bindValue(':password', $passwordValue, $passwordValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $statement->bindValue(':updated_at', $entity->getUpdatedAt()->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $statement->bindValue(':id', $entity->getId(), \PDO::PARAM_STR);
        $statement->execute();

        return $entity;
    }

    public function delete(string $id): bool
    {
        $sql = 'DELETE FROM `user` WHERE `id` = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    private function hydrateEntity(array $row): User
    {
        $entity = new User();

        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setValue($entity, (string) $row['id']);

        $entity->setFirstName($row['first_name'] !== null ? (string) $row['first_name'] : null);
        $entity->setSurname($row['surname'] !== null ? (string) $row['surname'] : null);
        $entity->setEmail($row['email'] !== null ? (string) $row['email'] : null);
        $entity->setPassword($row['password'] !== null ? (string) $row['password'] : null);
        $entity->setCreatedAt($row['created_at'] !== null ? new DateTimeImmutable($row['created_at']) : null);
        $entity->setUpdatedAt($row['updated_at'] !== null ? new DateTimeImmutable($row['updated_at']) : null);

        return $entity;
    }
}
