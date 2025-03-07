<?php

namespace App\Repository;

use App\Entity\Playlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Playlist>
 */
class PlaylistRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Playlist::class);
    }

    /**
     * Encuentra una playlist por su nombre
     */
    public function findByNombre(string $nombre): ?Playlist
    {
        return $this->createQueryBuilder('p')
            ->where('p.nombre = :nombre')
            ->setParameter('nombre', $nombre)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve las playlists más populares según la cantidad de likes
     */
    public function findMostPopular(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.likes', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las playlists de un usuario específico
     */
    public function findByPropietario(int $usuarioId): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.propietario = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las playlists escuchadas por un usuario específico
     */
    public function findPlaylistsEscuchadasByUsuario(int $usuarioId): array
    {
        return $this->createQueryBuilder('p')
            ->select('p')
            ->innerJoin('p.usuariosEscucharon', 'u')
            ->where('u.id = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los usuarios que han escuchado una playlist específica
     */
    public function findUsuariosByPlaylist(int $playlistId): array
    {
        return $this->createQueryBuilder('p')
            ->select('u')
            ->innerJoin('p.usuariosEscucharon', 'u')
            ->where('p.id = :playlistId')
            ->setParameter('playlistId', $playlistId)
            ->orderBy('u.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las playlists que contienen una canción específica
     */
    public function findByCancion(int $cancionId): array
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.playlistCanciones', 'pc')
            ->innerJoin('pc.cancion', 'c')
            ->where('c.id = :cancionId')
            ->setParameter('cancionId', $cancionId)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las últimas playlists añadidas
     */
    public function findRecentPlaylists(int $limit): array
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function buscarPorPatron(string $query)
    {
        return $this->createQueryBuilder('p')
            ->where('p.nombre LIKE :query')
            ->setParameter('query', "%$query%")
            ->getQuery()
            ->getResult();
    }

    public function findPublicPlaylists()
    {
        return $this->createQueryBuilder('p')
            ->where('p.visibilidad = :publica')
            ->setParameter('publica', 'publica')
            ->getQuery()
            ->getResult();
    }

    /**
 * Obtiene el total de likes por playlist.
 */
public function obtenerLikesPorPlaylist(): array
{
    return $this->createQueryBuilder('p')
        ->select('p.nombre AS playlist, p.likes AS totalLikes')
        ->orderBy('p.likes', 'DESC')
        ->getQuery()
        ->getResult();
}

}
