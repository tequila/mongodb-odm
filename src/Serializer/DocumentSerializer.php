<?php

namespace Tequila\MongoDB\ODM\Serializer;

use Tequila\MongoDB\ODM\DocumentMetadataFactoryInterface;
use Tequila\MongoDB\ODM\SerializerFactory;

class DocumentSerializer implements SerializerInterface
{
    /**
     * @var \Closure
     */
    private $serializerClosure;

    public function __construct(SerializerFactory $serializerFactory, DocumentMetadataFactoryInterface $metadataFactory)
    {
        $documentSerializer = $this;
        $this->serializerClosure = function ($documentClass) use ($serializerFactory, $metadataFactory, $documentSerializer) {
            $data = [];
            $metadata = $metadataFactory->getDocumentMetadata($documentClass);

            foreach ($metadata->getFieldsMetadata() as $fieldMetadata) {
                $fieldValue = $this->{$fieldMetadata->getPropertyName()};
                if (null !== $fieldValue) {
                    $serializerClass = $fieldMetadata->getSerializerClass();
                    $serializer = DocumentSerializer::class === $serializerClass
                        ? $documentSerializer
                        : $serializerFactory->getSerializer($serializerClass);
                    $serializerOptions = $fieldMetadata->getSerializerOptions();

                    $fieldValue = $serializer->serialize(
                        $fieldValue,
                        $serializerOptions
                    );
                }

                $data[$fieldMetadata->getDbFieldName()] = $fieldValue;
            }

            return $data;
        };
    }

    public function serialize($document, array $options = [])
    {
        $closure = $this->serializerClosure;

        return $closure->call(
            $document,
            $options['documentClass']
        );
    }
}
