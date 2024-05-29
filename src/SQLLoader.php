<?php

declare(strict_types=1);

namespace Yajra\SQLLoader;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Process\ProcessResult;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use LogicException;

class SQLLoader
{
    public ?string $file = null;

    public Method $method = Method::APPEND;

    /** @var TableDefinition[] */
    public array $tables = [];

    public ?string $controlFile = null;

    public ?string $badFile = null;

    public ?string $discardFile = null;

    protected ?string $disk = null;

    protected ?string $logPath = null;

    protected ?ProcessResult $result = null;

    protected bool $deleteFiles = false;

    protected string $logs = '';

    public function __construct(
        public array $options = []
    ) {
    }

    public function options(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    public function inFile(string $file): static
    {
        if (! File::exists($file)) {
            throw new InvalidArgumentException("File [{$file}] does not exist.");
        }

        $this->file = $file;

        return $this;
    }

    public function method(Method $method): static
    {
        $this->method = $method;

        return $this;
    }

    public function into(
        string $table,
        array $columns,
        ?string $terminatedBy = ',',
        ?string $enclosedBy = '"',
        ?string $trailing = null
    ): static {
        $this->tables[] = new TableDefinition($table, $columns, $terminatedBy, $enclosedBy, $trailing);

        return $this;
    }

    public function execute(): ProcessResult
    {
        if (! $this->tables) {
            throw new InvalidArgumentException('At least one table definition is required.');
        }

        if (! $this->file) {
            throw new InvalidArgumentException('Input file is required.');
        }

        $this->result = Process::run($this->buildCommand());

        if ($this->logPath && File::exists($this->logPath)) {
            $this->logs = File::get($this->logPath);
        }

        if ($this->deleteFiles) {
            $this->deleteGeneratedFiles();
        }

        return $this->result; // @phpstan-ignore-line
    }

    protected function buildCommand(): string
    {
        $filesystem = $this->getDisk();

        $file = $this->getFile();
        $filesystem->put($file, $this->buildControlFile());
        $tns = $this->buildTNS();
        $binary = $this->getSqlLoaderBinary();
        $filePath = $filesystem->path($file);

        $command = "$binary userid=$tns control={$filePath}";
        if (! $this->logPath) {
            $this->logPath = str_replace('.ctl', '.log', (string) $filePath);
            $command .= " log={$this->logPath}";
        }

        return $command;
    }

    public function getDisk(): Filesystem
    {
        if ($this->disk) {
            return Storage::disk($this->disk);
        }

        return Storage::disk(config('sql-loader.disk', 'local'));
    }

    public function disk(string $disk): static
    {
        $this->disk = $disk;

        return $this;
    }

    protected function getFile(): string
    {
        if (! $this->controlFile) {
            $this->controlFile = Str::uuid().'.ctl';
        }

        return $this->controlFile;
    }

    public function buildControlFile(): string
    {
        return (new ControlFileBuilder($this))->build();
    }

    protected function buildTNS(): string
    {
        return TnsBuilder::make();
    }

    public static function make(array $options = []): SQLLoader
    {
        return new self($options);
    }

    public function getSqlLoaderBinary(): string
    {
        return config('sql-loader.sqlldr', 'sqlldr');
    }

    protected function deleteGeneratedFiles(): void
    {
        if ($this->logPath && File::exists($this->logPath)) {
            File::delete($this->logPath);
        }

        if ($this->badFile && File::exists($this->badFile)) {
            File::delete($this->badFile);
        }

        if ($this->discardFile && File::exists($this->discardFile)) {
            File::delete($this->discardFile);
        }

        $filesystem = $this->getDisk();
        if ($this->controlFile && $filesystem->exists($this->controlFile)) {
            $filesystem->delete($this->controlFile);
        }
    }

    public function as(string $controlFile): static
    {
        if (! Str::endsWith($controlFile, '.ctl')) {
            $controlFile .= '.ctl';
        }

        $this->controlFile = $controlFile;

        return $this;
    }

    public function logsTo(string $path): static
    {
        $this->logPath = $path;

        return $this;
    }

    public function successful(): bool
    {
        if (is_null($this->result)) {
            return false;
        }

        return $this->result->successful();
    }

    public function debug(): string
    {
        $debug = 'Command:'.PHP_EOL.$this->buildCommand().PHP_EOL.PHP_EOL;
        $debug .= 'Control File:'.PHP_EOL.$this->buildControlFile().PHP_EOL;

        if ($this->result) {
            $debug .= 'Output:'.$this->result->output().PHP_EOL.PHP_EOL;
            $debug .= 'Error Output:'.PHP_EOL.$this->result->errorOutput().PHP_EOL;
            $debug .= 'Exit Code: '.$this->result->exitCode().PHP_EOL.PHP_EOL;
        }

        return $debug;
    }

    public function output(): string
    {
        if (is_null($this->result)) {
            return 'No output available';
        }

        return $this->result->output();
    }

    public function errorOutput(): string
    {
        if (is_null($this->result)) {
            return 'No error output available';
        }

        return $this->result->errorOutput();
    }

    public function deleteFilesAfterRun(bool $delete = true): static
    {
        $this->deleteFiles = $delete;

        return $this;
    }

    public function logs(): string
    {
        return $this->logs;
    }

    public function result(): ProcessResult
    {
        if (! $this->result) {
            throw new LogicException('Please run execute method first.');
        }

        return $this->result;
    }
}
