<?php

namespace JDLX\DrawioConverter\Wordpress;

use JDLX\DrawioConverter\Entity as McdEntity;


class Entity extends Driver
{


    /** @var McdEntity */
    protected $entity;


    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    /**
     * @return McdEntity
     */
    public function getEntity()
    {
        return $this->entity;
    }


    /**
     * @return string
     */
    public function getCode()
    {
        $entity = $this->getEntity();



        $fields = [];
        foreach($entity->getFields() as $field) {
            $fieldExporter = new Field($field);
            $fields[$field->getName()] = $fieldExporter->getCode();
        }



        $declaration = "
register_post_type(
    '" . $this->normalize($entity->getName()) . "',
    [
        'label' => '" . $entity->getName() . "',
        'public' => true,
        'hierarchical' => false,
        'menu_icon' => 'dashicons-food',
        'supports' => [" . implode(',', array_values($fields)) . "],
        'capability_type' => '" . $this->normalize($entity->getName()) . "',
        'map_meta_cap' => true,
        'show_in_rest' => true
    ]
);
        ";

        return $declaration;
    }

}
