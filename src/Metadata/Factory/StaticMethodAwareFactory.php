<?php

namespace Tequila\MongoDB\ODM\Metadata\Factory;

use Tequila\MongoDB\ODM\Metadata\ClassMetadata;
use Tequila\MongoDB\ODM\Exception\LogicException;

class StaticMethodAwareFactory implements MetadataFactoryInterface
{
    /**
     * @var array
     */
    private $metadataCache = [];

    /**
     * {@inheritdoc}
     */
    public function getClassMetadata($documentClass): ClassMetadata
    {
        if (!array_key_exists($documentClass, $this->metadataCache)) {
            if (!class_exists($documentClass)) {
                throw new LogicException(sprintf('Document class %s does not exist.', $documentClass));
            }

            $reflection = new \ReflectionClass($documentClass);
            if ($reflection->isAbstract()) {
                throw new LogicException(
                    sprintf('Document class %s must be not abstract.', $documentClass)
                );
            }

            if (!$reflection->hasMethod('loadClassMetadata')) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s to have method "loadClassMetadata()".',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $reflectionMethod = $reflection->getMethod('loadClassMetadata');

            if (!$reflectionMethod->isPublic() || !$reflectionMethod->isStatic()) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s method "loadClassMetadata" to be public and static.',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $metadata = new ClassMetadata($documentClass);
            call_user_func([$documentClass, 'loadClassMetadata'], $metadata);
            if ($metadata->isIdentifiable() && null === $metadata->getPrimaryKeyField()) {
                $metadata->addObjectIdField('id', '_id', true);
            }
            $this->metadataCache[$documentClass] = $metadata;
        }

        return $this->metadataCache[$documentClass];
    }
}
