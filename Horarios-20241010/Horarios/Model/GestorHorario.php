<?php
class GestorHorario {

    public function insertarHora(FranjaHorario $franjaHorario) {
        $rutaFichero = 'horarios/horarios.dat';
        $directorio = 'horarios';
    
        // Crear el directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
    
        // Verificar si la franja horaria ya existe
        if ($this->existeFranjaHoraria($franjaHorario->getDia(), $franjaHorario->getHora())) {
            throw new Exception("Error Horario: La franja de hora ya existe, elige otra que esté disponible.");
        }

        // Verificar si la franja horaria es la primera hora de la tarde del martes
        if ($franjaHorario->getDia() == 'M' && $franjaHorario->getHora() == '14:30 - 15:25' || $franjaHorario->getHora() == '8') {
        // Verificar si la materia es "Reunión de departamento"
            if ($franjaHorario->getMateria()->value != 'RD') {
                throw new Exception("Error Horario: Esta franja horaria esta reservada únicamente para la hora complementaria 'Reunión de departamento', no se puede establecer.");
            }
        }

    // Verificar si ya existe una franja horaria de "Reunión de departamento"
        if ($franjaHorario->getMateria()->value == 'Reunión de departamento') {
            if ($this->existeReunionDepartamento()) {
                throw new Exception("Error Horario: Ya existe la franja horaria de reunión de departamento");
            }
        }

        $hora = $franjaHorario->getHora();
        if ($hora == '10:45 - 11:15' || $hora == '17:15 - 17:45' || $hora == '4' ||  $hora == '11') {
            throw new Exception("Error Horario: Esta franja horaria está reservada para los recreos.");
        }

        // Verificar si ya existe una franja horaria de tutoría
        if ($franjaHorario->getMateria()->value == 'TUO') {
            if ($this->existeTutoria()) {
                throw new Exception("Error Horario: La tutoría ya está establecida en el horario semanal");
            }
        }

        if ($this->existeGuardiaSeguida($franjaHorario)) {
            throw new Exception("Error Horario: Las guardias no pueden establecerse en franjas horarias seguidas");
        }
    
        // Contar el número de franjas horarias de la misma materia en el mismo día
        $materia = $franjaHorario->getMateria()->value;
        $dia = $franjaHorario->getDia();
        $contadorFranjasMateria = 0;
        $contadorFranjasDia = 0;
        $contadorFranjasNoLectivasDia = 0;
        $contadorFranjasLectivasSemana = 0;
        $contadorFranjasNoLectivasSemana = 0;
        if (file_exists($rutaFichero)) {
            $fp = fopen($rutaFichero, 'r');
            while (($line = fgets($fp)) !== false) {
                $parts = explode(';', $line);
                if ($parts[1] == $dia) {
                    $contadorFranjasDia++;
                    if ($parts[6] != 'L') { // Asumo que el tipo de franja horaria se almacena en la posición 6
                        $contadorFranjasNoLectivasDia++;
                        $contadorFranjasNoLectivasSemana++;
                    } else {
                        $contadorFranjasLectivasSemana++;
                    }
                }
                if ($parts[1] == $dia && $parts[3] == $materia) {
                    $contadorFranjasMateria++;
                }
            }
            fclose($fp);
        }
    
        if ($contadorFranjasMateria >= 3) {
            throw new Exception("Error Horario: La franja horaria, ha superado el número de horas por día.");
        }
    
        if ($contadorFranjasDia >= 5) {
            throw new Exception("Error Horario: El número de horas lectivas durante el día se ha superado.");
        }
    
        if ($contadorFranjasNoLectivasDia >= 3) {
            throw new Exception("Error Horario: El número de horas complementarias durante este día se ha superado.");
        }
    
        if ($contadorFranjasLectivasSemana >= 18) {
            throw new Exception("Error Horario: El número de horas lectivas durante la semana se ha superado.");
        }
    
        if ($contadorFranjasNoLectivasSemana >= 6) {
            throw new Exception("Error Horario: El número de horas complementarias durante la semana se ha superado.");
        }
    
        // Abrir el fichero en modo append (si no existe, se creará)
        $fp = fopen($rutaFichero, 'a');
    
        // Verificar si el fichero se abrió correctamente
        if ($fp) {
            // Obtener la información de la franja horaria
            $curso = $franjaHorario->getCurso()->value;
            $dia = $franjaHorario->getDia();
            $hora = $franjaHorario->getHora();
            $materia = $franjaHorario->getMateria()->value;
            $clase = $franjaHorario->getClase()->value;
            $color = $franjaHorario->getColor();
            $tipo = $franjaHorario->getTipoFranja();
    
            // Formatear la información para escribirla en el fichero
            $registro = "$curso;$dia;$hora;$materia;$clase;$color;$tipo@";
    
            // Escribir el registro en el fichero
            fwrite($fp, $registro);
    
            // Cerrar el fichero
            fclose($fp);
        } else {
            // Error al abrir el fichero
            throw new Exception("Error al abrir el fichero $rutaFichero");
        }
    }
    private function existeFranjaHoraria($dia, $hora) {
        $rutaFichero = 'horarios/horarios.dat';
        if (file_exists($rutaFichero)) {
            $fp = fopen($rutaFichero, 'r');
            while (($line = fgets($fp)) !== false) {
                $parts = explode(';', $line);
                if ($parts[1] == $dia && $parts[2] == $hora) {
                    fclose($fp);
                    return true; // La franja horaria ya existe
                }
            }
            fclose($fp);
        }
        return false; // La franja horaria no existe
    }

