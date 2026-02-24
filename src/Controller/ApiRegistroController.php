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
                // Si esto sigue fallando, cámbialo por el nombre exacto de tu campo
                'email' => method_exists($p, 'getEmail') ? $p->getEmail() : 'Dato no disponible',
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

        // CORRECCIÓN: Regex ahora recibe el string directo como primer argumento
        $errors = $validator->validate($data['nombre'] ?? '', [
            new Assert\NotBlank(),
            new Assert\Regex('/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/')
        ]);

        if (count($errors) > 0) {
            return $this->json(['error' => 'El nombre no debe llevar números.'], 400);
        }

        $persona = new Registro();
        $persona->setNombre($data['nombre']);

        // CORRECCIÓN: Asegúrate de usar el setter correcto según tu entidad
        if (method_exists($persona, 'setEmail')) {
            $persona->setEmail($data['email'] ?? null);
        }

        $em->persist($persona);
        $em->flush();

        return $this->json(['status' => 'Persona guardada con éxito'], 201);
    }
}
