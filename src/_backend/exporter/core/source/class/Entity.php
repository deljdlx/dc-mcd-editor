<?php
namespace JDLX\DrawioConverter;

use JDLX\DrawioConverter\Traits\Timestamped;
use SimpleXMLElement;

class Entity extends AbstractEntity
{
    use Timestamped;

    public const TYPE_AUTO_COMPUTED = 'auto_computed';

    protected $value;

    protected $id;
    protected $name;

    protected $parentEntities;


    public function __construct($graph, $xmlNode = null)
    {
        parent::__construct($graph, $xmlNode);
        $this->createPrimaryKeyField();
    }


    /**
     * @return boolean
     */
    public function isReal(): bool
    {

        // NOTICE 2 fields because there is alway an id field
        if(count($this->fields) < 2 && count($this->getParentEntities()) == 0) {
            return false;
        }

        if(!$this->getName()) {
            return false;
        }
        return true;
    }

    public function isAbstract()
    {
        return !$this->isReal();
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getIdFieldName(): string
    {
        return 'id';
    }

    public function jsonSerialize(): mixed
    {
        $data = parent::jsonSerialize();
        $data['_metadata']['isAbstract'] = false;
        return $data;
    }
}