    private function existeReunionDepartamento() {
        $rutaFichero = 'horarios/horarios.dat';
        if (file_exists($rutaFichero)) {
            $fp = fopen($rutaFichero, 'r');
            while (($line = fgets($fp)) !== false) {
                $parts = explode(';', $line);
                if ($parts[3] == 'RD') {
                    fclose($fp);
                    return true;
                }
            }
            fclose($fp);
        }
        return false;
    }

    private function existeTutoria() {
        $rutaFichero = 'horarios/horarios.dat';
        if (file_exists($rutaFichero)) {
            $fp = fopen($rutaFichero, 'r');
            while (($line = fgets($fp)) !== false) {
                $parts = explode(';', $line);
                if ($parts[3] == 'TUO') {
                    fclose($fp);
                    return true;
                }
            }
            fclose($fp);
        }
        return false;
    }

    private function existeGuardiaSeguida(FranjaHorario $franjaHorario) {
        $rutaFichero = 'horarios/horarios.dat';
        if (file_exists($rutaFichero)) {
            $fp = fopen($rutaFichero, 'r');
            $guardias = [];
            while (($line = fgets($fp)) !== false) {
                $parts = explode(';', $line);
                if ($parts[3] == 'G') {
                    $guardias[] = $parts[1] . ' ' . $parts[2];
                }
            }
            fclose($fp);
    
            $dia = $franjaHorario->getDia();
            $hora = $franjaHorario->getHora();
            $guardiaActual = $dia . ' ' . $hora;
    
            foreach ($guardias as $guardia) {
                $parts = explode(' ', $guardia);
                $diaGuardia = $parts[0];
                $horaGuardia = $parts[1];
    
                if ($dia == $diaGuardia) {
                    $horaInicio = strtotime($hora);
                    $horaFin = strtotime($horaGuardia);
    
                    if (abs($horaInicio - $horaFin) <= 60 * 60) { // 1 hora
                        return true;
                    }
                }
            }
        }
        return false;
    }
    public function eliminarHora(FranjaHorario $franjaHorario) {
        $rutaFichero = 'horarios/horarios.dat';
        $directorio = 'horarios';
    
        // Verificar si el fichero existe
        if (!file_exists($rutaFichero)) {
            throw new Exception("Error: El fichero de datos no existe.");
        }
    
        // Verificar si la franja horaria existe
        if (!$this->existeFranjaHoraria($franjaHorario->getDia(), $franjaHorario->getHora())) {
            throw new Exception("Error Eliminar Hora: La hora y el día seleccionado no existe, no se puede eliminar.");
        }
    
        // Leer el contenido del fichero
        $contenido = file($rutaFichero, FILE_IGNORE_NEW_LINES);
    
        // Buscar el registro que se desea eliminar
        $claveEliminar = null;
        foreach ($contenido as $clave => $linea) {
            $parts = explode(';', $linea);
            if ($parts[1] == $franjaHorario->getDia() && $parts[2] == $franjaHorario->getHora()) {
                // Verificar si es una franja de Tutoría
                if ($parts[3] == 'TUO') {
                    // Contar las cotutorías
                    $contadorCotutorias = 0;
                    foreach ($contenido as $lineaCot) {
                        $partsCot = explode(';', $lineaCot);
                        if ($partsCot[3] == 'COTUTO') { // Asumiendo que 'COT' es el identificador para cotutorías
                            $contadorCotutorias++;
                        }
                    }
                    if ($contadorCotutorias >= 3) {
                        throw new Exception("Error Eliminar hora: No se puede eliminar la tutoría, se deben eliminar primero el resto de cotutorías.");
                    }
                }
    
                // Si no es una franja preestablecida, se procede a eliminar
                $claveEliminar = $clave;
                break;
            }
        }
    
        // Verificar si se encontró el registro
        if ($claveEliminar === null) {
            throw new Exception("Error Eliminar Hora: La hora y el día seleccionado no existe, no se puede eliminar.");
        }
    
        // Eliminar el registro del contenido
        unset($contenido[$claveEliminar]);
    
        // Sobreescribir el contenido del fichero
        file_put_contents($rutaFichero, implode("\n", $contenido));
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

    private function contarHorasPorDia($dia, $materia) {
        // Implementar la lógica para contar las horas de una materia en un día
    }

    // Agregar más métodos privados para ayudar con las validaciones y la lógica
}