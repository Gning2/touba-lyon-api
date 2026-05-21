<?php

namespace App\Controller;

use App\Entity\MaterialCondition;
use App\Repository\MaterialConditionRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/conditions', name: 'api_condition_')]
#[OA\Tag(name: 'États matériels')]
class MaterialConditionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private MaterialConditionRepository $conditionRepository,
        private ValidatorInterface $validator,
    ) {}

    #[Route('', name: 'list', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $conditions = $this->conditionRepository->findAllOrdered();
        return $this->json($conditions, 200, [], ['groups' => ['condition:read']]);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (!$data) {
            return $this->json(['error' => 'Données invalides'], Response::HTTP_BAD_REQUEST);
        }
        $condition = new MaterialCondition();
        $condition->setLabel($data['label'] ?? '');
        $condition->setColor($data['color'] ?? '#10B981');
        $condition->setIsAvailable($data['isAvailable'] ?? true);
        $condition->setDescription($data['description'] ?? null);
        $condition->setPosition($data['position'] ?? 0);

        $errors = $this->validator->validate($condition);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) { $errorMessages[$error->getPropertyPath()] = $error->getMessage(); }
            return $this->json(['errors' => $errorMessages], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->persist($condition);
        $this->em->flush();
        return $this->json($condition, Response::HTTP_CREATED, [], ['groups' => ['condition:read']]);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT', 'PATCH'])]
    public function update(MaterialCondition $condition, Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        if (isset($data['label']))       $condition->setLabel($data['label']);
        if (isset($data['color']))       $condition->setColor($data['color']);
        if (isset($data['isAvailable'])) $condition->setIsAvailable($data['isAvailable']);
        if (isset($data['description'])) $condition->setDescription($data['description']);
        if (isset($data['position']))    $condition->setPosition($data['position']);
        $this->em->flush();
        return $this->json($condition, 200, [], ['groups' => ['condition:read']]);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(MaterialCondition $condition): JsonResponse
    {
        $stockCount = $this->em->createQuery(
            'SELECT COUNT(s.id) FROM App\Entity\MaterialConditionStock s WHERE s.condition = :c AND s.quantity > 0'
        )->setParameter('c', $condition)->getSingleScalarResult();

        if ($stockCount > 0) {
            return $this->json(
                ['error' => 'Impossible de supprimer : des matériels utilisent cet état'],
                Response::HTTP_CONFLICT
            );
        }
        $this->em->remove($condition);
        $this->em->flush();
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}