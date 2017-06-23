<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\Generator\AbstractGenerator;

interface FieldMetadataInterface
{
    /**
     * @return string
     */
    public function getPropertyName(): string;

    /**
     * @return string
     */
    public function getDbFieldName(): string;

    /**
     * @param AbstractGenerator $proxyGenerator
     *
     * @return string
     */
    public function getUnserializationCode(AbstractGenerator $proxyGenerator): string;

    /**
     * @return string
     */
    public function getSerializationCode(): string;

    /**
     * @param AbstractGenerator $proxyGenerator
     */
    public function generateProxy(AbstractGenerator $proxyGenerator);

    /**
     * @param DocumentGenerator $documentGenerator
     */
    public function generateDocument(DocumentGenerator $documentGenerator);

    /**
     * @return string
     */
    public function getType(): string;
}
