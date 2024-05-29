<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

class TableDefinition
{
    public function __construct(
        public string $table,
        public array $columns,
        public ?string $terminatedBy = null,
        public ?bool $optionally = null,
        public ?string $enclosedBy = null,
        public ?string $trailing = null
    ) {
    }
}
