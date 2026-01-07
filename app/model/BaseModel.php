<?php

namespace App\Model;

use Fitchart\Application\Utilities;
use Nette\Utils\DateTime;

/**
 * Basic operations
 */
class BaseModel
{
    /** @var string Table name */
    protected $tableName;

    /** @var \Nette\Database\Context */
    protected $context;

    /** @var string */
    protected $datetime = null;
    

    /**
     * @param \Nette\Database\Context $context
     */
    public function __construct(\Nette\Database\Context $context)
    {
        $this->context = $context;
        $this->getTableName();
    }

    /**
     * @return string
     */
    protected function getTableName()
    {
        if (empty($this->tableName)) {
            $this->tableName = Utilities::convertClassNameToTableName(get_called_class());
        }
        return $this->tableName;
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->context->table($this->getTableName());
    }

    /**
     * @return \Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * @param array $by
     * @return \Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }

    /**
     * @param string $keyName
     * @param string $valueName
     * @return array
     */
    public function findActivePairs($keyName = 'id', $valueName = 'name')
    {
        return $this->getTable()
                    ->where(['active' => true])
                    ->fetchPairs($keyName, $valueName);
    }

    /**
     * @param string $keyName
     * @param string $valueName
     * @return array
     */
    public function findPairs($keyName = 'id', $valueName = 'name')
    {
        return $this->getTable()
                    ->fetchPairs($keyName, $valueName);
    }

    /**
     * @param array $by
     * @return \Nette\Database\Table\ActiveRow|false
     */
    public function findOneBy(array $by)
    {
        return $this->getTable()->where($by)->fetch();
    }

    /**
     * Returns row by primary key
     *
     * @param int $id
     * @return \Nette\Database\Table\ActiveRow|false
     */
    public function findRow($id)
    {
        return $this->getTable()->get((int) $id);
    }

    /**
     * @param array|\Traversable $data
     * @return \Nette\Database\Table\ActiveRow
     */
    public function insert($data)
    {
        return $this->getTable()->insert($data);
    }

    /**
     * @param array|\Traversable $data
     * @return int Number of affected rows
     */
    public function update($data)
    {
        return $this->getTable()->update($data);
    }

    /**
     * @param $id
     * @return int number of affected rows
     */
    public function delete($id)
    {
        return $this->findBy([$this->getTable()->getPrimary() => (int) $id])->delete();
    }

    /**
     * @param array $data
     * @return \Nette\Database\Table\ActiveRow|false
     */
    public function insertUpdate($data)
    {
        if (empty($data['id'])) {
            $record = $this->insert($data);
        } else {
            $record = $this->findRow((int) $data['id']);
            if ($record) {
                $record->update($data);
            } else {
                return false;
            }
        }

        return $record;
    }

    /**
     * @return array
     */
    public function getColumns()
    {
        $columns = $this->context->connection->getSupplementalDriver()->getColumns($this->getTableName());

        $columnsResult = [];

        foreach ($columns as $column) {
            $columnsResult[] = $column['name'];
        }

        return $columnsResult;
    }

    /**
     * @param string|NULL $format
     * @return DateTime|string
     */
    public function getDateTime($format = null)
    {
        if (empty($format)) {
            return $this->datetime ?: new DateTime();
        } elseif ($this->datetime) {
            $this->datetime = new DateTime($this->datetime);
            return $this->datetime->format($format);
        } else {
            $this->datetime = new DateTime();
            return $this->datetime->format($format);
        }
    }
}
