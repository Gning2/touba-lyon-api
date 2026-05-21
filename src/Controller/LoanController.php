<?php

namespace App\Controller;

use App\Entity\Loan;
use App\Entity\LoanItem;
use App\Entity\StockMovement;
use App\Repository\LoanRepository;
use App\Repository\MaterialRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/loans', name: 'api_loan_')]
#[OA\Tag(name: 'Emprunts')]
class LoanController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private LoanRepository $loanRepository,
        private MaterialRepository $materialRepository,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(path: '/api/loans', summary: 'Lister les emprunts',
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', description: 'active | returned | partial | overdue', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'search', in: 'query', description: 'Recherche par nom ou organisation',     schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste des emprunts',
            content: new OA\JsonContent(type: 'array',
                items: new OA\Items(ref: new Model(type: Loan::class, groups: ['loan:read']))))])]
    public function index(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');
        $loans  = $this->loanRepository->findWithFilters($status, $search);
        return $this->json($loans, Response::HTTP_OK, [], ['groups' => ['loan:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(path: '/api/loans/{id}', summary: "Détail d'un emprunt",
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Emprunt trouvé',
                content: new OA\JsonContent(ref: new Model(type: Loan::class, groups: ['loan:read']))),
            new OA\Response(response: 404, description: 'Introuvable')
        ])]
    public function show(Loan $loan): JsonResponse
    {
        return $this->json($loan, Response::HTTP_OK, [], ['groups' => ['loan:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(path: '/api/loans', summary: 'Créer un emprunt',
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(required: ['borrowerName', 'loanDate', 'items'], properties: [
                new OA\Property(property: 'borrowerName',         type: 'string',  example: 'Moussa Diallo'),
                new OA\Property(property: 'borrowerOrganization', type: 'string',  example: 'Association Keur Serigne Touba'),
                new OA\Property(property: 'borrowerPhone',        type: 'string',  example: '0612345678'),
                new OA\Property(property: 'borrowerEmail',        type: 'string'),
                new OA\Property(property: 'loanDate',             type: 'string',  format: 'date', example: '2024-06-01'),
                new OA\Property(property: 'expectedReturnDate',   type: 'string',  format: 'date', example: '2024-06-03'),
                new OA\Property(property: 'purpose',              type: 'string',  example: 'Gamou annuel'),
                new OA\Property(property: 'notes',                type: 'string'),
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                    required: ['materialId', 'quantity'],
                    properties: [
                        new OA\Property(property: 'materialId', type: 'integer', example: 1),
                        new OA\Property(property: 'quantity',   type: 'integer', example: 50),
                        new OA\Property(property: 'notes',      type: 'string'),
                    ]
                )),
            ])),
        responses: [
            new OA\Response(response: 201, description: 'Emprunt créé',
                content: new OA\JsonContent(ref: new Model(type: Loan::class, groups: ['loan:read']))),
            new OA\Response(response: 409, description: 'Stock insuffisant pour un des matériels')
        ])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data || empty($data['items'])) {
            return $this->json(['error' => 'Données invalides ou aucun matériel sélectionné'], Response::HTTP_BAD_REQUEST);
        }
        foreach ($data['items'] as $itemData) {
            $material = $this->materialRepository->find($itemData['materialId'] ?? 0);
            if (!$material) {
                return $this->json(['error' => "Matériel ID {$itemData['materialId']} introuvable"], Response::HTTP_NOT_FOUND);
            }
            $qty = (int)($itemData['quantity'] ?? 0);
            if ($material->getAvailableStock() < $qty) {
                return $this->json([
                    'error' => "Stock insuffisant pour \"{$material->getName()}\" : disponible {$material->getAvailableStock()}, demandé {$qty}"
                ], Response::HTTP_CONFLICT);
            }
        }
        $loan = new Loan();
        $loan->setBorrowerName($data['borrowerName'] ?? '');
        $loan->setBorrowerOrganization($data['borrowerOrganization'] ?? null);
        $loan->setBorrowerPhone($data['borrowerPhone'] ?? null);
        $loan->setBorrowerEmail($data['borrowerEmail'] ?? null);
        $loan->setLoanDate(new \DateTime($data['loanDate'] ?? 'now'));
        $loan->setPurpose($data['purpose'] ?? null);
        $loan->setNotes($data['notes'] ?? null);
        if (!empty($data['expectedReturnDate'])) {
            $loan->setExpectedReturnDate(new \DateTime($data['expectedReturnDate']));
        }
        $errors = $this->validator->validate($loan);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) { $errorMessages[$error->getPropertyPath()] = $error->getMessage(); }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        foreach ($data['items'] as $itemData) {
            $material = $this->materialRepository->find($itemData['materialId']);
            $qty = (int)$itemData['quantity'];
            $item = new LoanItem();
            $item->setMaterial($material);
            $item->setQuantity($qty);
            $item->setNotes($itemData['notes'] ?? null);
            $loan->addItem($item);
            $this->em->persist($item);
            $movement = new StockMovement();
            $movement->setMaterial($material);
            $movement->setLoan($loan);
            $movement->setType(StockMovement::TYPE_OUT);
            $movement->setQuantity($qty);
            $movement->setStockBefore($material->getAvailableStock());
            $movement->setStockAfter($material->getAvailableStock() - $qty);
            $movement->setBorrowerName($loan->getBorrowerName());
            $movement->setBorrowerOrganization($loan->getBorrowerOrganization());
            $movement->setReason("Emprunt - {$loan->getBorrowerOrganization()}");
            $this->em->persist($movement);
            $material->setAvailableStock($material->getAvailableStock() - $qty);
        }
        $this->em->persist($loan);
        $this->em->flush();
        return $this->json($loan, Response::HTTP_CREATED, [], ['groups' => ['loan:read']]);
    }

    #[Route('/{id}/return', name: 'return', methods: ['POST'])]
    #[OA\Post(path: '/api/loans/{id}/return', summary: 'Enregistrer un retour (total ou partiel)',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'items', type: 'array', items: new OA\Items(
                    properties: [
                        new OA\Property(property: 'loanItemId',       type: 'integer'),
                        new OA\Property(property: 'returnedQuantity', type: 'integer'),
                    ]
                )),
            ])),
        responses: [
            new OA\Response(response: 200, description: 'Retour enregistré',
                content: new OA\JsonContent(ref: new Model(type: Loan::class, groups: ['loan:read']))),
            new OA\Response(response: 409, description: 'Emprunt déjà soldé')
        ])]
    public function processReturn(Loan $loan, Request $request): JsonResponse
    {
        if ($loan->getStatus() === Loan::STATUS_RETURNED) {
            return $this->json(['error' => 'Cet emprunt est déjà soldé'], Response::HTTP_CONFLICT);
        }
        $data        = json_decode($request->getContent(), true) ?? [];
        $returnItems = $data['items'] ?? [];
        $totalPending = 0;
        foreach ($loan->getItems() as $loanItem) {
            $returnQty = 0;
            foreach ($returnItems as $ri) {
                if ((int)$ri['loanItemId'] === $loanItem->getId()) {
                    $returnQty = (int)$ri['returnedQuantity'];
                    break;
                }
            }
            if ($returnQty > 0) {
                $material  = $loanItem->getMaterial();
                $returnQty = min($returnQty, $loanItem->getRemainingQuantity());
                $movement  = new StockMovement();
                $movement->setMaterial($material);
                $movement->setLoan($loan);
                $movement->setType(StockMovement::TYPE_IN);
                $movement->setQuantity($returnQty);
                $movement->setStockBefore($material->getAvailableStock());
                $movement->setStockAfter($material->getAvailableStock() + $returnQty);
                $movement->setBorrowerName($loan->getBorrowerName());
                $movement->setBorrowerOrganization($loan->getBorrowerOrganization());
                $movement->setReason("Retour emprunt - {$loan->getBorrowerOrganization()}");
                $this->em->persist($movement);
                $material->setAvailableStock($material->getAvailableStock() + $returnQty);
                $loanItem->setReturnedQuantity($loanItem->getReturnedQuantity() + $returnQty);
            }
            $totalPending += $loanItem->getRemainingQuantity();
        }
        if ($totalPending === 0) {
            $loan->setStatus(Loan::STATUS_RETURNED);
            $loan->setActualReturnDate(new \DateTime());
        } else {
            $loan->setStatus(Loan::STATUS_PARTIAL);
        }
        $this->em->flush();
        return $this->json($loan, Response::HTTP_OK, [], ['groups' => ['loan:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(path: '/api/loans/{id}', summary: 'Supprimer un emprunt',
        description: 'Uniquement possible si le statut est "returned"',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 409, description: 'Emprunt encore actif')
        ])]
    public function delete(Loan $loan): JsonResponse
    {
        if ($loan->getStatus() === Loan::STATUS_ACTIVE) {
            return $this->json(
                ['error' => "Impossible de supprimer un emprunt actif. Soldez-le d'abord."],
                Response::HTTP_CONFLICT
            );
        }
        $this->em->remove($loan);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
