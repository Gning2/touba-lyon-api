<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/categories', name: 'api_category_')]
#[OA\Tag(name: 'Catégories')]
class CategoryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private CategoryRepository $categoryRepository,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(path: '/api/categories', summary: 'Lister toutes les catégories',
        responses: [new OA\Response(response: 200, description: 'Liste des catégories',
            content: new OA\JsonContent(type: 'array',
                items: new OA\Items(ref: new Model(type: Category::class, groups: ['category:read']))))])]
    public function index(): JsonResponse
    {
        $categories = $this->categoryRepository->findAllWithMaterialCount();

        $data = array_map(function(Category $category) {
            return [
                'id'            => $category->getId(),
                'name'          => $category->getName(),
                'description'   => $category->getDescription(),
                'color'         => $category->getColor(),
                'icon'          => $category->getIcon(),
                'createdAt'     => $category->getCreatedAt()?->format('c'),
                'updatedAt'     => $category->getUpdatedAt()?->format('c'),
                'materialCount' => $category->getMaterials()->count(),
            ];
        }, $categories);

        return $this->json($data);
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    #[OA\Get(path: '/api/categories/{id}', summary: "Détail d'une catégorie",
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 200, description: 'Catégorie trouvée',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:read']))),
            new OA\Response(response: 404, description: 'Introuvable')
        ])]
    public function show(Category $category): JsonResponse
    {
        return $this->json($category, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    #[OA\Post(path: '/api/categories', summary: 'Créer une catégorie',
        requestBody: new OA\RequestBody(required: true,
            content: new OA\JsonContent(required: ['name'], properties: [
                new OA\Property(property: 'name',        type: 'string', example: 'Cuisine'),
                new OA\Property(property: 'description', type: 'string', example: 'Matériel de cuisine'),
                new OA\Property(property: 'color',       type: 'string', example: '#F59E0B'),
                new OA\Property(property: 'icon',        type: 'string', example: 'restaurant'),
            ])),
        responses: [
            new OA\Response(response: 201, description: 'Créée',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:read']))),
            new OA\Response(response: 422, description: 'Données invalides')
        ])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }
        $category = new Category();
        $category->setName($data['name'] ?? '');
        $category->setDescription($data['description'] ?? null);
        $category->setColor($data['color'] ?? null);
        $category->setIcon($data['icon'] ?? null);
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->persist($category);
        $this->em->flush();
        return $this->json($category, Response::HTTP_CREATED, [], ['groups' => ['category:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    #[OA\Put(path: '/api/categories/{id}', summary: 'Modifier une catégorie',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        requestBody: new OA\RequestBody(content: new OA\JsonContent(properties: [
            new OA\Property(property: 'name',        type: 'string'),
            new OA\Property(property: 'description', type: 'string'),
            new OA\Property(property: 'color',       type: 'string'),
            new OA\Property(property: 'icon',        type: 'string'),
        ])),
        responses: [
            new OA\Response(response: 200, description: 'Modifiée',
                content: new OA\JsonContent(ref: new Model(type: Category::class, groups: ['category:read']))),
            new OA\Response(response: 404, description: 'Introuvable')
        ])]
    public function update(Category $category, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['name'])) $category->setName($data['name']);
        if (array_key_exists('description', $data)) $category->setDescription($data['description']);
        if (array_key_exists('color', $data)) $category->setColor($data['color']);
        if (array_key_exists('icon', $data)) $category->setIcon($data['icon']);
        $errors = $this->validator->validate($category);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->flush();
        return $this->json($category, Response::HTTP_OK, [], ['groups' => ['category:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(path: '/api/categories/{id}', summary: 'Supprimer une catégorie',
        description: 'Impossible si la catégorie contient des matériels',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 409, description: 'Catégorie non vide')
        ])]
    public function delete(Category $category): JsonResponse
    {
        $materialCount = $category->getMaterialCount();
        if ($materialCount > 0) {
            return $this->json(
                ['error' => "Impossible de supprimer : cette catégorie contient {$materialCount} matériel(s)"],
                Response::HTTP_CONFLICT
            );
        }
        $this->em->remove($category);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
