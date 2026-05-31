<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Employee;
use App\Factory\PdoFactory;

class EmployeeRepository
{
    public function __construct(
        private readonly \PDO $pdo,
    ) {
    }

    /**
     * Create an instance using PdoFactory for the database connection
     */
    public static function create(): self
    {
        return new self(PdoFactory::create());
    }

    public function find(string $id): ?Employee
    {
        $sql = 'SELECT * FROM `employee` WHERE `id` = :id LIMIT 1';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_STR);
        $statement->execute();

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return $this->hydrateEntity($row);
    }

    /**
     * @return Employee[]
     */
    public function findAll(): array
    {
        $sql = 'SELECT * FROM `employee`';
        $statement = $this->pdo->query($sql);

        $entities = [];
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $entities[] = $this->hydrateEntity($row);
        }

        return $entities;
    }

    public function insert(Employee $entity): Employee
    {
        $sql = 'INSERT INTO `employee` (`surname`, `firstname`, `salary`) VALUES (:surname, :firstname, :salary)';
        $statement = $this->pdo->prepare($sql);
            $surnameValue = $entity->getSurname();
            $statement->bindValue(':surname', $surnameValue, $surnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $firstnameValue = $entity->getFirstname();
            $statement->bindValue(':firstname', $firstnameValue, $firstnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $salaryValue = $entity->getSalary();
            $statement->bindValue(':salary', $salaryValue, $salaryValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $statement->execute();

        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setValue($entity, (int) $this->pdo->lastInsertId());

        return $entity;
    }

    public function update(Employee $entity): Employee
    {
        $sql = 'UPDATE `employee` SET `surname` = :surname, `firstname` = :firstname, `salary` = :salary WHERE `id` = :id';
        $statement = $this->pdo->prepare($sql);
            $surnameValue = $entity->getSurname();
            $statement->bindValue(':surname', $surnameValue, $surnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $firstnameValue = $entity->getFirstname();
            $statement->bindValue(':firstname', $firstnameValue, $firstnameValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
            $salaryValue = $entity->getSalary();
            $statement->bindValue(':salary', $salaryValue, $salaryValue !== null ? \PDO::PARAM_STR : \PDO::PARAM_NULL);
        $statement->bindValue(':id', $entity->getId(), \PDO::PARAM_STR);
        $statement->execute();

        return $entity;
    }

    public function delete(string $id): bool
    {
        $sql = 'DELETE FROM `employee` WHERE `id` = :id';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_STR);
        $statement->execute();

        return $statement->rowCount() > 0;
    }

    private function hydrateEntity(array $row): Employee
    {
        $entity = new Employee();

        $reflection = new \ReflectionProperty($entity, 'id');
        $reflection->setValue($entity, (string) $row['id']);

        $entity->setSurname((string) $row['surname']);
        $entity->setFirstname((string) $row['firstname']);
        $entity->setSalary((string) $row['salary']);

        return $entity;
    }
}
