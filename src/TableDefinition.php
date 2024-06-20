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

        $sql .= $this->delimiterSpecification();

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

    private function delimiterSpecification(): string
    {
        $specs = ['FIELDS'];

        if ($this->csv) {
            $specs[] = 'CSV';
            $specs[] = $this->withEmbedded ? 'WITH EMBEDDED' : 'WITHOUT EMBEDDED';
        }

        if ($this->terminatedBy) {
            $specs[] = "TERMINATED BY '{$this->terminatedBy}'";
        }

        if ($this->enclosedBy) {
            $specs[] = "OPTIONALLY ENCLOSED BY '{$this->enclosedBy}'";
        }

        if (count($specs) > 1) {
            return implode(' ', $specs).PHP_EOL;
        }

        return '';
    }
}
