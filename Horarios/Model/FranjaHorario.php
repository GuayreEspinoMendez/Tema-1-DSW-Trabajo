<?php
require_once 'modulo.php';

class FranjaHorario extends Modulo {
    private $dia;
    private $hora;
    private $tipoFranja;
    private $color;

    public function __construct(?Curso $curso, ?Clase $clase, Materia $materia, $dia, $hora, $tipoFranja, $color) {
        parent::__construct($curso, $clase, $materia);
        $this->dia = $dia;
        $this->hora = $hora;
        $this->tipoFranja = $tipoFranja;
        $this->color = $color instanceof Color ? $color->value : $color;
    }

    public function getDia() {
        return $this->dia;
    }

    public function getHora() {
        return $this->hora;
    }

    public function getTipoFranja(): string {
        return $this->tipoFranja instanceof TipoFranja ? $this->tipoFranja->value : $this->tipoFranja;
    }

    public function getColor(): string {
        return $this->color ?? '';
    }

    // Setters for the new attributes
    public function setDia($dia): void {
        $this->dia = $dia;
    }

    public function setHora($hora): void {
        $this->hora = $hora;
    }

    public function setTipoFranja($tipoFranja): void {
        $this->tipoFranja = $tipoFranja instanceof TipoFranja ? $tipoFranja->value : $tipoFranja;
    }

    public function setColor($color): void {
        $this->color = $color instanceof Color ? $color->value : $color;
    }
}
?>