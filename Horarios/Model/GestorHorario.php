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
        if ($franjaHorario->getMateria()->value != 'RE') {
            throw new Exception("Error Horario: Esta franja horaria está reservada para los recreos.");
        }
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
    
        $tabla = '<table border="1" style="width:100%; table-layout:fixed;"><tr><th>Hora</th>';
        foreach ($dias as $dia) {
            $tabla .= "<th>{$dia->value}</th>";
        }
        $tabla .= '</tr>';
    
        foreach ($horas as $hora) {
            $tabla .= "<tr><td>{$hora->value}</td>";
            foreach ($dias as $dia) {
                $celda = $horario[$dia->value][$hora->codigoHora()] ?? null;
                if ($celda) {
                    $contenido = '';
                    $color = $celda['color'];
                    
                    // Asignar colores específicos
                    if ($celda['tipo'] == 'C' || $celda['materia'] == 'RE') {
                        $color = '#87ceeb'; // Color para complementarias y recreo
                    }
                    
                    $estilo = "style='background-color:{$color}; padding:5px; height:60px; font-size: 12px; text-align: center;'";
                    
                    if ($celda['materia'] == 'RE' || $celda['materia'] == 'OT' || $celda['materia'] == 'COTUTO' || $celda['materia'] == 'G' || $celda['materia'] == 'RD') {
                        $contenido = $celda['materia'];
                    } else {
                        $contenido = "{$celda['curso']}<br>{$celda['materia']}<br>{$celda['clase']}";
                    }
                    $tabla .= "<td $estilo>$contenido</td>";
                } else {
                    $tabla .= '<td style="height:60px;"></td>';
                }
            }
            $tabla .= '</tr>';
        }
    
        $tabla .= '</table>';
        return $tabla;
    }

    public function subirFichero($rutaFicheroSubido) {
        $rutaFicheroDestino = 'horarios/horarios.dat';
    
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
        require_once 'Campos.php';
    
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
    
        // Limpiar el horario existente
        file_put_contents('horarios/horarios.dat', '');
    
        // Generar horario
        foreach ($diasSemana as $dia) {
            foreach ($horasDisponibles as $hora) {
                if ($this->esHoraRecreo($hora)) {
                    $this->insertarHora(new FranjaHorario(
                        Curso::from('1ADAW'),  // Un curso cualquiera, no es relevante para el recreo
                        Clase::from('R01'),    // Una clase cualquiera, no es relevante para el recreo
                        Materia::RECREO,
                        $dia->value,
                        $hora->codigoHora(),
                        TipoFranja::Recreo,
                        Color::AzulClaro
                    ));
                } elseif ($horasLectivas < 18 && mt_rand(0, 1) == 1) {
                    // Intentar insertar hora lectiva
                    try {
                        $curso = $cursos[array_rand($cursos)];
                        $materia = $materias[array_rand($materias)];
                        $clase = $clases[array_rand($clases)];
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
                    } catch (Exception $e) {
                        // Si falla, intentar con hora complementaria
                        $this->insertarHoraComplementaria($dia, $hora, $horasComplementarias);
                    }
                } else {
                    // Intentar insertar hora complementaria
                    $this->insertarHoraComplementaria($dia, $hora, $horasComplementarias);
                }
            }
        }
    
        $this->anadirHorasEspecificas();
    
        return "Horario generado correctamente.";
    }
    
    private function insertarHoraComplementaria($dia, $hora, &$horasComplementarias) {
    try {
        $complementarias = ['OT', 'TUO', 'COTUTO', 'RD', 'G'];
        $complementaria = Materia::from($complementarias[array_rand($complementarias)]);
        $this->insertarHora(new FranjaHorario(
            Curso ::from('1ADAW'),  // Un curso cualquiera, no es relevante para la hora complementaria
            Clase::from('R01'),    // Una clase cualquiera, no es relevante para la hora complementaria
            $complementaria,
            $dia->value,
            $hora->codigoHora(),
            TipoFranja::Complementaria,
            Color::VerdeClaro
        ));
        $horasComplementarias++;
    } catch (Exception $e) {
        // Si falla, no hacer nada
    }
}
    private function esHoraRecreoBloqueada(Hora $hora): bool {
        return $hora === Hora::Cuarta || $hora === Hora::Onceava;
    }
    
    private function esHoraRecreo(Hora $hora): bool {
        return $hora === Hora::Cuarta || $hora === Hora::Onceava;
    }
    private function generarFranjaRecreoBloqueada(Semana $dia, Hora $hora): array {
        return [
            'curso' => '',
            'dia' => $dia->value,
            'hora' => $hora->codigoHora(),
            'materia' => Materia::RECREO->value,
            'clase' => '',
            'color' => Color::AzulClaro->value,
            'tipo' => TipoFranja::Recreo->value
        ];
    }
    private function generarFranjaLectiva(Semana $dia, Hora $hora): array {
        $materia = $this->seleccionarMateriaLectiva([]);
        $clase = Clase::cases()[array_rand(Clase::cases())];
        $curso = Curso::cases()[array_rand(Curso::cases())];
    
        return [
            'curso' => $curso->value,
            'dia' => $dia->value,
            'hora' => $hora->codigoHora(),
            'materia' => $materia->value,
            'clase' => $clase->value,
            'color' => $this->asignarColorMateria($materia),
            'tipo' => TipoFranja::Lectiva->value
        ];
    }
    
    private function generarFranjaComplementaria(Semana $dia, Hora $hora): array {
        $complementarias = [
            'OT',    // OTRO
            'TUO',   // TUTORÍA
            'COTUTO',// COTUTORIA
            'RD' , // REUNIÓN_DEPARTAMENTO
            'G'
        ];
    
        $complementaria = $complementarias[array_rand($complementarias)];
    
        return [
            'curso' => '',
            'dia' => $dia->value,
            'hora' => $hora->codigoHora(),
            'materia' => $complementaria,
            'clase' => '',
            'color' => Color::Amarillo->value,
            'tipo' => TipoFranja::Complementaria->value
        ];
    }
    
    private function seleccionarMateriaLectiva(array $materiasPorDia): Materia {
        $materiasDisponibles = array_diff_key(Materia::cases(), $materiasPorDia);
        return $materiasDisponibles[array_rand($materiasDisponibles)];
    }
    
    private function asignarColorMateria(Materia $materia): string {
        $colores = [
            Color::Rojo->value,
            Color::Azul->value,
            Color::Verde->value,
            Color::Amarillo->value,
            Color::Naranja->value,
            Color::Rosa->value
        ];
        $index = array_search($materia, Materia::cases());
        return $colores[$index % count($colores)];
    }
    
    
    private function ajustarHorasRequeridas(array &$horario, int $horasLectivas, int $horasComplementarias, array $horasDisponibles, TiposHorarios $tipoHorario): void {
        while ($horasLectivas < 18) {
            $dia = Semana::cases()[array_rand(Semana::cases())];
            $hora = $horasDisponibles[array_rand($horasDisponibles)];
            $materia = $this->seleccionarMateriaLectiva([]);
            $clase = Clase::cases()[array_rand(Clase::cases())];
            $curso = Curso::cases()[array_rand(Curso::cases())];
    
            $horario[] = [
                'curso' => $curso->value,
                'dia' => $dia->value,
                'hora' => $hora->codigoHora(),
                'materia' => $materia->value,
                'clase' => $clase->value,
                'color' => $this->asignarColorMateria($materia),
                'tipo' => TipoFranja::Lectiva->value
            ];
            $horasLectivas++;
        }
    
        while ($horasComplementarias < 6) {
            $dia = Semana::cases()[array_rand(Semana::cases())];
            $hora = $horasDisponibles[array_rand($horasDisponibles)];
            $horario[] = $this->generarFranjaComplementaria($dia, $hora);
            $horasComplementarias++;
        }
    }
    private function anadirHorasEspecificas(array &$horario): void {
        // Añadir Reunión de Departamento (RD)
        $diaRD = Semana::Martes;
        $horaRD = Hora::Octava; // Primera hora de la tarde
        $horario[] = [
            'curso' => '',
            'dia' => $diaRD->value,
            'hora' => $horaRD->codigoHora(),
            'materia' => Materia::REUNIÓN_DEPARTAMENTO->value,
            'clase' => '',
            'color' => Color::AzulClaro->value,
            'tipo' => TipoFranja::Complementaria->value
        ];
    
        // Añadir Tutoría (TUO)
        $diaTutoria = Semana::cases()[array_rand(Semana::cases())];
        $horaTutoria = Hora::cases()[array_rand(Hora::cases())];
        $horario[] = [
            'curso' => '',
            'dia' => $diaTutoria->value,
            'hora' => $horaTutoria->codigoHora(),
            'materia' => Materia::TUTORÍA->value,
            'clase' => '',
            'color' => Color::Verde->value,
            'tipo' => TipoFranja::Complementaria->value
        ];
    
        // Añadir Guardias (G)
        $guardiasAsignadas = 0;
        while ($guardiasAsignadas < 3) {
            $diaGuardia = Semana::cases()[array_rand(Semana::cases())];
            $horaGuardia = Hora::cases()[array_rand(Hora::cases())];
            
            // Verificar que no haya una guardia en la hora anterior o siguiente
            if (!$this->hayGuardiaAdyacente($horario, $diaGuardia, $horaGuardia)) {
                $horario[] = [
                    'curso' => '',
                    'dia' => $diaGuardia->value,
                    'hora' => $horaGuardia->codigoHora(),
                    'materia' => Materia::GUARDIA->value,
                    'clase' => '',
                    'color' => Color::Amarillo->value,
                    'tipo' => TipoFranja::Complementaria->value
                ];
                $guardiasAsignadas++;
            }
        }
    }
    
    private function guardarHorario(array $horario): void {
        $rutaFichero = 'horarios/horarios.dat';
        $directorio = dirname($rutaFichero);
    
        // Crear el directorio si no existe
        if (!is_dir($directorio)) {
            mkdir($directorio, 0777, true);
        }
    
        // Abrir el fichero en modo escritura
        $fp = fopen($rutaFichero, 'w');
    
        if ($fp) {
            foreach ($horario as $franja) {
                $linea = implode(';', [
                    $franja['curso'],
                    $franja['dia'],
                    $franja['hora'],
                    $franja['materia'],
                    $franja['clase'],
                    $franja['color'],
                    $franja['tipo']
                ]) . "@\n";
                fwrite($fp, $linea);
            }
    
            fclose($fp);
        } else {
            throw new Exception("No se pudo abrir el fichero para escribir el horario.");
        }
    }
    
    // Función auxiliar para verificar si hay una guardia en horas adyacentes
    private function hayGuardiaAdyacente(array $horario, Semana $dia, Hora $hora): bool {
        $horaAnterior = null;
        $horaSiguiente = null;
    
        $horaActual = array_search($hora, Hora::cases());
        if ($horaActual > 0) {
            $horaAnterior = Hora::cases()[$horaActual - 1];
        }
        if ($horaActual < count(Hora::cases()) - 1) {
            $horaSiguiente = Hora::cases()[$horaActual + 1];
        }
    
        foreach ($horario as $franja) {
            if ($franja['dia'] === $dia->value && $franja['hora'] === $horaAnterior?->codigoHora()) {
                return true;
            }
    
            if ($franja['dia'] === $dia->value && $franja['hora'] === $horaSiguiente?->codigoHora()) {
                return true;
            }
        }
    
        return false;
    }
    
}