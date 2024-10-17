<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <title>Horario Docente</title>
</head>

<?php
require_once 'Controller/HorarioController.php';
require_once 'Model/Campos.php';

$horarioController = new HorarioController();
?>

<body>
    <div class="container">
        <h1 class="text-center">Horario de Clases</h1>

        <div class="row">
            <div class="container col-md-8">
                <?php
                // Mostrar cuadro horario
                echo $horarioController->obtenerHorario();
                ?>
            </div>
            <br>
            <br>

            <div class="container col-md-4">
                <h5>Operaciones Horario:</h5>

                <form action="Controller/HorarioController.php" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-6">
                            <label>Curso:</label>
                            <select class="form-select" id="curso" name="curso">
                                <option value=''></option>
                                <?php
                                foreach (Curso::cases() as $curso) {
                                    echo "<option value='{$curso->value}'>{$curso->value}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Día:</label>
                            <select class="form-select" id="dia" name="dia">
                                <option value=''></option>
                                <?php
                                foreach (Semana::cases() as $dia) {
                                    echo "<option value='{$dia->value}'>{$dia->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>Tipo:</label>
                            <select class="form-select" id="tipoFranja" name="tipoFranja">
                                <option value=''></option>
                                <?php
                                foreach (TipoFranja::cases() as $tipo) {
                                    echo "<option value='{$tipo->value}'>{$tipo->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Hora:</label>
                            <select class="form-select" id="hora" name="hora">
                                <option value=''></option>
                                <?php
                                foreach (Hora::cases() as $hora) {
                                    echo "<option value='{$hora->codigoHora()}'>{$hora->value}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>Materia:</label>
                            <select class="form-select" id="materia" name="materia">
                                <option value=''></option>
                                <?php
                                foreach (Materia::cases() as $materia) {
                                    echo "<option value='{$materia->value}'>{$materia->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary" name="action" value="insertarHora">Insertar Hora</button>
                            <button type="submit" class="btn btn-danger" name="action" value="eliminarHora">Eliminar Hora</button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <label>Tipo Horario:</label>
                            <select class="form-select" id="tipoHorario" name="tipoHorario">
                                <option value=''></option>
                                <?php
                                foreach (TiposHorarios::cases() as $tipoHorario) {
                                    echo "<option value='{$tipoHorario->value}'>{$tipoHorario->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6 d-flex align-items-end">
                            <button type="submit" class="btn btn-info" name="action" value="generarHorario">Generar Horario</button>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-6">
                            <input type="file" name="horarioFile" id="horarioFile">
                        </div>
                        <div class="col-6">
                            <button type="submit" class="btn btn-warning" name="action" value="cargarHorario">Cargar Horario</button>
                        </div>
                    </div>

                    <!-- Agregamos la sección de búsqueda de horarios -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Búsqueda de Horarios:</h5>
                            <label>Curso:</label>
                            <select class="form-select" id="cursoBuscar" name="cursoBuscar">
                                <?php
                                foreach (Curso::cases() as $curso) {
                                    echo "<option value='{$curso->value}'>{$curso->value}</option>";
                                }
                                ?>
                            </select>

                            <label>Día:</label>
                            <select class="form-select" id="diaBuscar" name="diaBuscar">
                                <?php
                                foreach (Semana::cases() as $dia) {
                                    echo "<option value='{$dia->value}'>{$dia->name}</option>";
                                }
                                ?>
                            </select>

                            <button type="submit" class="btn btn-success" name="action" value="buscarHorario">Buscar Horario</button>
                        </div>
                    </div>

                    <!-- Agregamos la sección de estadísticas de horarios -->
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5>Estadísticas de Horarios:</h5>
                            <button type="submit" class="btn btn-secondary" name="action" value="estadisticasHorarios">Ver Estadísticas</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-OERcA2EqjJCMA+/3y+gxIOqMEjwtxJY7tBDT86QzKL3PwOPB5QVchkHQDYWNEQtU"
        crossorigin="anonymous"></script>
</body>

</html>