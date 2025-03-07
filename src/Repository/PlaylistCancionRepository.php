<?php

namespace App\Repository;

use App\Entity\PlaylistCancion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlaylistCancion>
 */
class PlaylistCancionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlaylistCancion::class);
    }

    /**
     * Devuelve las canciones de una playlist específica
     */
    public function findByPlaylist(int $playlistId): array
    {
        return $this->createQueryBuilder('pc')
            ->where('pc.playlist = :playlistId')
            ->setParameter('playlistId', $playlistId)
            ->orderBy('pc.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las playlists donde se encuentra una canción específica
     */
    public function findByCancion(int $cancionId): array
    {
        return $this->createQueryBuilder('pc')
            ->where('pc.cancion = :cancionId')
            ->setParameter('cancionId', $cancionId)
            ->orderBy('pc.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las canciones más reproducidas en playlists
     */
    public function findMostPlayed(int $limit): array
    {
        return $this->createQueryBuilder('pc')
            ->select('pc, SUM(pc.reproducciones) as totalReproducciones')
            ->groupBy('pc.cancion')
            ->orderBy('totalReproducciones', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    } // lo dejo pero lo vamos a manejar desde usuario_cancion


    /**
     * Devuelve duracion total y numero de canciones
     */
    public function getPlaylistStats(int $playlistId): array
    {
        return $this->createQueryBuilder('pc')
            ->select('COUNT(pc.id) as totalCanciones', 'SUM(c.duracion) as totalDuracion')
            ->innerJoin('pc.cancion', 'c')
            ->where('pc.playlist = :playlistId')
            ->setParameter('playlistId', $playlistId)
            ->getQuery()
            ->getSingleResult();
    }

    public function obtenerReproduccionesPorEstilo(): array
{
    return $this->createQueryBuilder('pc')
        ->select('e.nombre AS estilo, SUM(pc.reproducciones) AS totalReproducciones')
        ->join('pc.cancion', 'c')
        ->join('c.estilo', 'e')
        ->groupBy('e.id')
        ->orderBy('totalReproducciones', 'DESC')
        ->getQuery()
        ->getResult();
}

public function obtenerLikesPorPlaylist(): array
{
    return $this->createQueryBuilder('pc')
        ->select('p.nombre AS playlist, SUM(pc.likes) AS totalLikes')
        ->join('pc.playlist', 'p')
        ->groupBy('p.id')
        ->orderBy('totalLikes', 'DESC')
        ->getQuery()
        ->getResult();
}

/**
 * Obtiene el total de reproducciones por playlist.
 */
public function obtenerReproduccionesPorPlaylist(): array
{
    return $this->createQueryBuilder('pc')
        ->select('p.nombre AS playlist, SUM(pc.reproducciones) AS totalReproducciones')
        ->join('pc.playlist', 'p')
        ->groupBy('p.id')
        ->orderBy('totalReproducciones', 'DESC')
        ->getQuery()
        ->getResult();
}
}



