<?php
namespace JDLX\DrawioConverter;

class Relation implements \JsonSerializable
{
    public const TYPE_INHERIT = 'inherit';
    public const TYPE_RELATION = 'relation';

    protected $graph;
    protected $xml;

    /**
     * @var Entity
     */
    protected $from;

    /**
     * @var Entity
     */
    protected $to;

    protected $cardinalityNodes = [];

    /**
     * @var Cardinality
     */
    protected $fromCardinality;

    /**
     * @var Cardinality
     */
    protected $toCardinality;

    /**
     * @var string
     */
    protected $type;


    protected $id;


    public function __construct($graph, $xmlNode = null, $from = null, $to = null, $type = null)
    {
        $this->graph = $graph;
        $this->xml = $xmlNode;
        $this->from = $from;
        $this->to = $to;


        $this->type = self::TYPE_RELATION;

        if($type === null) {
            if(strpos((string) $xmlNode['style'], 'block')) {
                $this->type = self::TYPE_INHERIT;
            }
        }
        else {
            $this->type = $type;
        }


        if($this->type == static::TYPE_RELATION) {
            if($this->xml) {
                $this->extractCardinality();
            }

            $this->from->addRelation($this);
            $this->to->addRelation($this);

        }
        else if($this->type == static::TYPE_INHERIT) {
            $this->from->inherit($this->to);
        }
    }

    public function getType()
    {
        return $this->type;
    }


    public function foreignKeyOn(AbstractEntity $entity): bool
    {

        // entitiy having a self relation (parent_id)
        if($entity->getId() == $this->getTo()->getId() && $entity->getId() == $this->getFrom()->getId()) {
            $cardinality = $this->getFromCardinality();
            if($cardinality->requireForeignKey()) {
                return true;
            }

            $cardinality = $this->getToCardinality();
            if($cardinality->requireForeignKey()) {
                return true;
            }
        }
        elseif($entity->getId() == $this->getFrom()->getId()) {
            $cardinality = $this->getFromCardinality();
            if($cardinality->requireForeignKey()) {
                return true;
            }
        }
        elseif($entity->getId() == $this->getTo()->getId()) {
            $cardinality = $this->getToCardinality();
            if($cardinality->requireForeignKey()) {
                return true;
            }
        }

        foreach($entity->getParentEntities() as $parentEntity) {
            if($this->foreignKeyOn($parentEntity)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return RelationTable
     */
    public function getRelationTable()
    {

        $relationTable = new RelationTable($this->from, $this->to, $this);
        return $relationTable;
    }

    public function isNN()
    {
        if($this->getFromCardinality()->getMax() == 'n' && $this->getToCardinality()->getMax() == 'n') {
            return true;
        }
        return false;
    }

    /**
     * @return Entity
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @return Entity
     */
    public function getTo()
    {
        return $this->to;
    }

    public function setFromCardinality($min, $max)
    {
        $this->fromCardinality = new Cardinality("$min,$max");
        return $this;
    }

    public function setToCardinality($min, $max)
    {
        $this->toCardinality = new Cardinality("$min,$max");
        return $this;
    }



    /**
     * @return this
     */
    public function extractCardinality()
    {
        $query = '//mxCell[@parent="' . $this->getId() . '"]';
        $this->cardinalityNodes = $this->graph->xPath($query);
        return $this;
    }

    /**
     * @return Cardinality
     */
    public function getFromCardinality()
    {
        if(!$this->fromCardinality) {
            $min = null;
            $cardinality = null;
            foreach($this->cardinalityNodes as $node) {
                if($min === null || (float) $node->mxGeometry['x'] < $min) {
                    $cardinality = (string) $node['value'];
                    $min =  (float) $node->mxGeometry['x'];
                }
            }

            $this->fromCardinality = new Cardinality($cardinality);
        }
        return $this->fromCardinality;
    }

    /**
     * @return Cardinality
     */
    public function getToCardinality() {
        if (!$this->toCardinality) {
            $max = null;
            $cardinality = null;
            foreach ($this->cardinalityNodes as $node) {
                if ($max === null || (float) $node->mxGeometry['x'] > $max) {
                    $cardinality = (string) $node['value'];
                    $max =  (float) $node->mxGeometry['x'];
                }
            }
            $this->toCardinality = new Cardinality($cardinality);
        }
        return $this->toCardinality;
    }

    public function getLabel() {
        $max = null;
        $min = null;

        foreach($this->cardinalityNodes as $node) {
            if ($max === null || (float) $node->mxGeometry['x'] > $max) {
                $max =  (float) $node->mxGeometry['x'];
            }
            if($min === null || (float) $node->mxGeometry['x'] < $min) {

                $min =  (float) $node->mxGeometry['x'];
            }
        }

        foreach($this->cardinalityNodes as $node) {
            // a label has beed defined for the relation
            if((float) $node->mxGeometry['x'] > $min && (float) $node->mxGeometry['x'] < $max) {
                if((string) $node['value']) {
                    return (string) $node['value'];
                }
            }
        }

        /*
        if($this->getToCardinality()->getMax() == '1') {
            return "has one";
        }

        if($this->getToCardinality()->getMax() == 'n') {
            return "has many";
        }
        */

        return '';
    }

    /**
     * @return string
     */
    public function getId()
    {
        if(!$this->id) {
            if($this->xml) {
                $this->id =  (string) $this->xml['id'];
            }
            else {
                $this->id = uniqid();
            }
        }

        return $this->id;
    }

    public function jsonSerialize(): mixed
    {
        return [
            '_metadata' => [
                'id' => $this->getId(),
                'type' => $this->getType(),
            ],
            'label' => $this->getLabel(),
            'fromCardinality' => $this->getFromCardinality(),
            'toCardinality' => $this->getToCardinality(),
            'from' => $this->from,
            'to' => $this->to,
        ];
    }
}

