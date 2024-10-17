<?php

class Modulo {
    private ?Curso $curso;
    private ?Clase $clase;
    private Materia $materia;

    public function __construct(?Curso $curso, ?Clase $clase, Materia $materia) {
        $this->curso = $curso;
        $this->clase = $clase;
        $this->materia = $materia;
    }

    public function setCurso(Curso $nuevocurso) {
        $this->curso = $nuevocurso;
    }

    public function setClase(Clase $nuevaclase) {
        $this->clase= $nuevaclase;
    }

    public function setMateria(Materia $nuevamateria) {
        $this->materia = $nuevamateria;
    }

    public function getCurso(): ?Curso {
        return $this->curso;
    }

    public function getClase(): ?Clase {
        return $this->clase;
    }

    public function getMateria(): Materia {
        return $this->materia;
    }
}
?>