<?php

namespace App\Controller;

use App\Entity\MaterialConditionHistory;
use App\Entity\MaterialConditionStock;
use App\Entity\Material;
use App\Repository\MaterialConditionRepository;
use App\Repository\MaterialConditionStockRepository;
use App\Repository\MaterialConditionHistoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/materials/{id}/conditions', name: 'api_material_condition_')]
#[OA\Tag(name: 'États matériels')]
class MaterialConditionStockController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MaterialConditionRepository $conditionRepository,
        private MaterialConditionStockRepository $stockRepository,
        private MaterialConditionHistoryRepository $historyRepository,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(Material $material): JsonResponse
    {
        $stocks = $this->stockRepository->findByMaterial($material->getId());
        return $this->json($stocks, 200, [], [
            'groups' => ['condition_stock:read', 'condition:read']
        ]);
    }

    #[Route('/history', name: 'history', methods: ['GET'])]
    public function history(Material $material): JsonResponse
    {
        $history = $this->historyRepository->findByMaterial($material->getId());
        return $this->json($history, 200, [], [
            'groups' => ['history:read', 'condition:read']
        ]);
    }

    #[Route('', name: 'update', methods: ['POST'])]
    public function updateConditionStock(Material $material, Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true);
        $stocks  = $data['stocks'] ?? [];
        $comment = $data['comment'] ?? null;

        if (empty($stocks)) {
            return $this->json(['error' => 'La liste des états est obligatoire'], Response::HTTP_BAD_REQUEST);
        }

        // ── 1. Validation complète avant tout changement ──────────────────
        $totalQty = 0;
        $lines    = [];

        foreach ($stocks as $stock) {
            $conditionId = (int)($stock['conditionId'] ?? 0);
            $qty         = (int)($stock['quantity']    ?? 0);

            if (!$conditionId) {
                return $this->json(['error' => 'conditionId manquant dans un état'], Response::HTTP_BAD_REQUEST);
            }
            if ($qty < 0) {
                return $this->json(['error' => "La quantité ne peut pas être négative (conditionId: {$conditionId})"], Response::HTTP_BAD_REQUEST);
            }

            $condition = $this->conditionRepository->find($conditionId);
            if (!$condition) {
                return $this->json(['error' => "État introuvable (id: {$conditionId})"], Response::HTTP_NOT_FOUND);
            }

            $lines[$conditionId] = ['condition' => $condition, 'quantity' => $qty];
            $totalQty += $qty;
        }

        if ($totalQty > $material->getTotalStock()) {
            return $this->json([
                'error' => "Total dépassé : {$totalQty} ventilés > {$material->getTotalStock()} en stock"
            ], Response::HTTP_CONFLICT);
        }

        // ── 2. Tout est valide — on applique les changements ──────────────
        $this->em->beginTransaction();

        try {
            $currentStocks = $this->stockRepository->findByMaterial($material->getId());

            $existingByCondition = [];
            foreach ($currentStocks as $s) {
                $existingByCondition[$s->getCondition()->getId()] = $s;
            }

            foreach ($lines as $conditionId => $line) {
                if (isset($existingByCondition[$conditionId])) {
                    $existingByCondition[$conditionId]->setQuantity($line['quantity']);
                } else {
                    $newStock = new MaterialConditionStock();
                    $newStock->setMaterial($material);
                    $newStock->setCondition($line['condition']);
                    $newStock->setQuantity($line['quantity']);
                    $this->em->persist($newStock);
                }

                $history = new MaterialConditionHistory();
                $history->setMaterial($material);
                $history->setCondition($line['condition']);
                $history->setQuantity($line['quantity']);
                $history->setComment($comment);
                $this->em->persist($history);
            }

            // Mettre à 0 les états non mentionnés
            foreach ($existingByCondition as $conditionId => $existingStock) {
                if (!isset($lines[$conditionId])) {
                    $existingStock->setQuantity(0);
                }
            }

            // ── 3. Recalculer le stock disponible ─────────────────────────
            $unavailableQty = 0;
            foreach ($lines as $line) {
                if (!$line['condition']->isAvailable()) {
                    $unavailableQty += $line['quantity'];
                }
            }

            $loanedQty    = $material->getLoanedQuantity();
            $newAvailable = max(0, $material->getTotalStock() - $unavailableQty - $loanedQty);
            $material->setAvailableStock($newAvailable);

            $this->em->flush();
            $this->em->commit();

        } catch (\Exception $e) {
            $this->em->rollback();
            return $this->json(['error' => 'Erreur : ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $updatedStocks = $this->stockRepository->findByMaterial($material->getId());

        return $this->json([
            'message'        => 'Ventilation mise à jour',
            'stocks'         => $updatedStocks,
            'availableStock' => $material->getAvailableStock(),
        ], 200, [], ['groups' => ['condition_stock:read', 'condition:read']]);
    }
}