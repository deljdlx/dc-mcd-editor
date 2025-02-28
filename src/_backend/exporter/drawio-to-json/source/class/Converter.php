<?php

namespace JDLX\DrawioConverter\JSON;


class Converter
{

    protected $graph;

    public function __construct($graph)
    {
        $this->graph = $graph;
    }


    public function getJSON()
    {
        $data = [
            'entities' => [],
            'relations' => [],
        ];

        foreach($this->graph->getEntities() as $entity) {
            $data['entities'][$entity->getName()] = $entity;
        }

        foreach($this->graph->getAbstractEntities() as $entity) {
            $data['abstractEntities'][$entity->getName()] = $entity;
        }

        foreach($this->graph->getRelations() as $relatios) {
            $data['relations'][] = $relatios;
        }


        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        /*
        foreach($this->graph->getRelations() as $relation) {
            if($relation->isNN()) {

                $exporter = new RelationTable($relation->getRelationTable());
                $sql .= $exporter->getSQL($dropIfExists);
            }
        }
        return $sql;
        */
    }
}

