<?php
declare(strict_types=1);

namespace DGS;


interface AbstractQueryBuilderInterface
{
    /**
     * @param array|string $fields
     * @return AbstractQueryBuilderInterface
     */
    public function select($fields): AbstractQueryBuilderInterface;

    /**
     * @param array|string|AbstractQueryBuilderInterface $tables
     * @return AbstractQueryBuilderInterface
     */
    public function from($tables): AbstractQueryBuilderInterface;

    /**
     * @param array|string $conditions
     * @return AbstractQueryBuilderInterface
     */
    public function where($conditions): AbstractQueryBuilderInterface;

    /**
     * @param array|string $fields
     * @return AbstractQueryBuilderInterface
     */
    public function groupBy($fields): AbstractQueryBuilderInterface;

    /**
     * @param array|string $conditions
     * @return AbstractQueryBuilderInterface
     */
    public function having($conditions): AbstractQueryBuilderInterface;

    /**
     * @param array|string $fields
     * @return AbstractQueryBuilderInterface
     */
    public function orderBy($fields): AbstractQueryBuilderInterface;

    /**
     * @param int|string $limit
     * @return AbstractQueryBuilderInterface
     */
    public function limit($limit): AbstractQueryBuilderInterface;

    /**
     * @param int|string $offset
     * @return AbstractQueryBuilderInterface
     */
    public function offset($offset): AbstractQueryBuilderInterface;

    /**
     * @return string|null
     */
    public function build(): ?string;

    /**
     * @return string|null
     */
    public function buildCount(): ?string;
}