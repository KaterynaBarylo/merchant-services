<?php

namespace App\Tests\Unit\Service;

use App\Repository\LinkRepository;
use App\Service\RandomStringGenerator;
use PHPUnit\Framework\TestCase;

class RandomStringGeneratorTest extends TestCase
{
    public function testItGeneratesStringOfCertainLength()
    {
        $repository = $this->createMock(LinkRepository::class);

        $generator = new RandomStringGenerator($repository);
        $randomString = $generator->generateRandomString(3);
        self::assertSame(3, strlen($randomString));
    }
}
