<?php
namespace db;
/**
 * Constraint represents the metadata of a table constraint.
 *
 */
class Constraint extends \base\BaseObject
{
    /**
     * @var string[]|null list of column names the constraint belongs to.
     */
    public $columnNames;
    /**
     * @var string|null the constraint name.
     */
    public $name;
}
