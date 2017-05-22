<?php

namespace Tequila\MongoDB\ODM\Proxy;

interface UpdateBuilderInterface
{
    /**
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function addAllToSet(string $field, array $values);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function addToSet(string $field, $value);

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function popFirst(string $field);

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function popLast(string $field);

    /**
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function pullAll(string $field, array $values);

    /**
     * @param string $field
     * @param $condition
     *
     * @return mixed
     */
    public function pull(string $field, $condition);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function push(string $field, $value);

    /**
     * @param string $field
     * @param array  $values
     *
     * @return mixed
     */
    public function pushAll(string $field, array $values);

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function currentDate(string $field);

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function currentTimestamp(string $field);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function increment(string $field, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function multiply(string $field, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function min(string $field, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function max(string $field, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function setOnInsert(string $field, $value);

    /**
     * @param string $field
     * @param $value
     *
     * @return mixed
     */
    public function set(string $field, $value);

    /**
     * @param string $field
     *
     * @return mixed
     */
    public function unsetField(string $field);
}
