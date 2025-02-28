<?php

namespace JDLX\DrawioConverter\SQLExporter\MySQL;

use JDLX\DrawioConverter\Entity as GenericEntity;
use JDLX\DrawioConverter\Field as McdField;
use JDLX\DrawioConverter\Relation;

class Entity extends Driver
{

    protected $defaultCollate = 'utf8mb4_unicode_ci';
    protected $defaultPrimaryKeyType = 'INT(16) UNSIGNED';

    protected GenericEntity $entity;


    public function __construct(GenericEntity $entity)
    {
        $this->entity = $entity;
    }

    public function getEntity(): GenericEntity
    {
        return $this->entity;
    }


    /**
     * @return string
     */
    public function getSQL(bool $dropIfExists = false): string
    {
        $entity = $this->getEntity();
        $instructions = [];
        $indexes = [];
        $foreignEntities = [];

        $sql ='';


        if($dropIfExists) {
            $sql .= "-- ===========================================================\n";
            $sql .= "-- DROPPING TABLE FOR ENTITY `" . $entity->getName() . "`\n";
            $sql .= "-- ===========================================================\n";
            $sql .= "DROP TABLE IF EXISTS {$this->escape($entity->getName())};\n";
        }

        $sql .= "-- ===========================================================\n";
        $sql .= "-- CREATE TABLE FOR ENTITY `" . $entity->getName() . "`\n";
        $sql .= "-- ===========================================================\n";


        $sql .= 'CREATE TABLE ' . $this->escape($entity->getName()) . ' (' . "\n";

            foreach($entity->getFields() as $field) {
                $fieldExporter = new Field($field);
                $instructions[$field->getName()] = $fieldExporter->getSQL();
            }

            $foreignKeys = $this->getForeignKeys();
            $instructions = array_merge($instructions, $foreignKeys['instructions']);
            $indexes = array_merge($indexes, $foreignKeys['indexes']);
            $foreignEntities = array_merge($foreignEntities, $foreignKeys['foreignEntities']);

            $sql .= '    ' . implode(",\n    ", $instructions);
            $sql .= ",\n";

            // handling timestamp fields==============
            if($entity->isTimestamped()) {
                $sql .= $entity->getTimestampFields() . ",\n";
            }
            // =======================================


            $sql .= '    PRIMARY KEY (`' . $entity->getIdFieldName() . '`)';

            // handling indexes
            if(count($indexes)) {
                $sql .= ',' . "\n";
                foreach($indexes as $indexName => $fieldName) {
                    $sql .= '    INDEX `' . $indexName . '` (`' . $fieldName . '`)' . ",\n";
                }
                $sql = substr($sql, 0, -2);
            }

            // handling foreign keys
            if(count($foreignEntities)) {
                $sql .= ',' . "\n";
                foreach ($foreignEntities as $fieldName => $foreignEntity) {
                    $sql .=
                        '    CONSTRAINT `FK_' . $this->entity->getName() . '_' . $fieldName . '`'.
                        ' FOREIGN KEY (`' . $fieldName . '`) REFERENCES `' . $foreignEntity->getName() . '` (`' . $foreignEntity->getPrimaryKey()->getName() . '`)'.
                        ' ON UPDATE NO ACTION ON DELETE NO ACTION' . ",\n";
                    $sql = substr($sql, 0, -2);
                }
            }

        $sql .= "\n". ')' ."\n";

        $sql .= 'COLLATE="' . $this->defaultCollate . '"' ."\n";
        $sql .= 'ENGINE="InnoDB"' .";\n";

        return $sql;
    }

    // IMPORTANT handle relation x,1  x,1
    // BUG relation n/n sur meme entité
    protected function getForeignKeys()
    {
        $entity = $this->getEntity();
        $instructions = [];
        $indexes = [];
        $foreignEntities = [];

        $generatedRelations = [];
        foreach($entity->getRelations() as $relation) {

            $fieldName = '';

            if($relation->foreignKeyOn($entity)) {

                // relation already handled
                if(isset($generatedRelations[$relation->getId()])) {
                    continue;
                }

                // register relation
                $generatedRelations[$relation->getId()] = true;

                $targetEntity = $entity->getTargetEntityFromRelation($relation);

                $fieldName = $this->getForeignKeyName($relation);

                if(
                    ($relation->getFromCardinality()->getMax() == 1 && $relation->getToCardinality()->getMax() == 'n')
                    || ($relation->getToCardinality()->getMax() == 1 && $relation->getFromCardinality()->getMax() == 'n')
                ) {
                    $instructions[] = "-- ======= FOREIGN KEY TO `{$targetEntity->getName()}`========";

                    $field = new McdField();
                    $field->setName($fieldName);
                    $field->setType($targetEntity->getPrimaryKey()->getType());

                    $fieldExporter = new Field($field);
                    $instructions[] = $fieldExporter->getSQL(false);

                    $indexes[$fieldName] = $fieldName;

                    $foreignEntities[$fieldName] = $targetEntity;
                }

            }
        }

        return [
            'instructions' => $instructions,
            'indexes' => $indexes,
            'foreignEntities' => $foreignEntities,
        ];
    }


    /**
     * @param Relation $relation
     * @return string
     */

    protected function getForeignKeyName($relation)
    {
        $entity =  $this->getEntity();
        $fieldName = '';

        $targetEntity = $entity->getTargetEntityFromRelation($relation);

        // handling relation name
        if($relation->getLabel()) {
            $fieldName = $relation->getLabel();
        }

        if(!$fieldName) {
            if($this->isSelfRelation($relation)) {
                $fieldName = 'parent_id';
            }
            else {
                $fieldName = $targetEntity->getName() . '_id';
            }
        }

        return $fieldName;
    }

    public function isSelfRelation(Relation $relation): bool
    {
        $entity =  $this->getEntity();
        $targetEntity = $entity->getTargetEntityFromRelation($relation);

        if($entity === $targetEntity) {
            return true;
        }
        return false;
    }
}
