<?php

namespace App\Tests\Unit\Service;

use App\Entity\Metric;
use App\Repository\MetricRepository;
use App\Service\MetricsParser;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MetricsParserTest extends TestCase
{
    private MetricsParser $parser;
    private MetricRepository $repository;

    protected function setUp(): void
    {
        $logger = $this->createMock(LoggerInterface::class);
        $this->repository = $this->createMock(MetricRepository::class);
        $this->parser = new MetricsParser($this->repository, $logger);
    }

    public function testItCreatesNewMetric()
    {
        $baseMetrics = [
                [
                    'name' => '1779041200784401',
                    'clicks' => '1370',
                    'leads' => '33',
                    'unique_clicks' => '1193',
                    'roi' => '4.473845274649',
                ],
            ];
        $additionalMetrics = [
                'data' => [
                    'list' => [
                        [
                            'metrics' => [
                                'conversion' => '3',
                                'impressions' => '6228',
                            ],
                            'dimensions' => [
                                'ad_id' => '1779041200784401',
                            ],
                        ],
                    ],
                ],
            ];

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $this->repository
            ->expects($this->once())
            ->method('add');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $countMetricsAdded = $this->parser->parse($baseMetrics, $additionalMetrics);
        self::assertSame(1, $countMetricsAdded);
    }

    public function testItUpdatesMetric()
    {
        $baseMetrics = [
            [
                'name' => '1779041200784401',
                'clicks' => '1370',
                'leads' => '33',
                'unique_clicks' => '1193',
                'roi' => '4.473845274649',
            ],
        ];
        $additionalMetrics = [
            'data' => [
                'list' => [
                    [
                        'metrics' => [
                            'conversion' => '3',
                            'impressions' => '6228',
                        ],
                        'dimensions' => [
                            'ad_id' => '1779041200784401',
                        ],
                    ],
                ],
            ],
        ];
        $metric = new Metric('1779041200784401');

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->willReturn($metric);

        $this->repository
            ->expects($this->never())
            ->method('add');

        $this->repository
            ->expects($this->once())
            ->method('flush');

        $countMetricsAdded = $this->parser->parse($baseMetrics, $additionalMetrics);
        self::assertSame(1, $countMetricsAdded);
        self::assertSame(1370, $metric->getClicks());
        self::assertSame(33, $metric->getLeads());
        self::assertSame(1193, $metric->getUniqueClicks());
        self::assertSame(4.473845274649, $metric->getRoi());
        self::assertSame(3, $metric->getConversion());
        self::assertSame(6228, $metric->getImpressions());
    }

    /**
     * @dataProvider baseMetricsProvider
     */
    public function testTransformBaseMetricsReturnsCorrectData(array $metrics, $expectedMetrics)
    {
        self::assertSame($expectedMetrics, $this->parser->transformBaseMetrics($metrics));
    }

    public function baseMetricsProvider(): \Generator
    {
        yield 'Valid data' => [
            [
                [
                    'name' => '1779041200784401',
                    'clicks' => '1370',
                    'leads' => '33',
                    'unique_clicks' => '1193',
                    'roi' => '4.473845274649',
                ],
            ],
            [
                '1779041200784401' => [
                    'clicks' => 1370,
                    'unique_clicks' => 1193,
                    'leads' => 33,
                    'roi' => 4.473845274649,
                ],
            ],
        ];
        yield 'Empty data' => [
            [
                [
                    'name' => '1779041200784401',
                    'clicks' => '',
                    'leads' => '',
                    'unique_clicks' => '',
                    'roi' => '',
                ],
            ],
            [
                '1779041200784401' => [
                    'clicks' => 0,
                    'unique_clicks' => 0,
                    'leads' => 0,
                    'roi' => 0.0,
                ],
            ],
        ];
        yield 'Incomplete data' => [
            [
                [
                    'name' => '1779041200784401',
                ],
            ],
            [
                '1779041200784401' => [
                    'clicks' => 0,
                    'unique_clicks' => 0,
                    'leads' => 0,
                    'roi' => 0.0,
                ],
            ],
        ];
        yield 'No data' => [
            [
                [],
            ],
            [],
        ];
    }

    /**
     * @dataProvider additionalMetricsProvider
     */
    public function testTransformAdditionalMetricsReturnsCorrectData(array $metrics, $expectedMetrics)
    {
        self::assertSame($expectedMetrics, $this->parser->transformAdditionalMetrics($metrics));
    }

    public function additionalMetricsProvider(): \Generator
    {
        yield 'Valid data' => [
            [
                'data' => [
                    'list' => [
                        [
                            'metrics' => [
                                'conversion' => '3',
                                'impressions' => '6228',
                            ],
                            'dimensions' => [
                                'ad_id' => '1780174503863329',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '1780174503863329' => [
                    'conversion' => 3,
                    'impressions' => 6228,
                ],
            ],
        ];
        yield 'Empty data' => [
            [
                'data' => [
                    'list' => [
                        [
                            'metrics' => [
                                'conversion' => '',
                                'impressions' => '',
                            ],
                            'dimensions' => [
                                'ad_id' => '1780174503863329',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '1780174503863329' => [
                    'conversion' => 0,
                    'impressions' => 0,
                ],
            ],
        ];
        yield 'Incomplete data' => [
            [
                'data' => [
                    'list' => [
                        [
                            'metrics' => [
                            ],
                            'dimensions' => [
                                'ad_id' => '1780174503863329',
                            ],
                        ],
                    ],
                ],
            ],
            [
                '1780174503863329' => [
                    'conversion' => 0,
                    'impressions' => 0,
                ],
            ],
        ];

        yield 'Empty list' => [
            [
                'data' => [
                    'list' => [
                        [
                        ],
                    ],
                ],
            ],
            [
            ],
        ];
        yield 'No data' => [
            [],
            [],
        ];
    }
}
