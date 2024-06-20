<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Stringable;

class TableDefinition implements Stringable
{
    public function __construct(
        public string $table,
        public array $columns,
        public ?string $terminatedBy = null,
        public ?string $enclosedBy = null,
        public bool $trailing = false,
        public array $formatOptions = [],
        public ?string $when = null,
        public bool $csv = false,
        public bool $withEmbedded = false,
    ) {}

    public function __toString(): string
    {
        $sql = "INTO TABLE {$this->table}".PHP_EOL;

        if ($this->when) {
            $sql .= "WHEN {$this->when}".PHP_EOL;
        }

        if ($this->csv) {
            $sql .= 'FIELDS CSV '.($this->withEmbedded ? 'WITH' : 'WITHOUT').' EMBEDDED';
        }

        if ($this->terminatedBy) {
            $sql .= ! str_contains($sql, 'FIELDS') ? 'FIELDS ' : ' ';
            $sql .= "TERMINATED BY '{$this->terminatedBy}' ";
        }

        if ($this->enclosedBy) {
            $sql .= "OPTIONALLY ENCLOSED BY '{$this->enclosedBy}'";
        }

        if ($this->csv || $this->terminatedBy || $this->enclosedBy) {
            $sql .= PHP_EOL;
        }

        if ($this->formatOptions) {
            $sql .= implode(PHP_EOL, $this->formatOptions).PHP_EOL;
        }

        if ($this->trailing) {
            $sql .= 'TRAILING NULLCOLS'.PHP_EOL;
        }

        $sql .= '('.PHP_EOL;
        if ($this->columns) {
            $sql .= implode(
                ','.PHP_EOL,
                array_map(fn ($column) => str_repeat(' ', 2).$column, $this->columns)
            ).PHP_EOL;
        }
        $sql .= ')'.PHP_EOL;

        return $sql;
    }
}
