<?php

namespace JDLX\DrawioConverter\SQLExporter\MySQL;

use JDLX\DrawioConverter\Field as MCDField;
use JDLX\DrawioConverter\RelationTable as DrawioMCDConverterRelationTable;

class RelationTable extends Driver
{
    /**
     * @var DrawioMCDConverterRelationTable
     */
    protected $relationTable;

    public function __construct($relationTable)
    {
        $this->relationTable = $relationTable;
    }


    public function getSQL(bool $dropIfExists = false): string
    {

        $relation = $this->relationTable;
        $sql ='';


        if($dropIfExists) {
            $sql .= "-- ===========================================================\n";
            $sql .= "-- DROPPING TABLE FOR ENTITY " . $this->getTableName() . "\n";
            $sql .= "-- ===========================================================\n";
            $sql .= "DROP TABLE IF EXISTS {$this->escape($this->getTableName())};\n";
        }


        $sql .= "-- ============================================================================\n";
        $sql .= "-- RELATION TABLE CREATION BETWEEN `" . $relation->getFrom()->getName() . "` TABLE AND `" . $relation->getTo()->getName() . "` TABLE\n";
        $sql .= "-- ===========================================================================\n\n";

        $sql .= "CREATE TABLE " .$this->escape($this->getTableName()) . "(\n";
            if($relation->hasId()) {
                // $sql .= "    `id` BIGINT(20) UNSIGNED AUTO_INCREMENT,\n";

                //===========================================================
                $field = new MCDField();
                $field->setName('id');
                $field->setType(MCDField::TYPE_AUTO_ID);
                $fieldExporter = new Field($field);
                $sql .= $fieldExporter->getSQL() . ",\n";
            }

            $fromForeignKey = $relation->getFrom()->getName() . '_id';
            $toForeignKey = $relation->getTo()->getName() . '_id';


            //===========================================================
            $field = new MCDField();
            $field->setName($fromForeignKey);
            $field->setType($relation->getFrom()->getPrimaryKey()->getType());
            $fieldExporter = new Field($field);
            $sql .= $fieldExporter->getSQL(false) . ",\n";
            //===========================================================

            $field = new MCDField();
            $field->setName($toForeignKey);
            $field->setType($relation->getTo()->getPrimaryKey()->getType());
            $fieldExporter = new Field($field);
            $sql .= $fieldExporter->getSQL(false) . "";
            //===========================================================


            if($relation->isTimestamped()) {
                $sql .= ",\n" . $this->relationTable->getTimestampFields();
            }

            if ($relation->hasId()) {
                $sql .= ",\nPRIMARY KEY (`id`)";
                /* not needed, foreign keys create indexes automaticaly
                $sql .=
                "INDEX " . $this->getTableName(). "_" .  $relation->getFrom()->getName() . "_" . $relation->getTo()->getName() . " (" .
                    $this->escape( $relation->getFrom()->getName() . "_id") . "," .
                    $this->escape($relation->getTo()->getName() . "_id") . ")" .
                    "\n"
                ;
                */
            }
            else {
                $sql .= ",\nPRIMARY KEY (" . $fromForeignKey . ", " . $toForeignKey . ")\n";
            }

            $sql .= ',' . "\n";
            $sql .=
                '    CONSTRAINT `FK_' . $this->getTableName() . '_' . $fromForeignKey . '`'.
                ' FOREIGN KEY (`' . $fromForeignKey . '`) REFERENCES `' . $relation->getFrom()->getName() . '` (`' .  $relation->getFrom()->getPrimaryKey()->getName() . '`)'.
                ' ON UPDATE NO ACTION ON DELETE NO ACTION' . ",\n";
                $sql .=
                '    CONSTRAINT `FK_' . $this->getTableName() . '_' . $toForeignKey . '`'.
                ' FOREIGN KEY (`' . $toForeignKey . '`) REFERENCES `' . $relation->getTo()->getName() . '` (`' .  $relation->getTo()->getPrimaryKey()->getName() . '`)'.
                ' ON UPDATE NO ACTION ON DELETE NO ACTION';

        $sql .= ")\n";

        $sql .= "COLLATE='utf8mb4_unicode_ci'\nENGINE='InnoDB';\n";

        return $sql;
    }


    /**
     * @return string
     */
    public function getTableName()
    {
        if($relation = $this->relationTable->getRelation()) {
            if($relation->getLabel()) {
                return $relation->getLabel();
            }
        }
        return $this->relationTable->getFrom()->getName() . '_' .$this->relationTable->getTo()->getName();
    }
}
