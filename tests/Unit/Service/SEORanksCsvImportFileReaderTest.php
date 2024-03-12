<?php

namespace App\Tests\Unit\Service;

use App\Service\SEORanksCsvImportFileReader;
use PHPUnit\Framework\TestCase;

class SEORanksCsvImportFileReaderTest extends TestCase
{
    /**
     * @param string[] $expectedErrors
     *
     * @dataProvider loadFiles()
     */
    public function testItValidatesCsvFile(string $filePath, array $expectedErrors): void
    {
        $reader = new SEORanksCsvImportFileReader();
        $errors = $reader->validateCsv($filePath);
        $this->assertSame($expectedErrors, $errors);
    }

    /**
     * @return array{array{string, string[]}}
     */
    public function loadFiles(): array
    {
        return [
            [
                __DIR__ . '/resources/seo_ranks_wrong_columns.csv',
                ['Column(s) `Merchant Entity ID` not found in CSV file. Expected columns: `Merchant Entity ID`, `New SEO Rank`. Found columns: `Shop ID`, `New SEO Rank`'],
            ],
            [
                '/tmp/file-not-found.csv',
                ['Unable to read CSV file. Error: `/tmp/file-not-found.csv`: failed to open stream: No such file or directory.'],
            ],
            [
                __DIR__ . '/resources/seo_ranks_invalid_entity_id.csv',
                ['"Merchant Entity ID" column contains invalid values. Value "3333_3333" is not a valid UUID'],
            ],
            [
                __DIR__ . '/resources/seo_ranks_invalid_rank.csv',
                ['"New SEO Rank" column contains invalid values. Value "_abc" is not a valid number'],
            ],
            [
                __DIR__ . '/resources/seo_ranks_valid.csv',
                [],
            ],
        ];
    }

    public function testItReadsCsvFile(): void
    {
        $reader = new SEORanksCsvImportFileReader();
        $records = $reader->getRecords(__DIR__ . '/resources/seo_ranks_valid.csv');
        foreach ($records as $record) {
            $this->assertIsArray($record);

            $this->assertArrayHasKey(SEORanksCsvImportFileReader::COLUMN_ENTITY_ID, $record);
            $this->assertNotEmpty($record[SEORanksCsvImportFileReader::COLUMN_ENTITY_ID]);

            $this->assertArrayHasKey(SEORanksCsvImportFileReader::COLUMN_SEO_RANK, $record);
        }
    }
}
