<?php

class Validaciones {

    /**
     * Valida que el curso seleccionado sea válido
     */
    public static function validarCurso($curso) {
        $cursos_validos = array_map(fn($c) => $c->value, Curso::cases());
        if (!in_array($curso, $cursos_validos)) {
            throw new Exception("El curso seleccionado no es válido.");
        }
    }

    /**
     * Valida que el día seleccionado sea válido
     */
    public static function validarDia($dia) {
        $dias_validos = array_map(fn($d) => $d->value, Semana::cases());
        if (!in_array($dia, $dias_validos)) {
            throw new Exception("El día seleccionado no es válido.");
        }
    }

    /**
     * Valida que el tipo de franja seleccionado sea válido
     */
    public static function validarTipoFranja($tipoFranja) {
        $tipos_validos = array_map(fn($t) => $t->value, TipoFranja::cases());
        if (!in_array($tipoFranja, $tipos_validos)) {
            throw new Exception("El tipo de franja seleccionado no es válido.");
        }
    }

    /**
     * Valida que la hora seleccionada sea válida
     */
    public static function validarHora($hora) {
        $horas_validas = array_map(fn($h) => $h->codigoHora(), Hora::cases());
        if (!in_array($hora, $horas_validas)) {
            throw new Exception("La hora seleccionada no es válida.");
        }
    }

    /**
     * Valida que la materia seleccionada sea válida
     */
    public static function validarMateria($materia) {
        $materias_validas = array_map(fn($m) => $m->value, Materia::cases());
        if (!in_array($materia, $materias_validas)) {
            throw new Exception("La materia seleccionada no es válida.");
        }
    }

    /**
     * Valida que el tipo de horario seleccionado sea válido
     */
    public static function validarTipoHorario($tipoHorario) {
        $tipos_validos = array_map(fn($t) => $t->value, TiposHorarios::cases());
        if (!in_array($tipoHorario, $tipos_validos)) {
            throw new Exception("El tipo de horario seleccionado no es válido.");
        }
    }

    /**
     * Valida el fichero subido
     */
    public static function validarFicheroSubido($fichero) {
        if (!isset($fichero['tmp_name']) || empty($fichero['tmp_name'])) {
            throw new Exception("No se ha seleccionado ningún fichero.");
        }

        $allowed_types = ['application/octet-stream', 'text/plain'];
        $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
        $detected_type = finfo_file($fileInfo, $fichero['tmp_name']);
        finfo_close($fileInfo);

        if (!in_array($detected_type, $allowed_types)) {
            throw new Exception("El tipo de fichero no es válido. Solo se permiten ficheros .dat o .txt");
        }

        if ($fichero['size'] > 5000000) { // 5MB limit
            throw new Exception("El fichero es demasiado grande. El tamaño máximo permitido es 5MB.");
        }
    }
}

