<?php

namespace Tequila\MongoDB\ODM;

use Tequila\MongoDB\ODM\Exception\LogicException;

class DefaultMetadataFactory implements DocumentMetadataFactoryInterface
{
    /**
     * @var array
     */
    private $metadataCache = [];

    /**
     * @inheritdoc
     */
    public function getDocumentMetadata($documentClass)
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

            if (!$reflection->hasMethod('getMetadata')) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s to have method "getMetadata()".',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $reflectionMethod = $reflection->getMethod('getMetadata');

            if (!$reflectionMethod->isPublic() || !$reflectionMethod->isStatic()) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s method "getMetadata()" to be public and static.',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $metadata = call_user_func([$documentClass, 'getMetadata']);
            if (!$metadata instanceof DocumentMetadata) {
                throw new LogicException(
                    sprintf(
                        'Method %s::getMetadata() must return %s instance, but returns %s.',
                        $documentClass,
                        DocumentMetadata::class,
                        \Tequila\MongoDB\getType($metadata)
                    )
                );
            }

            $this->metadataCache[$documentClass] = $metadata;
        }

        return $this->metadataCache[$documentClass];
    }
}