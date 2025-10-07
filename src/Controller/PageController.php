<?php

namespace App\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Contacto;
use App\Entity\Provincia;

final class PageController extends AbstractController
{
    private $contactos = [
        1 => ["nombre" => "Juan Pérez", "telefono" => "524142432", "email" => "juanp@ieselcaminas.org"],
        2 => ["nombre" => "Ana López", "telefono" => "58958448", "email" => "anita@ieselcaminas.org"],
        5 => ["nombre" => "Mario Montero", "telefono" => "5326824", "email" => "mario.mont@ieselcaminas.org"],
        7 => ["nombre" => "Laura Martínez", "telefono" => "42898966", "email" => "lm2000@ieselcaminas.org"],
        9 => ["nombre" => "Nora Jover", "telefono" => "54565859", "email" => "norajover@ieselcaminas.org"]

    ]; 
        
    #[Route('/page', name: 'app_page')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/PageController.php',
        ]);
    }

    #[Route('/', name: 'inicio')]
    public function inicio(): Response
    {
        return $this->render('inicio.html.twig');
    }

    /*#[Route('/contacto/{codigo?1}', name: 'ficha_contacto')]
    public function ficha($codigo): Response
    {
        $contacto = $this->contactos[$codigo] ?? null;
        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
    }*/

    #[Route('/insertar', name:'insertar_contacto')]
    public function insertar(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        foreach($this->contactos as $c) {
            $contacto = new Contacto();
            $contacto->setNombre($c['nombre']);
            $contacto->setTelefono($c['telefono']);
            $contacto->setEmail($c['email']);
            $entityManager->persist($contacto);
        }

        try {
            $entityManager->flush();
            return new Response("Contactos insertados correctamente");
        } catch (\Exception $e) {
            return new Response("Error al insertar los contactos: " . $e->getMessage());
        }
    }

    #[Route('/contacto/{codigo?1}', name: 'ficha_contacto')]
    public function ficha(ManagerRegistry $doctrine, $codigo): Response
    {
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
    }

    #[Route('/contacto/update/{codigo?1}', name: 'update_contacto')]
    public function update(ManagerRegistry $doctrine, $codigo): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        if ($contacto) {
            $contacto->setNombre("Nuevo Nombre");
            try {
                $entityManager->flush();
                return $this->render("ficha_contacto.html.twig", ["contacto" => $contacto]);
            } catch (\Exception $e) {
                return new Response("Error al actualizar el contacto: " . $e->getMessage());
            }
        } else {
            return $this->render("ficha_contacto.html.twig", ["contacto" => null]);
        }
    }

    #[Route('/contacto/delete/{codigo?1}', name: 'delete_contacto')]
    public function delete(ManagerRegistry $doctrine, $codigo): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Contacto::class);
        $contacto = $repositorio->find($codigo);

        if ($contacto) {
            $entityManager->remove($contacto);
            $entityManager->flush();
            return new Response("Contacto eliminado correctamente");
        } else {
            return new Response("No se ha encontrado el contacto con código $codigo");
        }
    }

    #[Route('/contactoConProvincia/insertar/{codigo?1}', name: 'update_contactoConProvincia')]
    public function createContactoConProvincia(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $provincia = new Provincia();

        $provincia->setNombre("Alicante");
        $contacto = new Contacto();

        $contacto->setNombre("Contacto con provincia");
        $contacto->setTelefono("123456789");
        $contacto->setEmail("dwadwa@fe.cwa");
        $contacto->setProvincia($provincia);

        $entityManager->persist($provincia);
        $entityManager->persist($contacto);

        try {
            $entityManager->flush();
            return new Response("Contacto con provincia insertado correctamente");
        } catch (\Exception $e) {
            return new Response("Error al insertar el contacto con provincia: " . $e->getMessage());
        }
    }

    #[Route('/contactoSinProvincia/insertar/{codigo?1}', name: 'update_contactoSinProvincia')]
    public function createContactoSinProvincia(ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $repositorio = $doctrine->getRepository(Provincia::class);

        $provincia = $repositorio->findOneBy(['nombre' => 'Alicante']);

        $contacto = new Contacto();

        $contacto->setNombre("Contacto sin provincia");
        $contacto->setTelefono("987654321");
        $contacto->setEmail("fcwafwa@fes.fea");
        $contacto->setProvincia($provincia);

        $entityManager->persist($contacto);

        try {
            $entityManager->flush();
            return new Response("Contacto sin provincia insertado correctamente");
        } catch (\Exception $e) {
            return new Response("Error al insertar el contacto sin provincia: " . $e->getMessage());
        }
    }
}