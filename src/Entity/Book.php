<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Helpers\EntityTrait;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BookRepository")
 */
class Book
{
    use EntityTrait;
    

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $tipo;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $asignatura;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nombre;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $autor;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $editorial;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $codigo;

    /**
     * @ORM\Column(type="integer", options={"default":"0"})
     */
    private $status = 0;

    public function getId()
    {
        return $this->id;
    }

    function getTipo()
    {
        return $this->tipo;
    }

    function getAsignatura()
    {
        return $this->asignatura;
    }

    function getNombre()
    {
        return $this->nombre;
    }

    function getAutor()
    {
        return $this->autor;
    }

    function getEditorial()
    {
        return $this->editorial;
    }

    function getCodigo()
    {
        return $this->codigo;
    }

    function getStatus()
    {
        return $this->status;
    }

    function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    function setAsignatura($asignatura)
    {
        $this->asignatura = $asignatura;
    }

    function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }

    function setAutor($autor)
    {
        $this->autor = $autor;
    }

    function setEditorial($editorial)
    {
        $this->editorial = $editorial;
    }

    function setCodigo($codigo)
    {
        $this->codigo = $codigo;
    }

    function setStatus($status)
    {
        $this->status = $status;
    }
}
