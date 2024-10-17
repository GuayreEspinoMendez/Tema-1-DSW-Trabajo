<?php
class GestorHorario {

    public function insertarHora(FranjaHorario $franjaHorario) {
        $rutaFichero = '../horarios/horarios.dat';
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
        $rutaFichero = '../horarios/horarios.dat';
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
        $rutaFichero = '../horarios/horarios.dat';
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
        $rutaFichero = '../horarios/horarios.dat';
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
        $rutaFichero = '../horarios/horarios.dat';
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
        $rutaFichero = '../horarios/horarios.dat';
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
        require_once 'Campos.php';
        
        $rutaFichero = 'horarios/horarios.dat';
        $dias = Semana::cases();
        $horas = Hora::cases();
    
        $horario = [];
        if (file_exists($rutaFichero)) {
            $lineas = file($rutaFichero, FILE_IGNORE_NEW_LINES);
            foreach ($lineas as $linea) {
                $partes = explode(';', $linea);
                if (count($partes) >= 7) {
                    $dia = $partes[1];
                    $hora = $partes[2];
                    $horario[$dia][$hora] = [
                        'curso' => $partes[0],
                        'materia' => $partes[3],
                        'clase' => $partes[4],
                        'color' => $partes[5],
                        'tipo' => $partes[6]
                    ];
                }
            }
        }
    
        $tabla = '<table border="1" style="width:100%; table-layout:fixed;">';
        
        // Estilo para el encabezado
        $estiloEncabezado = 'style="background-color: black; color: white; padding: 10px; width: 14.28%;"';
        
        // Estilo para las celdas de las horas
        $estiloHora = 'style="background-color: #f0f0f0; padding: 5px 10px; text-align: left; width: 14.28%;"';
        
        $tabla .= "<tr><th $estiloEncabezado>Hora</th>";
        foreach ($dias as $dia) {
            $tabla .= "<th $estiloEncabezado>{$dia->value}</th>";
        }
        $tabla .= '</tr>';
    
        foreach ($horas as $hora) {
            $tabla .= "<tr><td $estiloHora>{$hora->value}</td>";
            foreach ($dias as $dia) {
                $celda = $horario[$dia->value][$hora->codigoHora()] ?? null;
                if ($celda) {
                    $contenido = '';
                    $color = $celda['color'];
                    
                    // Asignar colores específicos
                    if ($celda['tipo'] == 'C' || $celda['materia'] == 'RE') {
                        $color = '#87ceeb'; // Color para complementarias y recreo
                    }
                    
                    $estilo = "style='background-color:{$color}; padding:5px; height:60px; font-size: 12px; text-align: center; width: 14.28%;'";
                    
                    if ($celda['materia'] == 'RE' || $celda['materia'] == 'OT' || $celda['materia'] == 'COTUTO' || $celda['materia'] == 'G' || $celda['materia'] == 'RD') {
                        $contenido = $celda['materia'];
                    } else {
                        $contenido = "{$celda['curso']}<br>{$celda['materia']}<br>{$celda['clase']}";
                    }
                    $tabla .= "<td $estilo>$contenido</td>";
                } else {
                    $tabla .= '<td style="height:60px; width: 14.28%;"></td>';
                }
            }
            $tabla .= '</tr>';
        }
    
        $tabla .= '</table>';
        return $tabla;
    }

    public function subirFichero($rutaFicheroSubido) {
        $rutaFicheroDestino = '../horarios/horarios.dat';
    
        // Verificar si el fichero subido existe
        if (!file_exists($rutaFicheroSubido)) {
            throw new Exception("Error: El fichero subido no existe.");
        }
    
        // Verificar si el fichero subido es legible
        if (!is_readable($rutaFicheroSubido)) {
            throw new Exception("Error: No se puede leer el fichero subido.");
        }
    
        // Verificar si el directorio de destino existe, si no, crearlo
        $directorioDestino = dirname($rutaFicheroDestino);
        if (!is_dir($directorioDestino)) {
            if (!mkdir($directorioDestino, 0777, true)) {
                throw new Exception("Error: No se pudo crear el directorio de destino.");
            }
        }
    
        // Intentar copiar el fichero
        if (!copy($rutaFicheroSubido, $rutaFicheroDestino)) {
            throw new Exception("Error: No se pudo copiar el fichero.");
        }
    
        // Verificar si la copia fue exitosa
        if (!file_exists($rutaFicheroDestino)) {
            throw new Exception("Error: La copia del fichero no se completó correctamente.");
        }
    
        // Si todo ha ido bien, devolver un mensaje de éxito
        return "El fichero se ha subido y sobrescrito correctamente.";
    }

