<?php

namespace App\Controller;

use App\Entity\Material;
use App\Entity\StockMovement;
use App\Repository\MaterialRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/materials', name: 'api_material_')]
#[OA\Tag(name: 'Matériels')]
class MaterialController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MaterialRepository $materialRepository,
        private CategoryRepository $categoryRepository,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(path: '/api/materials', summary: 'Lister les matériels',
        parameters: [
            new OA\Parameter(name: 'category', in: 'query', description: 'Filtrer par ID catégorie', schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'search',   in: 'query', description: 'Recherche par nom ou référence', schema: new OA\Schema(type: 'string')),
        ],
        responses: [new OA\Response(response: 200, description: 'Liste des matériels',
            content: new OA\JsonContent(type: 'array',
                items: new OA\Items(ref: new Model(type: Material::class, groups: ['material:read']))))])]
    public function index(Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $search     = $request->query->get('search');
        if ($categoryId) {
            $materials = $this->materialRepository->findByCategory((int)$categoryId, $search);
        } elseif ($search) {
            $materials = $this->materialRepository->search($search);
        } else {
            $materials = $this->materialRepository->findAllWithCategory();
        }
        return $this->json($materials, Response::HTTP_OK, [], ['groups' => ['material:read']]);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(path: '/api/materials/{id}', summary: "Détail d'un matériel",
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Matériel trouvé',
                content: new OA\JsonContent(ref: new Model(type: Material::class, groups: ['material:read']))),
            new OA\Response(response: 404, description: 'Introuvable')
        ])]
    public function show(Material $material): JsonResponse
    {
        return $this->json($material, Response::HTTP_OK, [], ['groups' => ['material:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(path: '/api/materials', summary: 'Créer un matériel',
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(required: ['name', 'categoryId', 'totalStock'], properties: [
                new OA\Property(property: 'name',        type: 'string',  example: 'Grands bols'),
                new OA\Property(property: 'categoryId',  type: 'integer', example: 1),
                new OA\Property(property: 'totalStock',  type: 'integer', example: 100),
                new OA\Property(property: 'description', type: 'string'),
                new OA\Property(property: 'reference',   type: 'string'),
                new OA\Property(property: 'unit',        type: 'string',  example: 'unité'),
                new OA\Property(property: 'notes',       type: 'string'),
            ])),
        responses: [
            new OA\Response(response: 201, description: 'Créé',
                content: new OA\JsonContent(ref: new Model(type: Material::class, groups: ['material:read']))),
            new OA\Response(response: 404, description: 'Catégorie introuvable'),
            new OA\Response(response: 422, description: 'Données invalides')
        ])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }
        $category = $this->categoryRepository->find($data['categoryId'] ?? 0);
        if (!$category) {
            return $this->json(['error' => 'Catégorie introuvable'], Response::HTTP_NOT_FOUND);
        }
        $material = new Material();
        $material->setName($data['name'] ?? '');
        $material->setDescription($data['description'] ?? null);
        $material->setReference($data['reference'] ?? null);
        $material->setTotalStock((int)($data['totalStock'] ?? 0));
        $material->setUnit($data['unit'] ?? null);
        $material->setNotes($data['notes'] ?? null);
        $material->setCategory($category);
        $errors = $this->validator->validate($material);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) { $errorMessages[$error->getPropertyPath()] = $error->getMessage(); }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->persist($material);
        $this->em->flush();
        if ($material->getTotalStock() > 0) {
            $movement = new StockMovement();
            $movement->setMaterial($material);
            $movement->setType(StockMovement::TYPE_IN);
            $movement->setQuantity($material->getTotalStock());
            $movement->setStockBefore(0);
            $movement->setStockAfter($material->getTotalStock());
            $movement->setReason('Stock initial');
            $this->em->persist($movement);
            $this->em->flush();
        }
        return $this->json($material, Response::HTTP_CREATED, [], ['groups' => ['material:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Put(path: '/api/materials/{id}', summary: 'Modifier un matériel',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name',             type: 'string'),
            new OA\Property(property: 'categoryId',       type: 'integer'),
            new OA\Property(property: 'totalStock',       type: 'integer'),
            new OA\Property(property: 'adjustmentReason', type: 'string', description: 'Obligatoire si totalStock est modifié'),
            new OA\Property(property: 'unit',             type: 'string'),
            new OA\Property(property: 'description',      type: 'string'),
            new OA\Property(property: 'notes',            type: 'string'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifié',
                content: new OA\JsonContent(ref: new Model(type: Material::class, groups: ['material:read']))),
            new OA\Response(response: 404, description: 'Introuvable')
        ])]
    public function update(Material $material, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['categoryId'])) {
            $category = $this->categoryRepository->find($data['categoryId']);
            if ($category) $material->setCategory($category);
        }
        if (isset($data['name'])) $material->setName($data['name']);
        if (array_key_exists('description', $data)) $material->setDescription($data['description']);
        if (array_key_exists('reference',   $data)) $material->setReference($data['reference']);
        if (array_key_exists('unit',        $data)) $material->setUnit($data['unit']);
        if (array_key_exists('notes',       $data)) $material->setNotes($data['notes']);
        if (isset($data['totalStock'])) {
            $newTotal    = (int)$data['totalStock'];
            $diff        = $newTotal - $material->getTotalStock();
            $oldAvailable = $material->getAvailableStock();
            $newAvailable = max(0, $oldAvailable + $diff);
            $movement = new StockMovement();
            $movement->setMaterial($material);
            $movement->setType(StockMovement::TYPE_ADJUSTMENT);
            $movement->setQuantity(abs($diff));
            $movement->setStockBefore($oldAvailable);
            $movement->setStockAfter($newAvailable);
            $movement->setReason($data['adjustmentReason'] ?? 'Ajustement de stock');
            $this->em->persist($movement);
            $material->setTotalStock($newTotal);
            $material->setAvailableStock($newAvailable);
        }
        $errors = $this->validator->validate($material);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) { $errorMessages[$error->getPropertyPath()] = $error->getMessage(); }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->flush();
        return $this->json($material, Response::HTTP_OK, [], ['groups' => ['material:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(path: '/api/materials/{id}', summary: 'Supprimer un matériel',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimé'),
            new OA\Response(response: 409, description: 'Matériel actuellement emprunté')
        ])]
    public function delete(Material $material): JsonResponse
    {
        if ($material->getLoanedQuantity() > 0) {
            return $this->json(
                ['error' => 'Impossible de supprimer : du matériel est actuellement emprunté'],
                Response::HTTP_CONFLICT
            );
        }
        $this->em->remove($material);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
