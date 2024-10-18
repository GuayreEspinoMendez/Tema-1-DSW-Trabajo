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
    
        if (!file_exists($rutaFichero)) {
            throw new Exception("Error: El fichero de datos no existe.");
        }
    
        $contenido = file_get_contents($rutaFichero);
        $franjas = explode('@', $contenido);
    
        $franjasActualizadas = array_filter($franjas, function($franja) use ($franjaHorario) {
            $parts = explode(';', $franja);
            $mantener = !(count($parts) >= 7 && $parts[1] === $franjaHorario->getDia() && $parts[2] === $franjaHorario->getHora());
            return $mantener;
        });
    
        if (count($franjasActualizadas) === count($franjas)) {
            throw new Exception("Error Eliminar Hora: La hora y el día seleccionado no existe, no se puede eliminar.");
        }
    
        $contenidoActualizado = implode('@', $franjasActualizadas);
        $resultado = file_put_contents($rutaFichero, $contenidoActualizado);
    
        return true;
    }
    

    public function mostrarHorario() {
        $rutaFichero = 'horarios/horarios.dat';
        
        if (!file_exists($rutaFichero)) {
            return "El archivo $rutaFichero no existe.";
        }
    
        $contenido = file_get_contents($rutaFichero);
        if (empty($contenido)) {
            return "El archivo $rutaFichero está vacío.";
        }
    
        $dias = Semana::cases();
        $horas = Hora::cases();
    
        $horario = [];
        $entradas = explode('@', $contenido);
        foreach ($entradas as $entrada) {
            $partes = explode(';', $entrada);
            if (count($partes) >= 7) {
                $curso = $partes[0];
                $dia = $partes[1];
                $hora = $partes[2];
                $materia = $partes[3];
                $clase = $partes[4];
                $color = $partes[5];
                $tipo = $partes[6];
                $horario[$dia][$hora] = [
                    'curso' => $curso,
                    'materia' => $materia,
                    'clase' => $clase,
                    'color' => $color,
                    'tipo' => $tipo
                ];
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
                    $contenido = "{$celda['curso']}<br>{$celda['materia']}<br>{$celda['clase']}";
                    $estilo = "style='background-color:{$celda['color']}; padding:5px;'";
                    $tabla .= "<td $estilo>$contenido</td>";
                } else {
                    $tabla .= '<td></td>';
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
    
        foreach ($diasSemana as $dia) {
            $horasLectivasDia = 0;
            $horasComplementariasDia = 0;
            $materiasPorDia = [];
    
            foreach ($horasDisponibles as $hora) {
                if ($horasLectivas >= 18 && $horasComplementarias >= 6) {
                    break 2; // Salir de ambos bucles si se han completado las horas requeridas
                }
    
                if ($this->esHoraRecreoBloqueada($hora)) {
                    $horario[] = $this->generarFranjaRecreoBloqueada($dia, $hora);
                    continue;
                }
    
                if ($horasLectivas < 18 && $horasLectivasDia < 5 && mt_rand(0, 1) == 1) {
                    // Generar hora lectiva
                    $materia = $this->seleccionarMateriaLectiva($materiasPorDia);
                    $clase = $clases[array_rand($clases)];
                    $curso = $cursos[array_rand($cursos)];
    
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
                    $horasLectivasDia++;
                    $materiasPorDia[$materia->value] = ($materiasPorDia[$materia->value] ?? 0) + 1;
                } elseif ($horasComplementarias < 6 && $horasComplementariasDia < 3) {
                    // Generar hora complementaria
                    $horario[] = $this->generarFranjaComplementaria($dia, $hora);
                    $horasComplementarias++;
                    $horasComplementariasDia++;
                }
            }
        }
    
        // Guardar el horario generado
        $this->guardarHorario($horario);
    
        return "Horario generado correctamente.";
    }
    
    private function esHoraRecreoBloqueada(Hora $hora): bool {
        return $hora === Hora::Cuarta || $hora === Hora::Onceava;
    }
    
    private function generarFranjaRecreoBloqueada(Semana $dia, Hora $hora): array {
        return [
            'curso' => '',
            'dia' => $dia->value,
            'hora' => $hora->codigoHora(),
            'materia' => Materia::RECREO->value,
            'clase' => '',
            'color' => Color::NaranjaClaro->value,
            'tipo' => TipoFranja::Recreo->value
        ];
    }
    
    private function seleccionarMateriaLectiva(array $materiasPorDia): Materia {
        $materiasDisponibles = array_diff_key(Materia::cases(), $materiasPorDia);
        return $materiasDisponibles[array_rand($materiasDisponibles)];
    }
    
    private function asignarColorMateria(Materia $materia): string {
        $coloresMateria = [
            Materia::DSW->value => Color::Rojo->value,
            Materia::DOR->value => Color::Azul->value,
            Materia::DEW->value => Color::Verde->value,
            Materia::PRW->value => Color::Naranja->value,
            Materia::BAE->value => Color::Rosa->value,
            Materia::PRO->value => Color::Amarillo->value,
            
            // Complementarias
            Materia::OTRO->value => Color::Blanco->value,
            Materia::TUTORÍA->value => Color::AzulClaro->value,
            Materia::COTUTORIA->value => Color::Morado->value,
            Materia::GUARDIA->value => Color::VerdeClaro->value,
            Materia::REUNIÓN_DEPARTAMENTO->value => Color::VerdeOscuro->value,
        ];
    
        return $coloresMateria[$materia->value] ?? Color::Blanco->value;
    }
    
    private function generarFranjaComplementaria(Semana $dia, Hora $hora): array {
        $materiasComplementarias = [Materia::TUTORÍA, Materia::COTUTORIA, Materia::GUARDIA, Materia::REUNIÓN_DEPARTAMENTO];
        $materia = $materiasComplementarias[array_rand($materiasComplementarias)];
        
        return [
            'curso' => '',
            'dia' => $dia->value,
            'hora' => $hora->codigoHora(),
            'materia' => $materia->value,
            'clase' => '',
            'color' => $this->asignarColorMateria($materia),
            'tipo' => TipoFranja::Complementaria->value
        ];
    }
    
    private function ajustarHorasRequeridas(array &$horario, int $horasLectivas, int $horasComplementarias, array $horasDisponibles): void {
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
    
    private function guardarHorario(array $horario): void {
        $rutaFichero = 'horarios/horarios.dat';
        $contenido = '';
    
        foreach ($horario as $franja) {
            $color = $franja['color'] instanceof Color ? $franja['color']->value : $franja['color'];
            $contenido .= "{$franja['curso']};{$franja['dia']};{$franja['hora']};{$franja['materia']};{$franja['clase']};{$color};{$franja['tipo']}@";
        }
    
        file_put_contents($rutaFichero, $contenido);
    }
}