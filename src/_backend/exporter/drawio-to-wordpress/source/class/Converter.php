<?php

namespace JDLX\DrawioConverter\Wordpress;


class Converter
{

    protected $graph;

    public function __construct($graph)
    {
        $this->graph = $graph;
    }


    public function getCode()
    {
        $code = '';

        foreach($this->graph->getEntities() as $entity) {
            if($entity->isReal()) {

                $entityExporter = new Entity($entity);

                $code .= $entityExporter->getCode();
            }
            else {

            }
        }


        return $code;

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

