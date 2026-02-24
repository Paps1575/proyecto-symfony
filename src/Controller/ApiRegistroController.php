<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Repository\RegistroRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

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
                // CORRECCIÓN: Usamos getCorreo() que es el estándar que parece tener tu entidad
                'email' => method_exists($p, 'getCorreo') ? $p->getCorreo() : 'N/A',
            ];
        }

        return $this->json([
            'items' => $data,
            'pages' => ceil($total / $limit),
            'current_page' => $page,
        ]);
    }

    #[Route('/personas/{id}', name: 'api_personas_single', methods: ['GET'])]
    public function getOne(Registro $persona): JsonResponse
    {
        return $this->json([
            'id' => $persona->getId(),
            'nombre' => $persona->getNombre(),
            'email' => method_exists($persona, 'getCorreo') ? $persona->getCorreo() : 'N/A',
            'telefono' => method_exists($persona, 'getTelefono') ? $persona->getTelefono() : 'N/A',
        ]);
    }

    #[Route('/personas/{id}', name: 'api_personas_delete', methods: ['DELETE'])]
    public function delete(Registro $persona, EntityManagerInterface $em): JsonResponse
    {
        $em->remove($persona);
        $em->flush();
        return $this->json(['status' => 'Eliminado']);
    }
}
