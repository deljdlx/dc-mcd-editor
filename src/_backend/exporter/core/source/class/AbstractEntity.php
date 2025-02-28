<?php

namespace JDLX\DrawioConverter;

use JDLX\DrawioConverter\Traits\Timestamped;
use SimpleXMLElement;

class AbstractEntity implements \JsonSerializable
{
    /** @var Graph */
    protected $graph;

    /** @var SimpleXMLElement */
    protected $xml;


    /**
     * @var Field[]
     */
    protected $fields = [];


    /**
     * @var AbstractEntity[]
     */
    protected $parentEntities = [];

    protected $virtualFields = [];

    /**
     * @var Relation[]
     */
    protected $relations = [];

    protected $value;
    protected ?SimpleXMLElement $dataNode = null;

    protected $id;
    protected $name;

    protected $primaryKey;

    public function __construct($graph, $xmlNode = null)
    {
        $this->graph = $graph;
        $this->xml = $xmlNode;

        if($this->xml) {
            $dataNode =$this->xml->xPath('parent::object');
            if(count($dataNode)) {
                $this->dataNode = $dataNode[0];
            }
        }
        $this->extractFields();
    }

    /**
     * @param Relation $relation
     * @return Entity
     */
    public function getTargetEntityFromRelation($relation)
    {
        $targetEntity = null;

        if ($relation->getFrom() === $this) {
            $targetEntity = $relation->getTo();
        }
        else {
            foreach($this->getParentEntities() as $parentEntity) {
                if ($relation->getFrom() === $parentEntity) {
                    $targetEntity = $relation->getTo();
                    break;
                }
            }
        }

        if(!$targetEntity) {
            $targetEntity = $relation->getFrom();
        }
        return $targetEntity;
    }

    public function addRelation($relation)
    {
        $this->relations[] = $relation;
        return $this;
    }


    public function inherit($abstractEntity)
    {
        $this->parentEntities[$abstractEntity->getName()] = $abstractEntity;
        return $this;
    }


    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }


    public function createPrimaryKeyField()
    {
        $this->primaryKey = new Field($this->graph);
        $this->primaryKey->setType(Field::TYPE_AUTO_ID);
        $this->primaryKey->setId('id');
        $this->primaryKey->setName('id');

        // $this->fields['id'] = $this->primaryKey;
        $this->fields = ['id' => $this->primaryKey] + $this->fields;
        // array_unshift($this->fields, $this->primaryKey);

        return $this;
    }


    public function getVirtualFields()
    {
        $fields = $this->virtualFields;

        $parentEntities = $this->getParentEntities();

        foreach($parentEntities as $parentEntity) {
            $fields = array_merge($fields, $parentEntity->getVirtualFields());
        }
        return $fields;
    }


    public function getRelatedEntities($getObject = true)
    {
        $entities = [];
        $relations = $this->getRelations();
        foreach($relations as $relation) {
            $entity = false;

            if($relation->getFrom() === $this) {
                $entity = $relation->getTo();

            }
            elseif($relation->getTo() === $this) {
                $entity = $relation->getFrom();
            }

            if($entity) {

                $descriptor = [
                    'type' => $relation->getType(),
                    'label' => $relation->getLabel(),
                    'entity' => $entity->getName(),
                    'cardinality' => $relation->getToCardinality(),

                ];


                if($getObject) {
                    $descriptor['entity'] = $entity;

                }
                $entities[$entity->getName()] = $descriptor;
            }
        }

        return $entities;
    }


    /**
     * @return Relation[]
     */
    public function getRelations()
    {
        $relations = $this->relations;

        $parentEntities = $this->getParentEntities();
        foreach($parentEntities as $parentEntity) {
            $relations = array_merge($relations, $parentEntity->getRelations());
        }
        return $relations;
    }


    /**
     * @return AbstractEntity[]
     */
    public function getParentEntities($getObject = true)
    {
        $parentEntities = (array) $this->parentEntities;

        foreach($parentEntities as $parentEntity) {
            if ($parentEntity->getName() != $this->getName()) {
                $parentEntities = array_merge($parentEntities, $parentEntity->getParentEntities());
            }
        }

        $parentsByName = [];
        foreach($parentEntities as $parentEntity) {
            if($getObject) {
                $parentsByName[$parentEntity->getName()] = $parentEntity;
            }
            else {
                $parentsByName[] = $parentEntity->getName();
            }
        }


        return $parentsByName;
    }


    /**
     * @return Field[]
     */
    public function getFields()
    {
        $fields = $this->fields;
        $fields = array_merge($fields, $this->getParentFields());
        return $fields;
    }

    /**
     * @return Field[]
     */
    public function getParentFields()
    {
        $fields = [];
        $parentEntities = $this->getParentEntities();

        foreach($parentEntities as $parentEntity) {
            if($parentEntity->getName() != $this->getName()) {
                $parentFields = $parentEntity->getFields();

                foreach($parentFields as $field) {
                    $field->inherited(true, $parentEntity);
                }


                $fields = array_merge($fields, $parentFields);
            }
        }

        return $fields;
    }

    public function isReal(): bool
    {
        return false;
    }


    public function extractFields(): static
    {
        $query = '//mxCell[@parent="' . $this->getId()  . '"]';
        $nodes = $this->xml->xPath($query);

        foreach($nodes as $node) {
            $field = new Field($this, $node);
            if(!preg_match('`^#`', $field->getName())) {
                $this->fields[$field->getName()] = $field;
            }
            else {
                $this->virtualFields[$field->getName()] = $field;
            }
        }

        return $this;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function getName(): string
    {
        if(!$this->name) {
            $this->name = $this->getValue();
        }
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }



    public function getData(string $key = null): ?string
    {
        if($key === null) {
            $data = [];
            if($this->dataNode) {
                foreach($this->dataNode->attributes() as $attributeName => $value) {
                    $data[$attributeName] = (string) $value;
                }
            }
            return $data;
        }
        else {
            if ($this->dataNode) {

                if(array_key_exists($key, $this->dataNode)) {
                    return (string) $this->dataNode[$key];
                }
            }
        }
        return null;
    }

    public function getValue(): string
    {
        if($this->dataNode) {
            return (string) $this->dataNode['label'];
        }
        else {
            return (string) $this->xml['value'];
        }
    }

    public function getId()
    {

        if(!$this->id) {
            if($this->dataNode) {
                $this->id = (string) $this->dataNode['id'];
            }
            else {
                $this->id = (string) $this->xml['id'];;
            }
        }

        return $this->id;
    }

    public function getJSON()
    {
        return json_encode($this, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    public function jsonSerialize(): mixed
    {
        return [
            '_metadata' => [
                'id' => $this->getId(),
                'isAbstract' => true
            ],
            'name' => $this->getName(),
            'relatedEntities' => $this->getRelatedEntities(false),
            'parents' => $this->getParentEntities(false),
            'data' => $this->getData(),
            'fields' => $this->getFields(),
            'parentFields' => $this->getParentFields(),
        ];
    }
}
