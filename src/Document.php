<?php

namespace Tequila\MongoDB\ODM;

use MongoDB\BSON\ObjectID;
use MongoDB\BSON\Persistable;
use Tequila\MongoDB\DocumentInterface;
use Tequila\MongoDB\ODM\Exception\LogicException;

abstract class Document implements DocumentInterface, Persistable, DocumentManagerAwareInterface
{
    /**
     * @var ObjectID|null|mixed
     */
    protected $id;

    /**
     * @var DocumentManager
     */
    private $documentManager;

    /**
     * @return WriteModel\DeleteOneDocument
     */
    public function delete()
    {
        return $this->getDocumentManager()->deleteDocument($this);
    }

    /**
     * @return WriteModel\ReplaceOneDocument
     */
    public function replace()
    {
        return $this->getDocumentManager()->replaceDocument($this);
    }

    /**
     * @return WriteModel\UpdateOneDocument
     */
    public function update()
    {
        return $this->getDocumentManager()->updateDocument($this);
    }

    /**
     * @param DocumentManager $documentManager
     */
    final public function setDocumentManager(DocumentManager $documentManager)
    {
        $this->documentManager = $documentManager;
    }

    /**
     * @return mixed|ObjectID|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    protected function set($field, $value)
    {
        if ($this->getId()) {
            $this->update()->set($field, $value);
        }
    }

    /**
     * @return DocumentManager
     */
    private function getDocumentManager()
    {
        if (null === $this->documentManager) {
            throw new LogicException(
                'DocumentManager was not set to this document. Maybe document is new?'
            );
        }

        return $this->documentManager;
    }
}
