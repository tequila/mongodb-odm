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
     * {@inheritdoc}
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

            if (!$reflection->hasMethod('loadDocumentMetadata')) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s to have method "loadDocumentMetadata()".',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $reflectionMethod = $reflection->getMethod('loadDocumentMetadata');

            if (!$reflectionMethod->isPublic() || !$reflectionMethod->isStatic()) {
                throw new LogicException(
                    sprintf(
                        '%s requires document class %s method "loadDocumentMetadata" to be public and static.',
                        __CLASS__,
                        $documentClass
                    )
                );
            }

            $metadata = new DocumentMetadata($documentClass);
            call_user_func([$documentClass, 'loadDocumentMetadata'], $metadata);
            $this->metadataCache[$documentClass] = $metadata;
        }

        return $this->metadataCache[$documentClass];
    }
}
