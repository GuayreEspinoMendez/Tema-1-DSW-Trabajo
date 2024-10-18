<?php
require_once '../Model/GestorHorario.php';
require_once '../Model/FranjaHorario.php';
require_once '../Model/Campos.php';
require_once '../Model/Validaciones.php';

class HorarioController {
    private $gestorHorario;

    function __construct() {
        $this->gestorHorario = new GestorHorario();

        try {
            if (isset($_POST['action'])) {
                switch ($_POST['action']) {
                    case 'insertarHora':
                        $this->insertarHora();
                        break;
                    case 'eliminarHora':
                        $this->eliminarHora();
                        break;
                    case 'cargarHorario':
                        $this->cargarHorario();
                        break;
                    case 'generarHorario':
                        $this->generarHorario();
                        break;
                }
            }
        } catch (Exception $e) {
            echo '<p style="color:red">Excepción: ', $e->getMessage(), "</p><br>";
        }
    }

    private function insertarHora() {
        Validaciones::validarCurso($_POST['curso']);
        Validaciones::validarDia($_POST['dia']);
        Validaciones::validarTipoFranja($_POST['tipoFranja']);
        Validaciones::validarHora($_POST['hora']);
        Validaciones::validarMateria($_POST['materia']);
    
        // Verifica si 'clase' está establecido en $_POST, si no, proporciona un valor predeterminado
        $clase = isset($_POST['clase']) ? $_POST['clase'] : 'R01';
    
        $materia = Materia::from($_POST['materia']);
        $color = $this->asignarColorMateria($materia);
    
        $franjaHorario = new FranjaHorario(
            Curso::from($_POST['curso']),
            Clase::from($clase),
            $materia,
            $_POST['dia'],
            $_POST['hora'],
            TipoFranja::from($_POST['tipoFranja']),
            $color  // Ahora pasamos directamente el objeto Color
        );
    
        $this->gestorHorario->insertarHora($franjaHorario);
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=Hora insertada correctamente");
        exit();
    }
    
    private function asignarColorMateria(Materia $materia): Color {
        $coloresMateria = [
            // Materias
            'DSW' => Color::Rojo,
            'DOR' => Color::Azul,
            'DEW' => Color::Verde,
            'PRW' => Color::Naranja,
            'BAE' => Color::Rosa,
            'PRO' => Color::Amarillo,
            
            // Complementarias
            'OTRO' => Color::Blanco,
            'TUTORÍA' => Color::AzulClaro,
            'COTUTORIA' => Color::Morado,
            'GUARDIA' => Color::VerdeClaro,
            'REUNIÓN_DEPARTAMENTO' => Color::VerdeOscuro,
            'RECREO' => Color::NaranjaClaro
        ];
    
        return $coloresMateria[$materia->value] ?? Color::Blanco;
    }

    private function eliminarHora() {
        Validaciones::validarDia($_POST['dia']);
        Validaciones::validarHora($_POST['hora']);
    
        echo "Día seleccionado: " . $_POST['dia'] . "<br>";
        echo "Hora seleccionada: " . $_POST['hora'] . "<br>";
    
        $franjaHorario = new FranjaHorario(
            null,
            null,
            Materia::from('OT'),
            $_POST['dia'],
            $_POST['hora'],
            TipoFranja::from('L'),
            Color::from('#ffffff')
        );
    
        try {
            $eliminado = $this->gestorHorario->eliminarHora($franjaHorario);
            if ($eliminado) {
                $_SESSION['message'] = "Hora eliminada correctamente";
            } else {
                $_SESSION['error'] = "No se pudo eliminar la hora seleccionada";
            }
        } catch (Exception $e) {
            $_SESSION['error'] = $e->getMessage();
        }
    
        
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    private function cargarHorario() {
        Validaciones::validarFicheroSubido($_FILES['horarioFile']);
        $this->gestorHorario->subirFichero($_FILES['horarioFile']['tmp_name']);
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=Horario cargado correctamente");
        exit();
    }

    private function generarHorario() {
        Validaciones::validarTipoHorario($_POST['tipoHorario']);
        $tipoHorario = TiposHorarios::from($_POST['tipoHorario']);
        $mensaje = $this->gestorHorario->generarHorario($tipoHorario);
        header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($mensaje));
        exit();
    }



    public function obtenerHorario() {
        return $this->gestorHorario->mostrarHorario();
    }


}