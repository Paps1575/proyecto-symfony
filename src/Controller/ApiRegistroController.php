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
    // Función auxiliar para no repetir código y hallar el correo
    private function getEmailData(Registro $p): string {
        if (method_exists($p, 'getEmail') && $p->getEmail()) return $p->getEmail();
        if (method_exists($p, 'getCorreo') && $p->getCorreo()) return $p->getCorreo();
        if (method_exists($p, 'getMail') && $p->getMail()) return $p->getMail();
        return 'N/A';
    }

    #[Route('/personas', name: 'api_personas_list', methods: ['GET'])]
    public function list(RegistroRepository $repo, Request $request): JsonResponse {
        $limit = 5;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;
        $personas = $repo->findBy([], ['id' => 'DESC'], $limit, $offset);
        $total = $repo->count([]);

        $data = array_map(fn($p) => [
            'id' => $p->getId(),
            'nombre' => $p->getNombre(),
            'email' => $this->getEmailData($p),
        ], $personas);

        return $this->json(['items' => $data, 'pages' => ceil($total / $limit), 'current_page' => $page]);
    }

    #[Route('/personas/{id}', name: 'api_personas_single', methods: ['GET'])]
    public function getOne(Registro $persona): JsonResponse {
        return $this->json([
            'id' => $persona->getId(),
            'nombre' => $persona->getNombre(),
            'email' => $this->getEmailData($persona),
            // Asegúrate de que el método getTelefono exista en tu Registro.php
            'telefono' => method_exists($persona, 'getTelefono') ? $persona->getTelefono() : 'N/A',
        ]);
    }

    #[Route('/personas/{id}', name: 'api_personas_edit', methods: ['PUT'])]
    public function edit(Registro $persona, Request $request, EntityManagerInterface $em): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $persona->setNombre($data['nombre']);

        // Setter dinámico para el correo
        if (method_exists($persona, 'setEmail')) $persona->setEmail($data['email']);
        elseif (method_exists($persona, 'setCorreo')) $persona->setCorreo($data['email']);

        $em->flush();
        return $this->json(['status' => 'Actualizado']);
    }

    #[Route('/personas/{id}', name: 'api_personas_delete', methods: ['DELETE'])]
    public function delete(Registro $persona, EntityManagerInterface $em): JsonResponse {
        $em->remove($persona);
        $em->flush();
        return $this->json(['status' => 'Eliminado']);
    }
}
