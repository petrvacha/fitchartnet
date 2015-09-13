<?php

namespace App\Model;

use Nette\Utils\DateTime;
use \Fitchart\Application\Utilities;

/**
 * Basic operations
 */
class BaseModel extends \Nette\Object
{
    
    /** @var string Table name */
    protected $tableName;

    /** @var Nette\Database\Context */
    protected $context;

    /** @var string */
    protected $datetime = NULL;
    

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
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        return $this->context->table($this->getTableName());
    }

    /**
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * @param array $by
     * @return Nette\Database\Table\Selection
     */
    public function findBy(array $by)
    {
        return $this->getTable()->where($by);
    }

    /**
     * @param string $keyName
     * @param string $valueName
     * @return Nette\Database\Table\Selection
     */
    public function findActivePairs($keyName = 'id', $valueName = 'name')
    {
        return $this->getTable()
                    ->where(['active' => TRUE])
                    ->fetchPairs($keyName, $valueName);
    }

    /**
     * @param string $keyName
     * @param string $valueName
     * @return Nette\Database\Table\Selection
     */
    public function findPairs($keyName = 'id', $valueName = 'name')
    {
        return $this->getTable()
                    ->fetchPairs($keyName, $valueName);
    }

    /**
     * @param array $by
     * @return Nette\Database\Row
     */
    public function findOneBy(array $by)
    {
        return $this->getTable()->where($by)->fetch();
    }

    /**
     * Returns row by primary key
     * 
     * @param $id
     * @param Nette\Database\Row
     */
    public function findRow($id)
    {
        return $this->getTable()->get((int) $id);
    }

    /**
     * @param $data
     * @return 
     */
    public function insert($data)
    {
        return $this->getTable()->insert($data);
    }

    /**
     * @param $data
     * @return
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
        return $this->findBy(array($this->getTable()->getPrimary() => (int) $id))->delete();
    }

    /**
     * @param array
     * @return bool|int
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
                return FALSE;
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

        $columnsResult = array();

        foreach ($columns as $column) {
            $columnsResult[] = $column['name'];
        }

        return $columnsResult;
    }

    /**
     * @param string $format
     * @return DateTime
     */
    public function getDateTime($format = NULL)
    {
        if (empty($format)) {
            return $this->datetime ?: new DateTime();
        } else if ($this->datetime) {
            $this->datetime = new DateTime($this->datetime);
            return $this->datetime->format($format);
        } else {
            $this->datetime = new DateTime();
            return $this->datetime->format($format);
        }
    }
    
}