<?php

namespace ClawRock\CsvReader\Model;

use ClawRock\CsvReader\Api\CsvReaderInterface;

class CsvReaderFactory
{
    /** @var \Magento\Framework\ObjectManagerInterface  */
    protected $_objectManager;

    public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    public function create(string $path, string $enclosure = '"')
    {
        /** @var CsvReaderInterface $csvReader */
        $csvReader = $this->_objectManager->create(CsvReaderInterface::class);
        $csvReader->init($path, $enclosure);
        return $csvReader;
    }
}
