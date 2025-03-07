<?php

namespace App\Controller;

use App\Repository\PlaylistRepository;
use App\Repository\UsuarioRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EstadisticasController extends AbstractController
{
    #[Route('/manager', name: 'estadisticas')]
    public function index(PlaylistRepository $playlistRepo, UsuarioRepository $usuarioRepo): Response
    {
        // Obtener datos de likes por playlist
        $datos_likes = $playlistRepo->obtenerLikesPorPlaylist();

        // Obtener distribución de edades de usuarios
        $edad_datos = $usuarioRepo->clasificarUsuariosXedad();

        return $this->render('estadisticas/index.html.twig', [
            'datos_likes' => $datos_likes,
            'edad_datos' => $edad_datos,
        ]);
    }
}

