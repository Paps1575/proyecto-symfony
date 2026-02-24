<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Repository\RegistroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api')]
class ApiRegistroController extends AbstractController
{
    #[Route('/personas', name: 'api_personas_list', methods: ['GET'])]
    public function list(RegistroRepository $repo, Request $request): JsonResponse
    {
        $limit = 5;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;

        $personas = $repo->findBy([], ['id' => 'DESC'], $limit, $offset);
        $total = $repo->count([]);

        $data = [];
        foreach ($personas as $p) {
            $data[] = [
                'id' => $p->getId(),
                'nombre' => $p->getNombre(),
                // CORRECCIÓN: Si te da error, checa si en tu Entidad es getCorreo() o getEmail()
                'email' => method_exists($p, 'getEmail') ? $p->getEmail() : ($p->getCorreo() ?? 'N/A'),
            ];
        }

        return $this->json([
            'items' => $data,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
        ]);
    }

    #[Route('/personas/nuevo', name: 'api_personas_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // CORRECCIÓN: NotBlank sin array de opciones para evitar el error del log
        $errors = $validator->validate($data['nombre'] ?? '', [
            new Assert\NotBlank(),
            new Assert\Regex([
                'pattern' => '/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',
                'message' => 'El nombre no debe llevar números.'
            ])
        ]);

        if (count($errors) > 0) {
            return $this->json(['error' => $errors[0]->getMessage()], 400);
        }

        $persona = new Registro();
        $persona->setNombre($data['nombre']);

        // CORRECCIÓN DINÁMICA: Usa el método que exista en tu entidad
        if (method_exists($persona, 'setEmail')) {
            $persona->setEmail($data['email']);
        } else {
            $persona->setCorreo($data['email']);
        }

        $em->persist($persona);
        $em->flush();

        return $this->json(['status' => 'Persona guardada con éxito'], 201);
    }

    #[Route('/personas/{id}', name: 'api_personas_delete', methods: ['DELETE'])]
    public function delete(int $id, RegistroRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $persona = $repo->find($id);
        if (!$persona) return $this->json(['error' => 'No encontrado'], 404);

        $em->remove($persona);
        $em->flush();

        return $this->json(['status' => 'Eliminado']);
    }
}
