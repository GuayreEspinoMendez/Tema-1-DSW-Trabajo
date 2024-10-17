<?php
require_once 'modulo.php';

class FranjaHorario extends Modulo {
    private $dia;
    private $hora;
    private $tipoFranja;
    private $color;

    public function __construct(Curso $curso, Clase $clase, Materia $materia, $dia, $hora, $tipoFranja, $color) {
        parent::__construct($curso, $clase, $materia); // Llama al constructor de la clase padre
        $this->dia = $dia;
        $this->hora = $hora;
        $this->tipoFranja = $tipoFranja;
        $this->color = $color;
    }

    public function getDia() {
        return $this->dia;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getTipoFranja() {
        return $this->tipoFranja;
    }

    public function getColor() {
        return $this->color;
    }

    // Setters para los nuevos atributos
    public function setDia($dia): void {
        $this->dia = $dia;
    }

    public function setHora($hora): void {
        $this->hora = $hora;
    }

    public function setTipoFranja($tipoFranja): void {
        $this->tipoFranja = $tipoFranja;
    }

    public function setColor($color): void {
        $this->color = $color;
    }
}
?>