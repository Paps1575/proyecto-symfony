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
     * PASO 1: Pantalla de Registro
     * Aquí mostramos el formulario y aplicamos validaciones (Regex, NotBlank, etc.).
     */
    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Guardamos el nombre en la sesión para recuperarlo después
            $request->getSession()->set('usuario_nombre', $datos->nombre);

            return $this->redirectToRoute('app_confirmar');
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
     * PASO 2: Confirmación de Datos
     * Mostramos lo que el usuario escribió (recuperado de la sesión).
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
     * PASO 3: Éxito y Guardado en BD
     * El botón final dispara un POST para insertar el dato en MariaDB.
     */
    #[Route('/exito', name: 'app_exito', methods: ['GET', 'POST'])]
    public function exito(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = $request->getSession()->get('usuario_nombre', 'Anónimo');

            // Creamos el objeto de la entidad para la base de datos
            $registro = new Registro();
            $registro->setNombre($nombre);

            // Persistimos en MariaDB
            $em->persist($registro);
            $em->flush();

            $this->addFlash('success', '¡Registro guardado con éxito en la base de datos relacional!');
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
