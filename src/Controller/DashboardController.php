<?php

namespace App\Controller;

use App\Repository\CategoryRepository;
use App\Repository\LoanRepository;
use App\Repository\MaterialRepository;
use App\Repository\StockMovementRepository;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dashboard', name: 'api_dashboard_')]
#[OA\Tag(name: 'Dashboard')]
class DashboardController extends AbstractController
{
    public function __construct(
        private CategoryRepository $categoryRepository,
        private MaterialRepository $materialRepository,
        private LoanRepository $loanRepository,
        private StockMovementRepository $movementRepository,
    ) {}

    #[Route('', name: 'index', methods: ['GET'])]
    #[OA\Get(path: '/api/dashboard', summary: 'Données du tableau de bord',
        parameters: [
            new OA\Parameter(name: 'lowStockThreshold', in: 'query',
                description: 'Seuil de stock faible (défaut: 5)',
                schema: new OA\Schema(type: 'integer', default: 5))
        ],
        responses: [new OA\Response(response: 200, description: 'Données du dashboard')])]
    public function index(Request $request): JsonResponse
    {
        $lowStockThreshold = max(1, (int)$request->query->get('lowStockThreshold', 5));

        return $this->json([
            'stats' => [
                'totalCategories' => $this->categoryRepository->count([]),
                'totalMaterials'  => $this->materialRepository->count([]),
                'activeLoans'     => $this->loanRepository->countByStatus('active'),
                'overdueLoans'    => $this->loanRepository->countOverdue(),
            ],
            'recentMovements'   => $this->movementRepository->findRecent(10),
            'lowStockMaterials' => $this->materialRepository->findLowStock($lowStockThreshold),
            'topBorrowers'      => $this->loanRepository->getTopBorrowers(5),
        ], 200, [], ['groups' => ['movement:read', 'material:read']]);
    }
}