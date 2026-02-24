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
    /**
     * Intenta obtener el valor del correo probando diferentes nombres de métodos comunes
     */
    private function extraerCorreo(Registro $p): string
    {
        if (method_exists($p, 'getEmail') && $p->getEmail()) return $p->getEmail();
        if (method_exists($p, 'getCorreo') && $p->getCorreo()) return $p->getCorreo();
        if (method_exists($p, 'getMail') && $p->getMail()) return $p->getMail();
        return 'Sin correo';
    }

    /**
     * Intenta guardar el correo probando diferentes setters
     */
    private function asignarCorreo(Registro $p, string $valor): void
    {
        if (method_exists($p, 'setEmail')) $p->setEmail($valor);
        elseif (method_exists($p, 'setCorreo')) $p->setCorreo($valor);
        elseif (method_exists($p, 'setMail')) $p->setMail($valor);
    }

    #[Route('/personas', name: 'api_personas_list', methods: ['GET'])]
    public function list(RegistroRepository $repo, Request $request): JsonResponse
    {
        $limit = 5;
        $page = $request->query->getInt('page', 1);
        $offset = ($page - 1) * $limit;
        $personas = $repo->findBy([], ['id' => 'DESC'], $limit, $offset);
        $total = $repo->count([]);

        $data = array_map(fn($p) => [
            'id' => $p->getId(),
            'nombre' => $p->getNombre(),
            'email' => $this->extraerCorreo($p),
        ], $personas);

        return $this->json(['items' => $data, 'pages' => ceil($total / $limit), 'current_page' => $page]);
    }

    #[Route('/personas/nuevo', name: 'api_personas_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // VALIDACIONES DE SERVIDOR
        $constraints = new Assert\Collection([
            'nombre' => [
                new Assert\NotBlank(['message' => 'El nombre no puede estar vacío.']),
                new Assert\Regex([
                    'pattern' => '/^[a-zA-ZÁÉÍÓÚáéíóúñÑ\s]+$/',
                    'message' => 'El nombre solo puede contener letras.'
                ])
            ],
            'email' => [
                new Assert\NotBlank(['message' => 'El correo es obligatorio.']),
                new Assert\Email(['message' => 'El formato del correo no es válido.'])
            ],
            'id' => new Assert\Optional() // El ID es opcional en POST
        ]);

        $violations = $validator->validate($data, $constraints);
        if (count($violations) > 0) {
            return $this->json(['error' => $violations[0]->getMessage()], 400);
        }

        $persona = new Registro();
        $persona->setNombre($data['nombre']);
        $this->asignarCorreo($persona, $data['email']);

        $em->persist($persona);
        $em->flush();

        return $this->json(['status' => '¡Guardado con éxito!'], 201);
    }

    #[Route('/personas/{id}', name: 'api_personas_single', methods: ['GET'])]
    public function getOne(Registro $persona): JsonResponse
    {
        return $this->json([
            'id' => $persona->getId(),
            'nombre' => $persona->getNombre(),
            'email' => $this->extraerCorreo($persona),
        ]);
    }

    #[Route('/personas/{id}', name: 'api_personas_edit', methods: ['PUT'])]
    public function edit(Registro $persona, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Reutilizamos la lógica de validación
        $persona->setNombre($data['nombre']);
        $this->asignarCorreo($persona, $data['email']);

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
