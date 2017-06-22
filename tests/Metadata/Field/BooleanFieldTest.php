<?php

namespace Tequila\MongoDB\ODM\Tests\Metadata\Field;

use PHPUnit\Framework\TestCase;
use Tequila\MongoDB\ODM\Code\DocumentGenerator;
use Tequila\MongoDB\ODM\Code\PropertyGenerator;
use Tequila\MongoDB\ODM\Metadata\Field\BooleanField;
use Zend\Code\Generator\MethodGenerator;

class BooleanFieldTest extends TestCase
{
    public function testGetType()
    {
        $this->assertSame('bool', $this->getField()->getType());
    }

    public function testGenerateDocument()
    {
        $documentGenerator = $this->createMock(DocumentGenerator::class);
        $documentGenerator
            ->expects($this->once())
            ->method('addProperty')
            ->with($this->callback(function (PropertyGenerator $property) {
                $this->assertSame('boolField', $property->getName());
                $this->assertNull($property->getDefaultValue()->getValue());
                $this->assertSame(PropertyGenerator::VISIBILITY_PRIVATE, $property->getVisibility());

                return true;
            }));

        $documentGenerator
            ->expects($this->exactly(2))
            ->method('addMethod')
            ->withConsecutive(
                [
                    $this->callback(function (MethodGenerator $method) {
                        $this->assertSame('isBoolField', $method->getName());
                        $this->assertSame('?bool', $method->getReturnType()->generate());
                        $this->assertSame('return $this->boolField;', $method->getBody());
                        $this->assertCount(0, $method->getParameters());

                        return true;
                    }),
                ],
                [
                    $this->callback(function (MethodGenerator $method) {
                        $this->assertSame('setBoolField', $method->getName());
                        $this->assertNull($method->getReturnType());
                        $this->assertSame('$this->boolField = $boolField;', $method->getBody());
                        $this->assertCount(1, $method->getParameters());

                        return true;
                    }),
                ]
            );

        /* @var DocumentGenerator $documentGenerator */
        $this->getField()->generateDocument($documentGenerator);
    }

    private function getField(bool $defaultValue = null): BooleanField
    {
        return new BooleanField('boolField', 'bool_field', $defaultValue);
    }
}
