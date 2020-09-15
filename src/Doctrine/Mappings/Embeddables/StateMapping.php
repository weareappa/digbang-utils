<?php

namespace Digbang\Utils\Doctrine\Mappings\Embeddables;

use LaravelDoctrine\Fluent\EmbeddableMapping;
use LaravelDoctrine\Fluent\Fluent;

abstract class StateMapping extends EmbeddableMapping
{
    /**
     * Load the object's metadata through the Metadata Builder object.
     */
    public function map(Fluent $builder)
    {
        $builder->text('value');
        $builder->jsonArray('log')->option('jsonb', true);
    }

    abstract public function mapFor();
}
