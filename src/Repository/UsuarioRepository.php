<?php

namespace App\Repository;

use App\Entity\Usuario;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Usuario>
 */
class UsuarioRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Usuario::class);
    }

    /**
     * Encuentra un usuario por su correo electrónico
     */
    public function findByEmail(string $email): ?Usuario // Que busca ( con que dato de referencia) : que devuelve(? habre la opcion de que no exista)
    {
        return $this->createQueryBuilder('u') // alias para usuario
            ->where('u.email = :email')
            ->setParameter('email', $email) // pasa el argumento como un valor a la consulta evitando inyecciones SQL
            ->getQuery()
            ->getOneOrNullResult(); // cuando el resultad de la consulta es 1 resultado o null
    }

    /**
     * Devuelve los usuarios con más playlists creadas
     */
    public function findTopCreators(int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, COUNT(p.id) as numPlaylists')
            ->leftJoin('u.playlists', 'p')
            ->groupBy('u.id')
            ->orderBy('numPlaylists', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult(); // cuando el resultado esperado es una collecion de datos
    }

    /**
     * Devuelve los usuarios con más canciones subidas
     */
    public function findTopUploaders(int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, COUNT(c.id) as numCanciones')
            ->leftJoin('u.canciones', 'c')
            ->groupBy('u.id')
            ->orderBy('numCanciones', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve los usuarios con más reproducciones en canciones
     */
    public function findTopListeners(int $limit): array
    {
        return $this->createQueryBuilder('u')
            ->select('u, SUM(uc.reproducciones) as totalReproducciones')
            ->leftJoin('u.usuarioCancion', 'uc')
            ->groupBy('u.id')
            ->orderBy('totalReproducciones', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Encuentra el usuario que subió una canción específica
     */
    public function findUploaderByCancion(int $cancionId): ?Usuario
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.canciones', 'c')
            ->where('c.id = :cancionId')
            ->setParameter('cancionId', $cancionId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Devuelve todas las canciones subidas por un usuario específico
     */
    public function findCancionesByUsuario(int $usuarioId): array
    {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.canciones', 'c')
            ->where('u.id = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->select('c')
            ->orderBy('c.fechaCreacion', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Devuelve las playlists escuchadas por un usuario específico
     */
    public function findPlaylistsEscuchadasByUsuario(int $usuarioId): array
    {
        return $this->createQueryBuilder('u')
            ->select('p')
            ->innerJoin('u.playlistsEscuchadas', 'p')
            ->where('u.id = :usuarioId')
            ->setParameter('usuarioId', $usuarioId)
            ->orderBy('p.nombre', 'ASC')
            ->getQuery()
            ->getResult();
    }


    public function obtenerDistribucionEdades(): array
{
    return $this->createQueryBuilder('u')
        ->select('YEAR(CURRENT_DATE()) - YEAR(u.fechaNacimiento) AS edad, COUNT(u.id) AS cantidad')
        ->groupBy('edad')
        ->orderBy('edad', 'ASC')
        ->getQuery()
        ->getResult();
}

public function loadUserByIdentifier(string $identifier): ?Usuario
{
    return $this->createQueryBuilder('u')
        ->where('u.email = :identifier OR u.nombre = :identifier')
        ->setParameter('identifier', $identifier)
        ->getQuery()
        ->getOneOrNullResult();
}

public function clasificarUsuariosXedad(): array
{
    return $this->getEntityManager()->createQuery("
        SELECT 
            CASE 
                WHEN (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) < 18 THEN 'Menos de 18'
                WHEN (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) >= 18 AND (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) <= 25 THEN '18-25'
                WHEN (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) >= 26 AND (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) <= 35 THEN '26-35'
                WHEN (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) >= 36 AND (DATE_DIFF(CURRENT_DATE(), u.fechaNacimiento) / 365) <= 50 THEN '36-50'
                ELSE 'Más de 50' 
            END AS rango_edad, 
            COUNT(u.id) AS count
        FROM App\Entity\Usuario u
        WHERE u.fechaNacimiento IS NOT NULL
        GROUP BY rango_edad
        ORDER BY count DESC
    ")->getResult();
}



private function calcularEdad($fecha_nacimiento)
{
    if (!$fecha_nacimiento instanceof \DateTime) {
        $fecha_nacimiento = new \DateTime($fecha_nacimiento);
    }
    $hoy = new \DateTime();
    $edad = $hoy->diff($fecha_nacimiento);
    return $edad->y;
}
private function clasificarEdad($edad)
{
    if ($edad < 18) {
        return 'Menor de 18';
    } elseif ($edad >= 18 && $edad <= 30) {
        return '18-30 años';
    } elseif ($edad >= 31 && $edad <= 40) {
        return '31-40 años';
    } elseif ($edad >= 41 && $edad <= 50) {
        return '41-50 años';
    } else {
        return 'Más de 50 años';
    }
}


    
}
