<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

enum Method: string
{
    case INSERT = 'INSERT';
    case APPEND = 'APPEND';
    case REPLACE = 'REPLACE';
    case TRUNCATE = 'TRUNCATE';
}
