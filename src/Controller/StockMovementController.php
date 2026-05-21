<?php

namespace App\Controller;

use App\Entity\StockMovement;
use App\Repository\StockMovementRepository;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/movements', name: 'api_movement_')]
#[OA\Tag(name: 'Historique des mouvements')]
class StockMovementController extends AbstractController
{
    public function __construct(
        private StockMovementRepository $movementRepository,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(path: '/api/movements', summary: 'Historique des mouvements de stock',
        parameters: [
            new OA\Parameter(name: 'material', in: 'query', description: 'Filtrer par ID matériel',    schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type',     in: 'query', description: 'in | out | adjustment',      schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'dateFrom', in: 'query', description: 'Date de début (YYYY-MM-DD)', schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'dateTo',   in: 'query', description: 'Date de fin (YYYY-MM-DD)',   schema: new OA\Schema(type: 'string', format: 'date')),
            new OA\Parameter(name: 'page',     in: 'query', description: 'Page (défaut: 1)',           schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'limit',    in: 'query', description: 'Résultats par page (max 100)', schema: new OA\Schema(type: 'integer')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste paginée des mouvements',
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'items', type: 'array',
                    items: new OA\Items(ref: new Model(type: StockMovement::class, groups: ['movement:read']))),
                new OA\Property(property: 'total', type: 'integer'),
                new OA\Property(property: 'page',  type: 'integer'),
                new OA\Property(property: 'limit', type: 'integer'),
                new OA\Property(property: 'pages', type: 'integer'),
            ]))])]
    public function index(Request $request): JsonResponse
    {
        $materialId = $request->query->get('material');
        $type       = $request->query->get('type');
        $dateFrom   = $request->query->get('dateFrom');
        $dateTo     = $request->query->get('dateTo');
        $page       = max(1, (int)$request->query->get('page', 1));
        $limit      = min(100, max(10, (int)$request->query->get('limit', 50)));

        $result = $this->movementRepository->findWithFilters(
            $materialId ? (int)$materialId : null,
            $type,
            $dateFrom ? new \DateTime($dateFrom) : null,
            $dateTo   ? new \DateTime($dateTo)   : null,
            $page,
            $limit
        );

        return $this->json([
            'items' => $result['items'],
            'total' => $result['total'],
            'page'  => $page,
            'limit' => $limit,
            'pages' => ceil($result['total'] / $limit),
        ], Response::HTTP_OK, [], ['groups' => ['movement:read']]);
    }

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    #[OA\Get(path: '/api/movements/stats', summary: 'Statistiques globales des mouvements',
        responses: [new OA\Response(response: 200, description: 'Statistiques par type de mouvement')])]
    public function stats(): JsonResponse
    {
        $stats = $this->movementRepository->getGlobalStats();
        return $this->json($stats);
    }
}
