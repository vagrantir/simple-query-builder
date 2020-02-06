<?php

namespace PHPUnit\Test;

use DGS\SimpleQueryBuilder;
use PHPUnit\Framework\TestCase;

class SimpleQueryBuilderTest extends TestCase
{
    public function testSimpleQueryBuilder()
    {
        $tableName = "test_table";
        $fieldName = "field";
        $value = "123";
        $regexpAll = "/^\s*select\s+\*\s+from\s+\({$tableName}\)\s+order\s+by\s+{$fieldName}/";
        $regexpField = "/^\s*select\s+{$value}\s+as\s+{$fieldName}\s+from\s+\({$tableName}\)\s+order\s+by\s+{$fieldName}/";

        $builder = new SimpleQueryBuilder([$fieldName => $value]);

        $sql = $builder
            ->from($tableName)
            ->orderBy($fieldName)
            ->build();

        $this->assertRegExp($regexpAll, $sql);

        $sql = $builder
            ->select([$fieldName => $value])
            ->from($tableName)
            ->orderBy($fieldName)
            ->build();
        $this->assertRegExp($regexpField, $sql);
    }
}
