<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

class TableDefinition implements \Stringable
{
    public function __construct(
        public string $table,
        public array $columns,
        public ?string $terminatedBy = null,
        public ?string $enclosedBy = null,
        public ?string $trailing = null,
        public array $formatOptions = []
    ) {
    }

    public function __toString(): string
    {
        $sql = "INTO TABLE {$this->table}".PHP_EOL;

        if ($this->terminatedBy) {
            $sql .= "FIELDS TERMINATED BY '{$this->terminatedBy}' ";
        }

        if ($this->enclosedBy) {
            $sql .= "OPTIONALLY ENCLOSED BY '{$this->enclosedBy}'".PHP_EOL;
        }

        if ($this->trailing) {
            $sql .= $this->trailing.PHP_EOL;
        }

        if ($this->formatOptions) {
            $sql .= implode(PHP_EOL, $this->formatOptions).PHP_EOL;
        }

        $sql .= '('.PHP_EOL;
        if ($this->columns) {
            $sql .= $this->buildColumns($this->columns).PHP_EOL;
        }
        $sql .= ')'.PHP_EOL;

        return $sql;
    }

    protected function buildColumns(array $columns): string
    {
        return implode(','.PHP_EOL, array_map(fn ($column) => str_repeat(' ', 2).$column, $columns));
    }
}
