<?php

namespace App\Controller;

use App\Entity\Cancion;
use App\Entity\Estilo;
use App\Repository\CancionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cancion', name: 'cancion_')]
class CancionController extends AbstractController
{
    #[Route('/canciones', name: 'listar_canciones', methods: ['GET'])]
    public function listarCanciones(CancionRepository $cancionRepository): Response
    {
        // Obtener todas las canciones desde el repositorio
        $canciones = $cancionRepository->findAll();

        // Renderizar la plantilla 'index.html.twig' y pasar las canciones
        return $this->render('cancion/index.html.twig', [
            'canciones' => $canciones,
        ]);
    }
    #[Route('/{id}', name: 'ver_cancion', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function verCancion(int $id, CancionRepository $cancionRepository, Request $request): Response
    {
        $cancion = $cancionRepository->find($id);
    
        if (!$cancion) {
            throw $this->createNotFoundException('Canción no encontrada');
        }
    
        // Determinar el tipo de respuesta basado en la cabecera "Accept"
        if ($request->headers->get('Accept') === 'application/json') {
            // Devolver JSON
            return $this->json([
                'id' => $cancion->getId(),
                'titulo' => $cancion->getTitulo(),
                'autor' => $cancion->getAutor(),
                'album' => $cancion->getAlbum(),
                'archivo' => $cancion->getArchivo(),
                'albumImagen' => $cancion->getAlbumImagen(),
                // Agrega más campos si es necesario
            ]);
        } else {
            // Devolver HTML (renderizar la plantilla Twig)
            return $this->render('cancion/detalle_cancion.html.twig', [
                'cancion' => $cancion,
            ]);
        }
    }
    
    
    
    

    #[Route('/crear', name: 'crear_cancion', methods: ['POST'])]
public function crearCancion(Request $request, EntityManagerInterface $em, CancionRepository $cancionRepository): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    if (empty($data['titulo']) || empty($data['autor'])) {
        return $this->json(['message' => 'El título y el autor son obligatorios'], Response::HTTP_BAD_REQUEST);
    }

    // Verificar si ya existe una canción con el mismo título
    $tituloExistente = $cancionRepository->findOneBy(['titulo' => $data['titulo']]);
    if ($tituloExistente) {
        return $this->json([
            'message' => 'Ya existe una canción con el título "'.$data['titulo'].'".',
            'path' => 'src/Controller/CancionController'
        ], Response::HTTP_CONFLICT);
    }

    // Crear nueva canción
    $cancion = new Cancion();
    $cancion->setTitulo($data['titulo']);
    $cancion->setAutor($data['autor']);
    $cancion->setDuracion($data['duracion'] ?? null);
    $cancion->setAlbum($data['album'] ?? null);
    $cancion->setAnio($data['anio'] ?? null);
    $cancion->setAlbumImagen($data['albumImagen'] ?? null); // Solo guarda el nombre del archivo
    $cancion->setArchivo($data['archivo'] ?? null); // Solo guarda el nombre del archivo
    $cancion->setFechaCreacion(new \DateTimeImmutable());

    // Manejo del género: se elige de la lista o se crea un nuevo género
    if (!empty($data['genero'])) {
        if (is_numeric($data['genero'])) {
            $genero = $em->getRepository(Estilo::class)->find($data['genero']);
        } else {
            $genero = new Estilo();
            $genero->setNombre($data['genero']);
            $em->persist($genero);
        }
        $cancion->setGenero($genero);
    }

    $em->persist($cancion);
    $em->flush();

    return $this->json(['message' => 'Canción creada correctamente'], Response::HTTP_CREATED);
}

    

