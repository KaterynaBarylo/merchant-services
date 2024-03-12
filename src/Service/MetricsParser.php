<?php

namespace App\Service;

use App\Entity\Metric;
use App\Repository\MetricRepository;
use Psr\Log\LoggerInterface;

class MetricsParser
{
    public function __construct(private MetricRepository $repository, private LoggerInterface $logger)
    {
    }

    public function parse(array $baseMetrics, array $additionalMetrics): int
    {
        $baseMetrics = $this->transformBaseMetrics($baseMetrics);
        $additionalMetrics = $this->transformAdditionalMetrics($additionalMetrics);
        $countMetricsAdded = 0;

        foreach ($baseMetrics as $id => $baseMetric) {
            if (array_key_exists($id, $additionalMetrics)) {
                $metric = $this->repository->findOneBy(['adId' => $id]);
                if (!$metric instanceof Metric) {
                    $metric = new Metric($id);
                    $this->repository->add($metric);
                }
                $metric->setClicks($baseMetric['clicks'])
                    ->setRoi($baseMetric['roi'])
                    ->setLeads($baseMetric['leads'])
                    ->setUniqueClicks($baseMetric['unique_clicks'])
                    ->setConversion($additionalMetrics[$id]['conversion'])
                    ->setImpressions($additionalMetrics[$id]['impressions']);
                ++$countMetricsAdded;
            } else {
                $this->logger->warning(sprintf('No additional metrics (conversion, impressions) for ad name = %s ', $id));
            }
        }
        $this->repository->flush();

        return $countMetricsAdded;
    }

    public function transformBaseMetrics(array $metrics): array
    {
        $result = [];
        foreach ($metrics as $metric) {
            $name = $metric['name'] ?? null;
            if (null === $name) {
                continue;
            }
            if (!array_key_exists($name, $result)) {
                $result[$name] = [
                    'clicks' => (int) ($metric['clicks'] ?? 0),
                    'unique_clicks' => (int) ($metric['unique_clicks'] ?? 0),
                    'leads' => (int) ($metric['leads'] ?? 0),
                    'roi' => (float) ($metric['roi'] ?? 0),
                ];
            } else {
                $this->logger->warning(sprintf('Duplicate base metric data for merchant name = %s', $name));
            }
        }

        return $result;
    }

    public function transformAdditionalMetrics(array $metrics): array
    {
        $result = [];
        $list = $metrics['data']['list'] ?? [];
        foreach ($list as $metric) {
            $adId = $metric['dimensions']['ad_id'] ?? null;
            if (null === $adId) {
                continue;
            }
            if (!array_key_exists($adId, $result)) {
                $result[$adId] = [
                    'conversion' => (int) ($metric['metrics']['conversion'] ?? 0),
                    'impressions' => (int) ($metric['metrics']['impressions'] ?? 0),
                ];
            } else {
                $this->logger->warning(sprintf('Duplicate merchant ad metric data for ad_id = %s ', $adId));
            }
        }

        return $result;
    }
}
