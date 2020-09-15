<?php

namespace Digbang\Utils\Doctrine\Mappings\Embeddables;

use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;

abstract class EnumMapping extends EmbeddableMapping
{
    /**
     * Load the object's metadata through the Metadata Builder object.
     */
    public function map(Fluent $builder)
    {
        $builder->text('value');
    }

    abstract public function mapFor();
}