    public function generarHorario($tipoHorario) {
        require_once '../Model/Campos.php';
    
        $horario = [];
        $horasLectivas = 0;
        $horasComplementarias = 0;
        $diasSemana = Semana::cases();
        $materias = Materia::cases();
        $clases = Clase::cases();
        $cursos = Curso::cases();
    
        $horasManana = array_slice(Hora::cases(), 0, 7);
        $horasTarde = array_slice(Hora::cases(), 7);
    
        $horasDisponibles = [];
        switch ($tipoHorario) {
            case TiposHorarios::Mañana:
                $horasDisponibles = $horasManana;
                break;
            case TiposHorarios::Tarde:
                $horasDisponibles = $horasTarde;
                break;
            case TiposHorarios::Mixto:
                $horasDisponibles = Hora::cases();
                break;
            default:
                throw new Exception("Tipo de horario no válido");
        }
    
        // Generar horario
        foreach ($diasSemana as $dia) {
            $horasLectivasDia = 0;
            $horasComplementariasDia = 0;
            $materiasPorDia = [];
    
            foreach ($horasDisponibles as $hora) {
                if ($this->esHoraRecreoBloqueada($hora)) {
                    $this->insertarHora(new FranjaHorario(
                        Curso::from(''),
                        Clase::from(''),
                        Materia::from('RE'),
                        $dia->value,
                        $hora->codigoHora(),
                        TipoFranja::Recreo,
                        Color::AzulClaro
                    ));
                    continue;
                }
    
                if ($horasLectivas < 18 && $horasLectivasDia < 5 && mt_rand(0, 1) == 1) {
                    // Generar hora lectiva
                    $materia = $this->seleccionarMateriaLectiva($materiasPorDia);
                    $clase = $clases[array_rand($clases)];
                    $curso = $cursos[array_rand($cursos)];
    
                    $this->insertarHora(new FranjaHorario(
                        $curso,
                        $clase,
                        $materia,
                        $dia->value,
                        $hora->codigoHora(),
                        TipoFranja::Lectiva,
                        $this->asignarColorMateria($materia)
                    ));
                    $horasLectivas++;
                    $horasLectivasDia++;
                    $materiasPorDia[$materia->value] = ($materiasPorDia[$materia->value] ?? 0) + 1;
                } elseif ($horasComplementarias < 6 && $horasComplementariasDia < 3) {
                    // Generar hora complementaria
                    $this->insertarHora($this->generarFranjaComplementaria($dia, $hora));
                    $horasComplementarias++;
                    $horasComplementariasDia++;
                }
            }
        }
    
        // Asegurar que se cumplen las horas requeridas y añadir horas específicas
        $this->ajustarHorasRequeridas($horasLectivas, $horasComplementarias, $horasDisponibles);
        $this->anadirHorasEspecificas();
    
        return "Horario generado correctamente.";
    }

    private function esHoraRecreoBloqueada(Hora $hora): bool {
        return in_array($hora, [Hora::Cuarta, Hora::Onceava]);
    }
    
    private function seleccionarMateriaLectiva(array $materiasPorDia): Materia {
        $materiasDisponibles = array_diff(Materia::cases(), ['RE', 'TUO', 'COTUTO', 'G', 'RD']);
        
        do {
            $materia = $materiasDisponibles[array_rand($materiasDisponibles)];
        } while (($materiasPorDia[$materia->value] ?? 0) >= 3);
        
        return $materia;
    }
    
    private function asignarColorMateria(Materia $materia): Color {
        $coloresMateria = [
            'DSW' => Color::Naranja,
            'DOR' => Color::Verde,
            'DEW' => Color::Azul,
            'PRW' => Color::Rojo,
            'BAE' => Color::VerdeClaro,
            'PRO' => Color::Rosa,
            'OTRO' => Color::Amarillo
        ];
        
        return $coloresMateria[$materia->value] ?? Color::Blanco;
    }
    
    private function generarFranjaComplementaria(Semana $dia, Hora $hora): FranjaHorario {
        $materiasComplementarias = ['TUO', 'COTUTO', 'G', 'RD'];
        $materia = Materia::from($materiasComplementarias[array_rand($materiasComplementarias)]);
        
        return new FranjaHorario(
            Curso::from(''),
            Clase::from(''),
            $materia,
            $dia->value,
            $hora->codigoHora(),
            TipoFranja::Complementaria,
            Color::Azul
        );
    }
    
    private function ajustarHorasRequeridas(int &$horasLectivas, int &$horasComplementarias, array $horasDisponibles) {
        while ($horasLectivas < 18 || $horasComplementarias < 6) {
            $dia = Semana::cases()[array_rand(Semana::cases())];
            $hora = $horasDisponibles[array_rand($horasDisponibles)];
            
            if ($horasLectivas < 18) {
                $this->insertarHora($this->generarFranjaLectiva($dia, $hora));
                $horasLectivas++;
            } else {
                $this->insertarHora($this->generarFranjaComplementaria($dia, $hora));
                $horasComplementarias++;
            }
        }
    }
    
    private function anadirHorasEspecificas() {
        // Añadir Reunión de Departamento los martes a primera hora de la tarde
        $this->insertarHora(new FranjaHorario(
            Curso::from(''),
            Clase::from(''),
            Materia::from('RD'),
            Semana::Martes->value,
            Hora::Octava->codigoHora(),
            TipoFranja::Complementaria,
            Color::Azul
        ));
        
        // Añadir Tutoría
        $diaAleatorio = Semana::cases()[array_rand(Semana::cases())];
        $horaAleatoria = Hora::cases()[array_rand(Hora::cases())];
        $this->insertarHora(new FranjaHorario(
            Curso::from(''),
            Clase::from(''),
            Materia::from('TUO'),
            $diaAleatorio->value,
            $horaAleatoria->codigoHora(),
            TipoFranja::Complementaria,
            Color::Azul
            ));
        }

        private function generarFranjaLectiva(Semana $dia, Hora $hora): FranjaHorario {
            $materia = $this->seleccionarMateriaLectiva([]);
            $clase = Clase::cases()[array_rand(Clase::cases())];
            $curso = Curso::cases()[array_rand(Curso::cases())];
        
            return new FranjaHorario(
                $curso,
                $clase,
                $materia,
                $dia->value,
                $hora->codigoHora(),
                TipoFranja::Lectiva,
                $this->asignarColorMateria($materia)
            );
        }
        
}