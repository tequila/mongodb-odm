<?php

namespace Tequila\MongoDB\ODM\Code;

class PropertyGenerator extends \Zend\Code\Generator\PropertyGenerator
{
    public function generate()
    {
        return str_replace(' = null', '', parent::generate());
    }
}
