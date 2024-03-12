<?php

namespace App\Controller;

use App\Repository\MetricRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MerchantMetricsController extends AbstractController
{
    /**
     * @Route("/merchant-metrics")
     */
    public function index(Request $request, MetricRepository $repository)
    {
        $adId = $request->query->get('ad_id', '');

        $metrics = $repository->findBy(
            $adId ? ['adId' => $adId] : [],
            ['impressions' => 'ASC']
        );

        return $this->render('merchant/metrics.html.twig', [
            'metrics' => $metrics,
            'adId' => $adId,
        ]);
    }
}
