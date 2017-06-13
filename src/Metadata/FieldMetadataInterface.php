<?php

namespace Tequila\MongoDB\ODM\Metadata;

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
     * @param ProxyGenerator $proxyGenerator
     *
     * @return string
     */
    public function getSerializationCode(ProxyGenerator $proxyGenerator): string;

    /**
     * @return string
     */
    public function getReturnType(): string;

    /**
     * @return string
     */
    public function getTypeHint(): string;

    /**
     * @return mixed
     */
    public function getPropertyDefaultValue(): string;

    /**
     * @param ProxyGenerator $proxyGenerator
     */
    public function generateProxy(ProxyGenerator $proxyGenerator): void;
}
