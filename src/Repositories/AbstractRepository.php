<?php

declare(strict_types=1);

namespace Relnaggar\Veloz\Repositories;

use PDO;
use PDOException;

abstract class AbstractRepository
{
  protected PDO $pdo;
  protected string $tableName;
  protected string $modelClass;

  /**
   * @param DatabaseInterface $database The database interface.
   */
  public function __construct(DatabaseInterface $database)
  {
    $this->pdo = $database->getConnection();
  }

  /**
   * Select all records from the table.
   * 
   * @return array An array of record objects or associative arrays.
   * @throws PDOException If there is a database error.
   */
  public function selectAll(): array
  {
    $stmt = $this->pdo->prepare(<<<SQL
      SELECT *
      FROM {$this->tableName}
    SQL);
    $stmt->execute();
    if (isset($this->modelClass)) {
      $results = $stmt->fetchAll(PDO::FETCH_CLASS, $this->modelClass);
    } else {
      $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    return $results;
  }

  /**
   * Select a record by its primary key.
   * 
   * @param mixed $pkValue The primary key value.
   * @param string $pkColumn The primary key column name. Default is 'id'.
   * @return mixed The record object or associative array if found,
   *  null otherwise.
   * @throws PDOException If there is a database error.
   */
  public function selectOne(mixed $pkValue, string $pkColumn = 'id'): mixed
  {
    $stmt = $this->pdo->prepare(<<<SQL
      SELECT *
      FROM {$this->tableName}
      WHERE {$pkColumn} = :pkValue
    SQL);
    $stmt->execute(['pkValue' => $pkValue]);
    if (isset($this->modelClass)) {
      $result = $stmt->fetchObject($this->modelClass);
    } else {
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    if (!$result) {
      return null;
    }

    return $result;
  }

  /**
   * Get the primary key of a record if it exists, or create it if it does not.
   * 
   * @param mixed $record The record object or associative array to check or
   *  insert.
   * @param callable $isMatch A callable that takes two parameters (the record
   * to check and a record from the database) and returns true if they match.
   * @param string $pkColumn The primary key column name. Default is 'id'.
   * @return mixed The primary key value of the existing or newly created
   *  record.
   * @throws PDOException If there is a database error.
   */
  public function getPkOrCreate(
    mixed $record,
    callable $isMatch,
    string $pkColumn = 'id',
  ): mixed {
    if (!is_object($record) && !is_array($record)) {
      throw new \InvalidArgumentException(
        'Record must be an object or an associative array.',
      );
    }

    // check if record exists
    foreach ($this->selectAll() as $dbRecord) {
      if ($isMatch($record, $dbRecord)) {
        if (is_array($dbRecord)) {
          return $dbRecord[$pkColumn];
        } else {
          return $dbRecord->{$pkColumn};
        }
      }
    }

    // record does not exist, insert it
    $columns = [];
    $placeholders = [];
    $values = [];
    if (is_array($record)) {
      foreach ($record as $column => $value) {
        $columns[] = $column;
        $placeholders[] = ':' . $column;
        $values[$column] = $value;
      }
    } else {
      foreach (get_object_vars($record) as $column => $value) {
        $columns[] = $column;
        $placeholders[] = ':' . $column;
        $values[$column] = $value;
      }
    }
    $columnsStr = implode(', ', $columns);
    $placeholdersStr = implode(', ', $placeholders);
    $stmt = $this->pdo->prepare(<<<SQL
      INSERT INTO {$this->tableName} ({$columnsStr})
      VALUES ({$placeholdersStr})
    SQL);
    $stmt->execute($values);
    return $this->pdo->lastInsertId();
  }

  /**
   * Update a record in the table.
   * 
   * @param mixed $record The record object or associative array to update.
   * @param string $pkColumn The primary key column name. Default is 'id'.
   * @return void
   * @throws PDOException If there is a database error.
   */
  public function update(mixed $record, string $pkColumn = 'id'): void
  {
    $setClauses = [];
    $values = [];
    if (is_array($record)) {
      foreach ($record as $column => $value) {
        if ($column === $pkColumn) {
          continue;
        }
        $setClauses[] = "{$column} = :{$column}";
        $values[$column] = $value == '' ? null : $value;
      }
    } else {
      foreach (get_object_vars($record) as $column => $value) {
        if ($column === $pkColumn) {
          continue;
        }
        $setClauses[] = "{$column} = :{$column}";
        $values[$column] = $value == '' ? null : $value;
      }
    }
    $values['pkValue'] = is_array($record) ?
      $record[$pkColumn] :
      $record->{$pkColumn};
    $setStr = implode(', ', $setClauses);
    $stmt = $this->pdo->prepare(<<<SQL
      UPDATE {$this->tableName}
      SET {$setStr}
      WHERE {$pkColumn} = :pkValue
    SQL);
    $stmt->execute($values);
  }
}