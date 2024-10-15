<?php
class GestorHorario {
    private $rutaFichero = 'Datos/horarios.dat';

    public function insertarHora(FranjaHorario $franjaHorario) {
        // Implementar las validaciones y la lógica de inserción
        // Usar las condiciones 1-11 mencionadas en los requerimientos
    }

    public function eliminarHora(FranjaHorario $franjaHorario) {
        // Implementar la lógica de eliminación
        // Usar las condiciones 12-14 mencionadas en los requerimientos
    }

    public function mostrarHorario() {
        // Implementar la lógica para generar la tabla HTML
        // Leer el fichero horarios.dat y generar la tabla dinámicamente
    }

    public function subirFichero($rutaFicheroSubido) {
        // Implementar la lógica para sobrescribir horarios.dat con el nuevo fichero
    }

    public function generarHorario($tipoHorario) {
        // Implementar la lógica para generar horarios de mañana, tarde o mixto
        // Aplicar todas las condiciones mencionadas en los requerimientos
    }

    private function existeFranjaHoraria($dia, $hora) {
        // Implementar la lógica para verificar si una franja horaria ya existe
    }

    private function contarHorasPorDia($dia, $materia) {
        // Implementar la lógica para contar las horas de una materia en un día
    }

    // Agregar más métodos privados para ayudar con las validaciones y la lógica
}