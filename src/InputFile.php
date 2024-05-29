<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

class InputFile implements \Stringable
{
    public function __construct(
        public string $path,
        public ?string $badFile = null,
        public ?string $discardFile = null,
        public ?string $discardMax = null,
    ) {
    }

    public function __toString(): string
    {
        $sql = "INFILE '{$this->path}'";

        if ($this->badFile) {
            $sql .= " BADFILE '{$this->badFile}'";
        }

        if ($this->discardFile) {
            $sql .= " DISCARDFILE '{$this->discardFile}'";
        }

        if ($this->discardMax) {
            $sql .= " DISCARDMAX {$this->discardMax}";
        }

        return $sql;
    }
}
