<?php

namespace ClawRock\CsvReader\Api;

interface CsvReaderInterface
{
    public function init(string $path, string $enclosure = '"');

    public function optimizeSearchBy(string $field): void;

    public function getValue(array $row, string $field): string;

    public function get(): array;

    public function getRowByField(string $field, string $value): array;

    public function getRowsByField(string $field, string $value): array;

    public function getLastRowByField(string $field, string $value): array;
}
