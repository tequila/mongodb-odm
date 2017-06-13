<?php

namespace Tequila\MongoDB\ODM\Code;

class FileGenerator extends \Zend\Code\Generator\FileGenerator
{
    public function generate()
    {
        $code = parent::generate();
        foreach ($this->getClass()->getUses() as $useString) {
            $parts = explode(' as ', $useString);
            if (2 === count($parts)) {
                $resolved = $parts[1];
            } elseif (false !== $lastSeparatorPosition = strrpos($parts[0], '\\')) {
                $resolved = substr($parts[0], $lastSeparatorPosition + 1);
            } else {
                $resolved = $parts[0];
            }

            $code = str_replace('\\'.ltrim($parts[0], '\\'), $resolved, $code);
        }

        return $code;
    }
}