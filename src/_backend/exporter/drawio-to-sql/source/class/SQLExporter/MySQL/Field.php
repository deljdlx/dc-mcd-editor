<?php

namespace JDLX\DrawioConverter\SQLExporter\MySQL;

use JDLX\DrawioConverter\Field as DrawioMCDConverterField;

class Field extends Driver
{
    public const TYPE_DEFAULT = 'VARCHAR(255)';
    public const TYPE_ID = 'BIGINT(16) UNSIGNED';
    public const TYPE_TEXT = 'TEXT';
    public const TYPE_STRING_DEFAULT = 'VARCHAR(255)';

    /**
     * @var DrawioMCDConverterField
     */
    protected $field;

    /**
     * @var string
     */
    protected $type;


    public function __construct($field)
    {
        $this->field = $field;
    }


    public function getSQL($isDeclaration = true)
    {
        $field = $this->field;
        $fieldName = $this->escape($this->normalize($field->getName()));
        if($isDeclaration) {
            return $fieldName . ' ' . $this->getDeclaration();
        }
        else {
            return $fieldName . ' ' . $this->getType();
        }
    }


    public function getDefaultValue()
    {
        $defaultValue = $this->field->getDefaultValue();


        if(is_string($defaultValue) && strtolower($defaultValue) == 'now') {
            return 'CURRENT_TIMESTAMP';
        }
        elseif($defaultValue) {
            $value = $this->protectValue($defaultValue);

            return "'" . $value . "'";
        }

        return null;
    }


    // WARNING unsafe function
    protected function protectValue($value)
    {
        $value = str_replace("\\", "\\\\'", $value);
        $value = str_replace("'", "\'", $value);

        return $value;
    }

    /**
     * @return string
     */
    public function getDeclaration()
    {
        $sql = $this->getType();

        if($default = $this->getDefaultValue()) {
            $sql .= " DEFAULT ". $default . "";
        }

        if(!$this->nullAllowed()) {
            $sql .= ' NOT NULL';
        }

        if($this->field->getType() == DrawioMCDConverterField::TYPE_AUTO_ID) {
            $sql .= ' AUTO_INCREMENT';
        }

        return $sql;
    }


    /**
     *
     * @return bool
     */
    public function nullAllowed()
    {
        if($this->field->getType() == DrawioMCDConverterField::TYPE_AUTO_ID) {
            return false;
        }
        return $this->field->nullAllowed();
    }


    /**
     * @return string
     */
    public function getType()
    {
        if(!$this->type) {
            $fieldType = $this->field->getType();

            if($fieldType) {
                if($fieldType == DrawioMCDConverterField::TYPE_AUTO_ID) {
                    $this->type = static::TYPE_ID;
                }
                else {
                    $this->type = $fieldType;
                }
            }
            else {
                $this->type = $this->findType();
            }
        }

        return $this->type;
    }


    /**
     * @return string
     */
    public function findType()
    {
        if(preg_match('`(?:[^a-z]|^)date[^a-z]`', $this->field->getName())) {
            return 'DATETIME';
        }
        elseif(preg_match('`(?:[^a-z]|^)created`', $this->field->getName())) {
            return 'DATETIME';
        }
        elseif(preg_match('`(?:[^a-z]|^)updated`', $this->field->getName())) {
            return 'DATETIME';
        }

        return static::TYPE_DEFAULT;
    }
}
