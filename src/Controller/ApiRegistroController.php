<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Repository\RegistroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

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
                'id' => $p->getId(), // Se envía para lógica interna, no se muestra en la interfaz
                'nombre' => $p->getNombre(),
                'email' => $p->getEmail() ?? 'N/A',
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

        // Validación de nombre: solo letras y espacios (sin números)
        $errors = $validator->validate($data['nombre'] ?? '', [
            new Assert\NotBlank(['message' => 'El nombre es obligatorio.']),
            new Assert\Regex([
                'pattern' => '/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',
                'message' => 'El nombre no debe llevar números ni símbolos.',
            ]),
        ]);

        if (count($errors) > 0) {
            return $this->json(['error' => $errors[0]->getMessage()], 400);
        }

        $persona = new Registro();
        $persona->setNombre($data['nombre']);
        $persona->setEmail($data['email']);
        // Agrega setTelefono si tu entidad lo tiene configurado

        $em->persist($persona);
        $em->flush();

        return $this->json(['status' => 'Persona guardada con éxito'], 201);
    }

    #[Route('/personas/{id}', name: 'api_personas_delete', methods: ['DELETE'])]
    public function delete(int $id, RegistroRepository $repo, EntityManagerInterface $em): JsonResponse
    {
        $persona = $repo->find($id);

        if (!$persona) {
            return $this->json(['error' => 'No se encontró el registro'], 404);
        }

        $em->remove($persona);
        $em->flush();

        return $this->json(['status' => 'Registro eliminado correctamente']);
    }
}
