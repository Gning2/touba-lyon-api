<?php

namespace App\Controller;

use App\Entity\Material;
use App\Entity\MaterialPhoto;
use App\Repository\MaterialPhotoRepository;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/api/materials/{id}/photos', name: 'api_material_photo_')]
#[OA\Tag(name: 'Photos matériels')]
class MaterialPhotoController extends AbstractController
{
    private const MAX_PHOTOS    = 5;
    private const MAX_SIZE      = 3 * 1024 * 1024; // 3 Mo
    private const ALLOWED_TYPES = ['image/jpeg', 'image/jpg', 'image/png'];
    private const UPLOAD_DIR    = 'uploads/materials';

    public function __construct(
        private EntityManagerInterface $em,
        private MaterialPhotoRepository $photoRepository,
        private SluggerInterface $slugger,
    ) {}

    #[Route('', name: 'upload', methods: ['POST'])]
    #[OA\Post(path: '/api/materials/{id}/photos', summary: 'Uploader une photo',
        parameters: [new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))],
        responses: [
            new OA\Response(response: 201, description: 'Photo uploadée'),
            new OA\Response(response: 400, description: 'Fichier invalide'),
            new OA\Response(response: 409, description: 'Limite de 5 photos atteinte'),
        ])]
    public function upload(Material $material, Request $request): JsonResponse
    {
        // Vérifier limite
        if ($material->getPhotoCount() >= self::MAX_PHOTOS) {
            return $this->json(
                ['error' => 'Limite de ' . self::MAX_PHOTOS . ' photos atteinte pour ce matériel'],
                Response::HTTP_CONFLICT
            );
        }

        $file = $request->files->get('photo');

        if (!$file) {
            return $this->json(['error' => 'Aucun fichier reçu'], Response::HTTP_BAD_REQUEST);
        }

        // Récupérer les infos AVANT le move
        $originalName = $file->getClientOriginalName();
        $fileSize     = $file->getSize();
        $mimeType     = $file->getMimeType();
        $extension    = $file->guessExtension();

        // Vérifier le type MIME
        if (!in_array($mimeType, self::ALLOWED_TYPES)) {
            return $this->json(
                ['error' => 'Format non autorisé. Utilisez JPG ou PNG'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Vérifier la taille
        if ($fileSize > self::MAX_SIZE) {
            return $this->json(
                ['error' => 'Fichier trop volumineux. Maximum 3 Mo'],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Générer un nom unique
        $originalFilename = pathinfo($originalName, PATHINFO_FILENAME);
        $safeFilename     = $this->slugger->slug($originalFilename);
        $newFilename      = $safeFilename . '-' . uniqid() . '.' . $extension;

        // Créer le dossier si nécessaire
        $uploadPath = $this->getParameter('kernel.project_dir') . '/public/' . self::UPLOAD_DIR;
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        // Déplacer le fichier
        try {
            $file->move($uploadPath, $newFilename);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de l\'upload'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        // Créer l'entité APRÈS le move avec les infos récupérées avant
        $photo = new MaterialPhoto();
        $photo->setFilename($newFilename);
        $photo->setOriginalName($originalName);
        $photo->setFileSize($fileSize);
        $photo->setMaterial($material);

        $this->em->persist($photo);
        $this->em->flush();

        return $this->json([
            'id'           => $photo->getId(),
            'filename'     => $photo->getFilename(),
            'originalName' => $photo->getOriginalName(),
            'fileSize'     => $photo->getFileSize(),
            'url'          => $photo->getUrl(),
            'createdAt'    => $photo->getCreatedAt()?->format('c'),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{photoId}', name: 'delete', methods: ['DELETE'])]
    #[OA\Delete(path: '/api/materials/{id}/photos/{photoId}', summary: 'Supprimer une photo',
        parameters: [
            new OA\Parameter(name: 'id',      in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'photoId', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Supprimée'),
            new OA\Response(response: 404, description: 'Photo introuvable'),
        ])]
    public function delete(Material $material, int $photoId): JsonResponse
    {
        $photo = $this->photoRepository->find($photoId);

        if (!$photo || $photo->getMaterial()->getId() !== $material->getId()) {
            return $this->json(['error' => 'Photo introuvable'], Response::HTTP_NOT_FOUND);
        }

        // Supprimer le fichier physique
        $filePath = $this->getParameter('kernel.project_dir') . '/public/uploads/materials/' . $photo->getFilename();
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $this->em->remove($photo);
        $this->em->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
