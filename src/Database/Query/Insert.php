<?php


namespace Nip\Database\Query;

class Insert extends AbstractQuery
{

    protected $_cols;
    protected $_values;

    public function assemble()
    {
        $return = "INSERT INTO " . $this->protect($this->getTable());
        $return .= $this->parseCols();
        $return .= $this->parseValues();

        if ($this->hasPart('onDuplicate')) {
            $update = $this->getManager()->newQuery("update");

            $onDuplicate = $this->getPart('onDuplicate');
            foreach ($onDuplicate as $onDuplicate) {
                foreach ($onDuplicate as $key => $value) {
                    $data[$key] = $value;
                }
            }
            $update->data($data);

            $return .= " ON DUPLICATE KEY UPDATE {$update->parseUpdate()}";
        }

        return $return;
    }

    public function setCols($cols = array())
    {
        $this->_cols = $cols;
        return $this;
    }

    public function parseCols()
    {
        if (is_array($this->_parts['data'][0])) {
            $this->setCols(array_keys($this->_parts['data'][0]));
        }
        return $this->_cols ? ' (' . implode(',', array_map(array($this, 'protect'), $this->_cols)) . ')' : '';
    }

    public function setValues($values)
    {
        $this->_values = $values;
        return $this;
    }

    public function parseValues()
    {
        if ($this->_values instanceof AbstractQuery) {
            return ' ' . (string) $this->_values;
        } elseif (is_array($this->_parts['data'])) {
            return $this->parseData();
        }
        return false;
    }

    /**
     * Parses INSERT data
     *
     * @return string
     */
    protected function parseData()
    {
        $values = array();
        foreach ($this->_parts['data'] as $key => $data) {
            foreach ($data as $value) {
                if (!is_array($value)) {
                    $value = array($value);
                }

                foreach ($value as $insertValue) {
                    $values[$key][] = $this->getManager()->getAdapter()->quote($insertValue);
                }
            }
        }
        foreach ($values as &$value) {
            $value = "(" . implode(", ", $value) . ")";
        }

        return ' VALUES ' . implode(', ', $values);
    }
    
    public function onDuplicate($value)
    {
        $this->addPart('onDuplicate', $value);
    }

}