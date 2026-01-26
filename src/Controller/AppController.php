<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Form\RegistroType;
use App\Model\RegistroDatos;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    /**
     * PASO 1: Pantalla de Registro con reCAPTCHA y Manejo de Errores.
     */
    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                // Si el reCAPTCHA es válido y no hay errores de validación
                $request->getSession()->set('usuario_nombre', $datos->nombre);

                // Mensaje opcional para confirmar que pasó el filtro
                $this->addFlash('success', 'Validación correcta, gallo.');

                return $this->redirectToRoute('app_confirmar');
            }
            // MANEJO DE ERRORES: Si algo falla (captcha o validación),
            // Symfony inyecta los errores en la vista automáticamente.
            $this->addFlash('error', '¡Aguas! Revisa los errores del formulario.');
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(),
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => '#'],
            ],
        ]);
    }

    /**
     * PASO 2: Confirmación de Datos.
     */
    #[Route('/confirmar', name: 'app_confirmar')]
    public function confirmar(Request $request): Response
    {
        $nombre = $request->getSession()->get('usuario_nombre', 'Desconocido');

        return $this->render('app/confirmar.html.twig', [
            'nombre' => $nombre,
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => $this->generateUrl('app_registro')],
                ['name' => 'Confirmar', 'url' => '#'],
            ],
        ]);
    }

    /**
     * PASO 3: Éxito y Guardado Final en MySQL.
     */
    #[Route('/exito', name: 'app_exito', methods: ['GET', 'POST'])]
    public function exito(Request $request, EntityManagerInterface $em): Response
    {
        // Solo guardamos si se llega por POST desde la confirmación
        if ($request->isMethod('POST')) {
            $nombre = $request->getSession()->get('usuario_nombre', 'Anónimo');

            $registro = new Registro();
            $registro->setNombre($nombre);

            try {
                $em->persist($registro);
                $em->flush();
                $this->addFlash('success', '¡Registro guardado con éxito en Railway!');
            } catch (\Exception $e) {
                // Manejo de error si falla la conexión a la DB
                $this->addFlash('error', 'Error al guardar: '.$e->getMessage());

                return $this->redirectToRoute('app_registro');
            }
        }

        return $this->render('app/exito.html.twig', [
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => $this->generateUrl('app_registro')],
                ['name' => 'Confirmar', 'url' => $this->generateUrl('app_confirmar')],
                ['name' => 'Éxito', 'url' => '#'],
            ],
        ]);
    }
}
