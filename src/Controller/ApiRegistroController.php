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

        $data = array_map(fn ($p) => [
            'id' => $p->getId(),
            'nombre' => $p->getNombre() ?? 'Sin Nombre',
            'email' => method_exists($p, 'getEmail') ? $p->getEmail() : 'Dato no disponible',
        ], $personas);

        return $this->json(['items' => $data, 'pages' => ceil($total / $limit), 'current_page' => $page]);
    }

    #[Route('/personas/nuevo', name: 'api_personas_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // CORRECCIÓN AQUÍ: NotBlank ya no acepta arrays de opciones en tu versión
        $errors = $validator->validate($data['nombre'] ?? '', [
            new Assert\NotBlank(), // Lo dejamos limpio para evitar el error de los logs
            new Assert\Regex('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/'),
        ]);

        if (count($errors) > 0) {
            return $this->json(['error' => 'Nombre no válido'], 400);
        }

        $persona = new Registro();
        $persona->setNombre($data['nombre']);

        // Ajusta esto según el nombre de tu método en la entidad (getEmail o getCorreo)
        if (method_exists($persona, 'setEmail')) {
            $persona->setEmail($data['email']);
        }

        $em->persist($persona);
        $em->flush();

        return $this->json(['status' => '¡Guardado!'], 201);
    }

    #[Route('/personas/{id}', name: 'api_personas_single', methods: ['GET'])]
    public function getOne(Registro $persona): JsonResponse
    {
        return $this->json([
            'id' => $persona->getId(),
            'nombre' => $persona->getNombre(),
            'email' => method_exists($persona, 'getEmail') ? $persona->getEmail() : 'N/A',
        ]);
    }

    #[Route('/personas/{id}', name: 'api_personas_edit', methods: ['PUT'])]
    public function edit(Registro $persona, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $persona->setNombre($data['nombre']);
        if (method_exists($persona, 'setEmail')) {
            $persona->setEmail($data['email']);
        }
        $em->flush();

        return $this->json(['status' => '¡Actualizado!']);
    }

    #[Route('/personas/{id}', name: 'api_personas_delete', methods: ['DELETE'])]
    public function delete(Registro $persona, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($persona);
        $em->flush();

        return $this->json(['status' => 'Eliminado']);
    }
}
