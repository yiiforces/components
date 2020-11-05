<?php
namespace db\conditions;

/**
 * Condition that connects two or more SQL expressions with the `AND` operator.
 */
class OrCondition extends ConjunctionCondition
{
    /**
     * Returns the operator that is represented by this condition class, e.g. `AND`, `OR`.
     *
     * @return string
     */
    public function getOperator()
    {
        return 'OR';
    }
}
