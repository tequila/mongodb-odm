<?php

namespace Tequila\MongoDB\ODM\Metadata\Field;

use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Proxy\ProxyGenerator;

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
     * @param ProxyGenerator $proxyGenerator
     *
     * @return string
     */
    public function getUnserializationCode(ProxyGenerator $proxyGenerator): string;

    /**
     * @return string
     */
    public function getSerializationCode(): string;

    /**
     * @param ProxyGenerator $proxyGenerator
     */
    public function generateProxy(ProxyGenerator $proxyGenerator);

    /**
     * @param DocumentGenerator $documentGenerator
     */
    public function generateDocument(DocumentGenerator $documentGenerator);

    /**
     * @return string
     */
    public function getType(): string;
}
