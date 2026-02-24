<?php

namespace App\Controller;

use App\Entity\Registro;
use App\Entity\ImagenCarrusel;
use App\Form\RegistroType;
use App\Model\RegistroDatos;
use Doctrine\ORM\EntityManagerInterface;
use ReCaptcha\ReCaptcha;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AppController extends AbstractController
{
    // --- VISTA: INICIO (OUTFITFLOW) ---
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('home/index.html.twig');
    }

    // --- VISTA: CRUD DE DEIDADES ---
    #[Route('/crud-api', name: 'app_registro_api')]
    public function crud(): Response
    {
        return $this->render('registro_api/index.html.twig');
    }

    // --- VISTA Y LÓGICA: REGISTRO PRO (RECAPTCHA) ---
    #[Route('/registro', name: 'app_registro')]
    public function registro(Request $request): Response
    {
        $datos = new RegistroDatos();
        $form = $this->createForm(RegistroType::class, $datos);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $secret = '6LdJQlcsAAAAAAjD68LES59fdLyxsThXkDPuRz62';
            $recaptcha = new ReCaptcha($secret);
            $gRecaptchaResponse = $request->request->get('g-recaptcha-response');
            $resp = $recaptcha->verify($gRecaptchaResponse);

            if ($resp->isSuccess()) {
                $request->getSession()->set('usuario_nombre', $datos->nombre);
                return $this->redirectToRoute('app_confirmar');
            }
            $this->addFlash('error', 'Google rechazó el captcha. Intenta de nuevo.');
        }

        return $this->render('app/registro.html.twig', [
            'formulario' => $form->createView(), // CORRECCIÓN: Evita el error de renderizado null
        ]);
    }

    // --- VISTA: CARRUSEL DE IMÁGENES ---
    #[Route('/carrusel', name: 'app_carrusel')]
    public function carrusel(EntityManagerInterface $em): Response
    {
        // CORRECCIÓN: Buscamos directo desde el EntityManager para no usar el repositorio inexistente
        $imagenes = $em->getRepository(ImagenCarrusel::class)->findAll();

        return $this->render('carrusel/index.html.twig', [
            'imagenes' => $imagenes,
        ]);
    }

    // --- LÓGICA: SUBIR IMAGEN AL CARRUSEL ---
    #[Route('/carrusel/subir', name: 'app_carrusel_subir', methods: ['POST'])]
    public function subir(Request $request, EntityManagerInterface $em): Response
    {
        $archivo = $request->files->get('imagen');
        if ($archivo) {
            $nuevoNombre = uniqid().'.'.$archivo->guessExtension();
            // Asegúrate de que la carpeta public/uploads exista en tu Fedora
            $archivo->move($this->getParameter('kernel.project_dir').'/public/uploads', $nuevoNombre);

            $img = new ImagenCarrusel();
            $img->setRuta($nuevoNombre);
            $em->persist($img);
            $em->flush();
            $this->addFlash('success', '¡Imagen de deidad subida!');
        }

        return $this->redirectToRoute('app_carrusel');
    }

    // --- RUTAS DE APOYO ---
    #[Route('/confirmar', name: 'app_confirmar')]
    public function confirmar(Request $request): Response
    {
        $nombre = $request->getSession()->get('usuario_nombre', 'Usuario');
        return $this->render('app/confirmar.html.twig', ['nombre' => $nombre]);
    }

    #[Route('/exito', name: 'app_exito')]
    public function exito(Request $request, EntityManagerInterface $em): Response
    {
        $nombre = $request->getSession()->get('usuario_nombre', 'Anónimo');
        if ($request->isMethod('POST')) {
            $registro = new Registro();
            $registro->setNombre($nombre);
            $em->persist($registro);
            $em->flush();
            $this->addFlash('success', '¡Datos guardados con éxito!');
        }
        return $this->render('app/exito.html.twig', ['nombre' => $nombre]);
    }
}
