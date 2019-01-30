<?php

namespace ClawRock\CsvReader\Model;

use ClawRock\CsvReader\Api\CsvReaderInterface;
use ClawRock\CsvReader\Model\Exception\FieldNotExistException;
use ClawRock\CsvReader\Model\Exception\NotFoundException;
use ClawRock\CsvReader\Model\Exception\NotInitializedException;

class CsvReader implements CsvReaderInterface
{
    /** @var \Magento\Framework\File\Csv  */
    protected $csv;

    protected $data;

    protected $fieldIndexMap = [];

    protected $optimizedMaps = [];

    public function __construct(
        \Magento\Framework\File\Csv $csv
    ) {
        $this->csv = $csv;
    }

    public function init(string $path, string $enclosure = '"')
    {
        $this->csv->setEnclosure($enclosure);

        $data = $this->csv->getData($path);
        $this->createFieldIndexMap($data);
        $data = $this->removeDataHeader($data);
        $this->data = $data;

        unset($this->csv);
    }

    public function optimizeSearchBy(string $field): void
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        $this->optimizedMaps[$field] = [];

        foreach($this->get() as $data) {
            $fieldValue = $this->getValue($data, $field);

            if(!isset($this->optimizedMaps[$field][$fieldValue])) {
                $this->optimizedMaps[$field][$fieldValue] = [];
            }

            $this->optimizedMaps[$field][$fieldValue][] = $data;
        }
    }

    public function getValue(array $row, string $field): string
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        $index = $this->getFieldIndex($field);
        return $row[$index];
    }

    public function get(): array
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        return $this->data;
    }

    public function getRowByField(string $field, string $value): array
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        if(isset($this->optimizedMaps[$field])) {
            if(isset($this->optimizedMaps[$field][$value])) {
                return $this->optimizedMaps[$field][$value][0];
            }

            throw new NotFoundException();
        }

        foreach($this->data as $data) {
            if($this->getValue($data, $field) == $value) {
                return $data;
            }
        }

        throw new NotFoundException();
    }

    public function getRowsByField(string $field, string $value): array
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        if(isset($this->optimizedMaps[$field])) {
            if(isset($this->optimizedMaps[$field][$value])) {
                return $this->optimizedMaps[$field][$value] ?: [];
            }
        }

        $rows = [];

        foreach($this->data as $data) {
            if($this->getValue($data, $field) == $value) {
                $rows[] = $data;
            }
        }

        return $rows;
    }

    public function getLastRowByField(string $field, string $value): array
    {
        if(!$this->wasInitialized()) {
            throw new NotInitializedException();
        }

        if(isset($this->optimizedMaps[$field])) {
            if(isset($this->optimizedMaps[$field][$value])) {
                $lastIndex = count($this->optimizedMaps[$field][$value]) - 1;
                return $this->optimizedMaps[$field][$value][$lastIndex];
            }

            throw new NotFoundException();
        }

        for($i = count($this->data) - 1; $i >= 0; $i--) {
            if($this->getValue($this->data[$i], $field) == $value) {
                return $this->data[$i];
            }
        }

        throw new NotFoundException();
    }

    protected function createFieldIndexMap(array $data): bool
    {
        $header = $data[0];

        foreach($header as $key => $field) {
            $this->fieldIndexMap[$field] = $key;
        }

        return true;
    }

    protected function removeDataHeader(array $data): array
    {
        array_shift($data);
        return $data;
    }

    protected function getFieldIndex(string $field): int
    {
        if(!isset($this->fieldIndexMap[$field])) {
            throw new FieldNotExistException($field);
        }

        return $this->fieldIndexMap[$field];
    }

    protected function wasInitialized(): bool
    {
        return $this->data !== null;
    }
}
