<?php

namespace Tequila\MongoDB\ODM;

interface BulkWriteBuilderAwareInterface
{
    /**
     * @param BulkWriteBuilder $builder
     */
    public function setBulkWriteBuilder(BulkWriteBuilder $builder);
}
