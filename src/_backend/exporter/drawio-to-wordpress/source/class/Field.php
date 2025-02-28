<?php

namespace JDLX\DrawioConverter\Wordpress;

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


    public function getCode()
    {

        return "'" . $this->normalize($this->field->getName()) . "'";
    }


    public function getDefaultValue()
    {
        $defaultValue = $this->field->getDefaultValue();


        if(strtolower($defaultValue) == 'now') {
            return 'CURRENT_TIMESTAMP';
        }
        elseif($defaultValue) {
            $value = $this->protectValue($defaultValue);

            return "'" . $value . "'";
        }

        return null;
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
            $type = strtolower($this->field->getType());

            if($type) {
                if($type == 'string') {
                    $this->type = static::TYPE_STRING_DEFAULT;
                }
                elseif($type == 'text') {
                    $this->type = static::TYPE_TEXT;
                }
                elseif($type == DrawioMCDConverterField::TYPE_AUTO_ID) {
                    $this->type = static::TYPE_ID;
                }
            }
            else {
                $this->type = $this->findType();
            }
        }
        return $this->type;
    }
}