#[Route('/editar/{id}', name: 'editar_cancion', methods: ['PUT'])]
public function editarCancion(int $id, Request $request, EntityManagerInterface $em, CancionRepository $cancionRepository): JsonResponse
{
    $cancion = $cancionRepository->find($id);
    if (!$cancion) {
        return $this->json(['message' => 'Canción no encontrada'], Response::HTTP_NOT_FOUND);
    }
    $data = json_decode($request->getContent(), true);

    // Verificar si el título ya existe en otra canción
    $tituloExistente = $cancionRepository->findOneBy(['titulo' => $data['titulo'] ?? $cancion->getTitulo()]);
    if ($tituloExistente && $tituloExistente->getId() !== $cancion->getId()) {
        return $this->json([
            'message' => 'Ya existe otra canción con el título '.$data['titulo'],
            'path' => 'src/Controller/CancionController'
        ], Response::HTTP_CONFLICT);
    }

    $cancion->setTitulo($data['titulo'] ?? $cancion->getTitulo());
    $cancion->setAutor($data['autor'] ?? $cancion->getAutor());
    $cancion->setDuracion($data['duracion'] ?? $cancion->getDuracion());
    $cancion->setAlbum($data['album'] ?? $cancion->getAlbum());
    $cancion->setAnio($data['anio'] ?? $cancion->getAnio());
    $cancion->setAlbumImagen($data['albumImagen'] ?? $cancion->getAlbumImagen()); // Solo guarda el nombre del archivo
    $cancion->setArchivo($data['archivo'] ?? $cancion->getArchivo()); // Solo guarda el nombre del archivo

    // Asignar género correctamente (solo permitir selección de un género existente)
    if (!empty($data['genero']) && is_numeric($data['genero'])) {
        $genero = $em->getRepository(Estilo::class)->find($data['genero']);
        if ($genero) {
            $cancion->setGenero($genero);
        }
    }

    $em->flush();

    return $this->json([
        'message' => 'Canción actualizada correctamente',
        'path' => 'src/Controller/CancionController'
    ]);
}

    

    #[Route('/eliminar/{id}', name: 'eliminar_cancion', methods: ['DELETE'])]
    public function eliminarCancion(int $id, EntityManagerInterface $em, CancionRepository $cancionRepository): JsonResponse
    {
        $cancion = $cancionRepository->find($id);
        if (!$cancion) {
            return $this->json(['message' => 'Canción no encontrada'], Response::HTTP_NOT_FOUND);
        }

        $em->remove($cancion);
        $em->flush();

        return $this->json(['message' => 'Canción eliminada correctamente']);
    }

    #[Route('/estilo/{estiloId}', name: 'canciones_por_estilo', methods: ['GET'])]
    public function cancionesPorEstilo(int $estiloId, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findByEstilo($estiloId));
    }

    #[Route('/album/{album}', name: 'canciones_por_album', methods: ['GET'])]
    public function cancionesPorAlbum(string $album, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findByAlbum($album));
    }

    #[Route('/mas_reproducidas/{limit}', name: 'canciones_mas_reproducidas', methods: ['GET'])]
    public function cancionesMasReproducidas(int $limit, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findTopReproducciones($limit));
    }

    #[Route('/buscar/{titulo}', name: 'buscar_cancion_por_titulo', methods: ['GET'])]
    public function buscarCancionPorTitulo(string $titulo, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findByTitulo($titulo));
    }

    #[Route('/usuario/{usuarioId}', name: 'canciones_por_usuario', methods: ['GET'])]
    public function cancionesPorUsuario(int $usuarioId, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findBy(['usuario' => $usuarioId]));
    }

    #[Route('/recientes/{limit}', name: 'canciones_recientes', methods: ['GET'])]
    public function cancionesRecientes(int $limit, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findRecentCanciones($limit));
    }

    #[Route('/playlist/{cancionId}', name: 'playlist_por_cancion', methods: ['GET'])]
    public function playlistPorCancion(int $cancionId, CancionRepository $cancionRepository): JsonResponse
    {
        return $this->json($cancionRepository->findPlaylistsByCancion($cancionId));
    }
}
