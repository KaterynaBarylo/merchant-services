<?php

namespace App\Command;

use App\Service\MetricsParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ImportMerchantMetricsCommand extends Command
{
    protected static $defaultName = 'app:import-merchant-metrics';
    protected static $defaultDescription = 'Getting JSON data about merchants metrics from URL and preparing it for saving into DB';

    public function __construct(private HttpClientInterface $client, private MetricsParser $metricsParser)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->info('Start parsing data form API endpoints');

        $baseMetrics = $this->getJson('https://****/endpoint1.json', $io);
        $additionalMetrics = $this->getJson('https://****/endpoint2.json', $io);

        if (null === $baseMetrics || null === $additionalMetrics) {
            return Command::FAILURE;
        }

        $countMetricsAdded = $this->metricsParser->parse($baseMetrics, $additionalMetrics);
        $io->success(sprintf('%d metrics were successfully processed', $countMetricsAdded));

        return Command::SUCCESS;
    }

    private function getJson(string $url, SymfonyStyle $io): ?array
    {
        $response = $this->client->request(
            'GET',
            $url
        );
        try {
            return $response->toArray();
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            $io->error(sprintf('%s: Fail getting data from %s', $e::class, $url));

            return null;
        }
    }
}
