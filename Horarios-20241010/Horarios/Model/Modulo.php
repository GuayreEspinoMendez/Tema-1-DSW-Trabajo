<?php
class Modulo {
    private $curso;
    private $clase;
    private $materia;

    public function __construct($curso, $clase, $materia) {
        $this->curso = $curso;
        $this->clase = $clase;
        $this->materia = $materia;
    }

    public function getCurso() {
        return $this->curso;
    }

    public function getClase() {
        return $this->clase;
    }

    public function getMateria() {
        return $this->materia;
    }
}
?>