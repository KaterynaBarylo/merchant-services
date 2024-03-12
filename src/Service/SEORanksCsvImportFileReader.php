<?php

namespace App\Service;

use Iterator;
use League\Csv\Exception;
use League\Csv\InvalidArgument;
use League\Csv\Reader;
use League\Csv\UnavailableStream;
use Webmozart\Assert\Assert;
use Webmozart\Assert\InvalidArgumentException;

class SEORanksCsvImportFileReader
{
    public const COLUMN_ENTITY_ID = 'Merchant Entity ID';
    public const COLUMN_SEO_RANK = 'New SEO Rank';
    public const COLUMNS = [self::COLUMN_ENTITY_ID, self::COLUMN_SEO_RANK];
    public const DELIMITER = ';';

    /**
     * @return string[]
     */
    public function validateCsv(string $pathName): array
    {
        try {
            $reader = $this->createReader($pathName);
            $header = $reader->getHeader();
            $records = $reader->getRecords();
        } catch (UnavailableStream|Exception $e) {
            return [
                sprintf('Unable to read CSV file. Error: %s', $e->getMessage()),
            ];
        }

        $notFoundColumns = [];
        foreach (self::COLUMNS as $column) {
            if (false === \in_array($column, $header, true)) {
                $notFoundColumns[] = $column;
            }
        }

        if ([] !== $notFoundColumns) {
            return [
                sprintf(
                    'Column(s) `%s` not found in CSV file. Expected columns: `%s`. Found columns: `%s`',
                    implode('`, `', $notFoundColumns),
                    implode('`, `', self::COLUMNS),
                    implode('`, `', $header)
                ),
            ];
        }

        try {
            /** @var array<SEORanksCsvImportFileReader::COLUMN_*, string> $record */
            foreach ($records as $record) {
                Assert::uuid(
                    $record[self::COLUMN_ENTITY_ID],
                    sprintf('"%s" column contains invalid values. Value "%s" is not a valid UUID', self::COLUMN_ENTITY_ID, str_replace('%', '_', $record[self::COLUMN_ENTITY_ID]))
                );
                Assert::numeric(
                    $record[self::COLUMN_SEO_RANK],
                    sprintf('"%s" column contains invalid values. Value "%s" is not a valid number', self::COLUMN_SEO_RANK, str_replace('%', '_', $record[self::COLUMN_SEO_RANK]))
                );
            }
        } catch (InvalidArgumentException $e) {
            return [$e->getMessage()];
        }

        return [];
    }

    /**
     * @throws UnavailableStream
     * @throws InvalidArgument
     * @throws Exception
     */
    public function getRecords(string $pathName): Iterator
    {
        $reader = $this->createReader($pathName);

        return $reader->getRecords();
    }

    /**
     * @throws InvalidArgument
     * @throws UnavailableStream
     * @throws Exception
     */
    private function createReader(string $pathName): Reader
    {
        return Reader::createFromPath($pathName)
            ->setDelimiter(self::DELIMITER)
            ->setHeaderOffset(0);
    }
}
