<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Form\RegistroType;
use App\Model\RegistroDatos;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha; // Librería oficial de Google
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    /**
     * PASO 1: Registro con validación manual de reCAPTCHA v2.
     */
    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request, ReCaptcha $reCaptcha): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            // Obtenemos la respuesta del widget directamente del POST
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');

            // Verificamos con los servidores de Google usando la IP del cliente
            $resp = $reCaptcha->verify($gRecaptchaResponse, $request->getClientIp());

            if ($resp->isSuccess() && $form->isValid()) {
                // Guardamos el nombre en la sesión para el siguiente paso
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                $this->addFlash('success', 'Validación de seguridad correcta, gallo.');

                return $this->redirectToRoute('app_confirmar');
            }

            $this->addFlash('error', '¡Aguas! El captcha no es válido o faltan datos.');
        }

        // IMPORTANTE: Siempre enviamos el createView() para que Twig no reciba un null
        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(),
            'breadcrumbs' => [
                ['name' => 'Inicio', 'url' => '/'],
                ['name' => 'Registro', 'url' => '#'],
            ],
        ]);
    }

    /**
     * PASO 2: Confirmación de datos guardados en la sesión.
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
     * PASO 3: Éxito y guardado final en MySQL.
     */
    #[Route('/exito', name: 'app_exito', methods: ['GET', 'POST'])]
    public function exito(Request $request, EntityManagerInterface $em): Response
    {
        if ($request->isMethod('POST')) {
            $nombre = $request->getSession()->get('usuario_nombre', 'Anónimo');

            $registro = new Registro();
            $registro->setNombre($nombre);

            try {
                $em->persist($registro);
                $em->flush(); // Ejecuta el INSERT en la base de datos de Railway
                $this->addFlash('success', '¡Registro guardado con éxito en Railway!');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error al guardar: ' . $e->getMessage());
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
