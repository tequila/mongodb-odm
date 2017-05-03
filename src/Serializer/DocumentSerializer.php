<?php

namespace Tequila\MongoDB\ODM\Serializer;

use Tequila\MongoDB\ODM\DocumentMetadataFactoryInterface;
use Tequila\OptionsResolver\OptionsResolver;

class DocumentSerializer extends Serializer
{
    /**
     * @var OptionsResolver
     */
    private static $resolver;

    /**
     * @var \Closure
     */
    private $serializerClosure;

    public function __construct()
    {
        $this->serializerClosure = function (
            DocumentMetadataFactoryInterface $metadataFactory,
            SerializerFactory $serializerFactory,
            $documentClass,
            array $options
        ) {
            $data = [];
            $metadata = $metadataFactory->getDocumentMetadata($documentClass);

            foreach ($metadata->getFieldsMetadata() as $fieldMetadata) {
                $serializer = $serializerFactory->getSerializer($fieldMetadata->getSerializerClass());
                $serializerOptions = $fieldMetadata->getSerializerOptions();
                $serializerOptions += $options;

                $data[$fieldMetadata->getDbFieldName()] = $serializer->serialize(
                    $this->{$fieldMetadata->getPropertyName()},
                    $serializerOptions
                );
            }

            return $data;
        };
    }

    public function serialize($document, array $options = [])
    {
        $options = self::resolveOptions($options);
        $closure = $this->serializerClosure;

        return $closure->call(
            $document,
            $options['metadataFactory'],
            $this->factory,
            $options['documentClass'],
            $options
        );
    }

    /**
     * @param array $options
     * @return array
     */
    private static function resolveOptions(array $options)
    {
        if (!self::$resolver) {
            $resolver = new OptionsResolver();
            $resolver->setDefined([
                'documentClass',
                'metadataFactory',
            ]);

            $resolver
                ->setAllowedTypes('documentClass', 'string')
                ->setAllowedTypes('metadataFactory', DocumentMetadataFactoryInterface::class);

            self::$resolver = $resolver;
        }

        return self::$resolver->resolve($options);
    }
}