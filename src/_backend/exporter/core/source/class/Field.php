<?php
namespace JDLX\DrawioConverter;

class Field extends Entity
{
    public const TYPE_AUTO_ID = 'auto_id';

    protected $name;
    protected $type;

    protected $nullAllowed = true;

    protected $autoincrement = false;


    protected $defaultValue;
    protected $inherited = false;
    protected $inheritedFrom;


    public function __construct($graph = null, $xmlNode = null)
    {
        $this->graph = $graph;
        $this->xml = $xmlNode;

        if($this->xml) {
            $dataNode =$this->xml->xPath('parent::object');
            if(count($dataNode)) {
                $this->dataNode = $dataNode[0];
            }
        }
    }

    public function inherited($value, $from = null)
    {
        $this->inheritedFrom = $from;
        $this->inherited = $value;
        return $this;
    }

    public function getDefaultValue()
    {
        if($this->dataNode) {
            $attributes = $this->dataNode->attributes();

            if(isset($attributes['DEFAULT'])) {
                return $this->dataNode['DEFAULT'];
            }
        }
        return null;
    }


    /**
     * @return bool
     */
    public function nullAllowed()
    {
        if($this->dataNode) {
            $attributes = $this->dataNode->attributes();

            if(isset($attributes['NOT_NULL'])) {
                return false;
            }
        }
        return $this->nullAllowed;
    }


    public function getType()
    {
        if(!$this->type) {
            if($this->dataNode) {
                if($this->dataNode['type']) {
                    $this->type = (string) $this->dataNode['type'];
                }
                elseif($this->dataNode['TYPE']) {
                    $this->type = (string) $this->dataNode['TYPE'];
                }
            }
        }
        return $this->type;
    }

    /**
     * @param string $type
     * @return this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    public function isGenerated()
    {

        if($this->getType() == static::TYPE_AUTO_ID) {
            return true;
        }

        return false;
    }



    public function jsonSerialize(): mixed
    {
        $data = [
            '_metadata' => [
                'id' => $this->getId(),
                'type' => $this->getType(),
                'isGenerated' => $this->isGenerated(),
                'inherited' => $this->inherited,
            ],
            'name' => $this->getName(),
            'data' => $this->getData(),
        ];

        if($this->inherited) {
            $data['_metadata']['inheritedFrom'] = $this->inheritedFrom->getName();
        }


        return $data;
    }
}
