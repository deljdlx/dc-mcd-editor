<?php

namespace JDLX\DrawioConverter\SQLExporter\MySQL;


class Converter
{

    protected $graph;

    public function __construct($graph)
    {
        $this->graph = $graph;
    }


    public function getSQL($dropIfExists = false)
    {
        $sql = '';
        foreach($this->graph->getEntities() as $entity) {
            if($entity->isReal()) {
                $entityExporter = new Entity($entity);
                $sql .= $entityExporter->getSQL($dropIfExists) . "\n";
            }
            else {

            }
        }
        foreach($this->graph->getRelations() as $relation) {
            if($relation->isNN()) {

                $exporter = new RelationTable($relation->getRelationTable());
                $sql .= $exporter->getSQL($dropIfExists);
            }
        }
        return $sql;
    }
}

