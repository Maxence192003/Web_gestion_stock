<?php

namespace App\Controller;

use App\Repository\PerteRepository;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/analyses', name: 'api_analysis_')]
final class ApiAnalysisController extends AbstractController
{
    #[Route('/mobile', name: 'mobile', methods: ['GET'])]
    public function mobile(
        Request $request,
        VenteRepository $venteRepository,
        PerteRepository $perteRepository,
    ): JsonResponse {
        $availableMonths = array_values(array_unique(array_merge(
            $venteRepository->findAvailableMonths(),
            $perteRepository->findAvailableMonths(),
        )));
        rsort($availableMonths);

        $requestedMonth = (string) $request->query->get('mois', '');
        $defaultMonth = $availableMonths[0] ?? (new \DateTimeImmutable())->format('Y-m');
        $selectedMonth = preg_match('/^\d{4}-\d{2}$/', $requestedMonth) ? $requestedMonth : $defaultMonth;

        $periodStart = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $selectedMonth.'-01 00:00:00');
        if (!$periodStart instanceof \DateTimeImmutable) {
            $periodStart = new \DateTimeImmutable('first day of this month 00:00:00');
            $selectedMonth = $periodStart->format('Y-m');
        }

        $periodEnd = $periodStart->modify('+1 month');

        return $this->json([
            'periodLabel' => $periodStart->format('m/Y'),
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'topVentes' => $venteRepository->findTopSellingProductsForPeriod($periodStart, $periodEnd, 2),
            'topPertes' => $perteRepository->findTopLossProductsForPeriod($periodStart, $periodEnd, 2),
        ]);
    }
}