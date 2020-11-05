<?php
namespace db\pgsql;

use db\ArrayExpression;
use db\ExpressionBuilderInterface;
use db\ExpressionBuilderTrait;
use db\ExpressionInterface;
use db\JsonExpression;
use db\Query;
use helpers\Json;

/**
 * Class JsonExpressionBuilder builds [[JsonExpression]] for PostgreSQL DBMS.
 */
class JsonExpressionBuilder implements ExpressionBuilderInterface
{
    use ExpressionBuilderTrait;

    /**
     * {@inheritdoc}
     * @param JsonExpression|ExpressionInterface $expression the expression to be built
     */
    public function build(ExpressionInterface $expression, array &$params = [])
    {
        $value = $expression->getValue();

        if ($value instanceof Query) {
            list ($sql, $params) = $this->queryBuilder->build($value, $params);
            return "($sql)" . $this->getTypecast($expression);
        }
        if ($value instanceof ArrayExpression) {
            $placeholder = 'array_to_json(' . $this->queryBuilder->buildExpression($value, $params) . ')';
        } else {
            $placeholder = $this->queryBuilder->bindParam(Json::encode($value), $params);
        }

        return $placeholder . $this->getTypecast($expression);
    }

    /**
     * @param JsonExpression $expression
     * @return string the typecast expression based on [[type]].
     */
    protected function getTypecast(JsonExpression $expression)
    {
        if ($expression->getType() === null) {
            return '';
        }

        return '::' . $expression->getType();
    }
}
