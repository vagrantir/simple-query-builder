<?php
declare(strict_types=1);

namespace DGS;


use http\Exception;
use http\Exception\BadConversionException;

abstract class AbstractQueryBuilder implements AbstractQueryBuilderInterface
{
    protected $_fields;

    /**
     * @var array
     */
    private $_select = [];
    /**
     * @var array|AbstractQueryBuilderInterface|string
     */
    private $_from = [];
    /**
     * @var array
     */
    private $_where = [];
    /**
     * @var array
     */
    private $_groupBy = [];
    /**
     * @var array
     */
    private $_orderBy = [];
    /**
     * @var array|string
     */
    private $_having = [];
    /**
     * @var int
     */
    private $_limit = null;
    /**
     * @var int
     */
    private $_offset = null;

    /**
     * AbstractQueryBuilder constructor.
     * @param array $config
     */
    public function __construct(?array $config)
    {
        $this->_fields = $config['fields'] ?? [];
    }

    public function select($fields): AbstractQueryBuilderInterface
    {
        $this->_select = $fields;
        return $this;
    }

    public function from($tables): AbstractQueryBuilderInterface
    {
        $this->_from = is_string($tables) ? [$tables] : $tables;
        return $this;
    }

    public function where($conditions): AbstractQueryBuilderInterface
    {
        $this->_where = $conditions;
        return $this;
    }

    public function groupBy($fields): AbstractQueryBuilderInterface
    {
        $this->_groupBy = $fields;
        return $this;
    }

    public function having($conditions): AbstractQueryBuilderInterface
    {
        $this->_having = $conditions;
        return $this;
    }

    public function orderBy($fields): AbstractQueryBuilderInterface
    {
        $this->_orderBy = $fields;
        return $this;
    }

    public function limit($limit): AbstractQueryBuilderInterface
    {
        $this->_limit = $limit;
        return $this;
    }

    public function offset($offset): AbstractQueryBuilderInterface
    {
        $this->_offset = $offset;
        return $this;
    }

    public function build(): string
    {
        return $this->buildQuery();
    }

    protected function buildQuery(): string
    {
        return \trim(\implode(" ", [
            $this->buildSelect(),
            $this->buildFrom(),
            $this->buildWhere(),
            $this->buildGroupBy(),
            $this->buildOrderBy(),
            $this->buildLimit(),
        ])) ?: "";
    }

    protected function buildSelect(): string
    {
        $fields = "*";

        if (!empty($this->_select)) {
            $fields = $this->buildFields();
        }

        return "select {$fields}";
    }

    protected function buildFields(): string
    {
        $fields = [];

        foreach ($this->_select as $alias => $field) {
            $fields[] = $this->buildField($field, $alias);
        }

        return \trim(\implode(", ", $fields));
    }

    protected function buildField($field, $alias): string
    {
        $params = [];

        $field = ($this->_fields[$field] ?? $field);

        /**
         * if $field = ["field"=>$someValue, ...any parameters]
         */
        if (\is_array($field) and $field['field'] ?? false) {
            $params = $field;
            unset($params['field']);
            $field = $field['field'];
        }

        if (\is_callable($field)) {
            $field = $field($params);
        }

        $alias = isset($alias) && !is_numeric($alias) ? " as {$alias}" : "";

        return "{$field}{$alias}";
    }

    protected function buildFrom(): string
    {
        if (empty($this->_from)) {
            throw new \LogicException('It`s MUST be set at least one table for select');
        }

        $tables = [];

        foreach ($this->_from as $alias => $table) {
            $tables[] = $this->buildTable($table, $alias);
        }

        return "from " . \trim(\implode(", ", $tables));
    }

    protected function buildTable($table, $alias): string
    {
        if (\is_object($table) && \class_implements($table)[AbstractQueryBuilderInterface::class] ?? false) {
            $table = $table->build();
        }

        if (!\is_string($table)) {
            throw new \LogicException(printf('Invalid type of table. Must be a string but %s given', \gettype($table)));
        }

        $alias = isset($alias) && !is_numeric($alias) ? " {$alias}" : "";
        return "({$table}){$alias}";
    }

    abstract protected function buildWhere(): string;

    protected function buildGroupBy(): string
    {
        $groupBy = "";

        if (!empty($this->_groupBy)) {
            $groupBy = is_array($this->_groupBy) ? \trim(\implode(", ", $this->_groupBy)) : $this->_groupBy;
            $having = $this->buildHaving();
            $groupBy = \trim("group by {$groupBy} {$having}");
        }

        return $groupBy;
    }

    protected function buildHaving(): ?string
    {
        return $this->_having ? "having {$this->_having}" : "";
    }

    protected function buildOrderBy(): ?string
    {
        $orderBy = "";

        if (!empty($this->_orderBy)) {
            $orderBy = is_array($this->_orderBy) ? \trim(\implode(", ", $this->_orderBy)) : $this->_orderBy;
            $orderBy = "order by {$orderBy}";
        }

        return $orderBy;
    }

    protected function buildLimit(): ?string
    {
        $limit = "";

        if (!empty($this->_limit)) {
            $offset = $this->buildOffset();
            $limit = "limit {$this->_limit} {$offset}";
        }

        return $limit;
    }

    protected function buildOffset(): ?string
    {
        $offset = "";

        if (!empty($this->_offset)) {
            $offset = "offset {$this->_offset}";
        }

        return $offset;
    }

    public function buildCount(): string
    {
        return $this->buildCountQuery();
    }

    protected function buildCountQuery()
    {
        return \trim(\implode(" ", [
            "select count(*)",
            $this->buildFrom(),
            $this->buildWhere(),
            $this->buildGroupBy(),
            $this->buildOrderBy(),
            $this->buildLimit(),
        ])) ?: "";
    }
}