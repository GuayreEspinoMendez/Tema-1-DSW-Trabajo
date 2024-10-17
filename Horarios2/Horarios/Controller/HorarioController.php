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

        $franjaHorario = new FranjaHorario(
            Curso::from($_POST['curso']),
            Clase::from($_POST['clase']),
            Materia::from($_POST['materia']),
            $_POST['dia'],
            $_POST['hora'],
            TipoFranja::from($_POST['tipoFranja']),
            Color::from($_POST['color'])
        );

        $this->gestorHorario->insertarHora($franjaHorario);
    }

    private function eliminarHora() {
        Validaciones::validarDia($_POST['dia']);
        Validaciones::validarHora($_POST['hora']);
    
        // Creamos una instancia de FranjaHorario solo con los datos necesarios para eliminar
        $franjaHorario = new FranjaHorario(
            Curso::from('1ADAW'),  // Usamos un valor predeterminado, ya que no es relevante para la eliminación
            Clase::from('R01'),    // Usamos un valor predeterminado, ya que no es relevante para la eliminación
            Materia::from('OT'),   // Usamos un valor predeterminado, ya que no es relevante para la eliminación
            $_POST['dia'],
            $_POST['hora'],
            TipoFranja::from('L'), // Usamos un valor predeterminado, ya que no es relevante para la eliminación
            Color::from('#ffffff') // Usamos un valor predeterminado, ya que no es relevante para la eliminación
        );
    
        $this->gestorHorario->eliminarHora($franjaHorario);
    }
    private function cargarHorario() {
        Validaciones::validarFicheroSubido($_FILES['horarioFile']);
        $this->gestorHorario->subirFichero($_FILES['horarioFile']['tmp_name']);
    }

    private function generarHorario() {
        Validaciones::validarTipoHorario($_POST['tipoHorario']);
        $this->gestorHorario->generarHorario($_POST['tipoHorario']);
    }

    public function obtenerHorario() {
        return $this->gestorHorario->mostrarHorario();
    }
}