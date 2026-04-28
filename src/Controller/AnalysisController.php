<?php

namespace App\Controller;

use App\Repository\PerteRepository;
use App\Repository\VenteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/analyses')]
final class AnalysisController extends AbstractController
{
    #[Route('', name: 'app_analysis_index', methods: ['GET'])]
    public function index(Request $request, VenteRepository $venteRepository, PerteRepository $perteRepository): Response
    {
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

        return $this->render('analysis/index.html.twig', [
            'periodLabel' => $periodStart->format('m/Y'),
            'selectedMonth' => $selectedMonth,
            'availableMonths' => $availableMonths,
            'topVentes' => $venteRepository->findTopSellingProductsForPeriod($periodStart, $periodEnd),
            'topPertes' => $perteRepository->findTopLossProductsForPeriod($periodStart, $periodEnd),
            'monthlyRevenue' => $venteRepository->getMonthlyRevenue($periodStart, $periodEnd),
        ]);
    }
}