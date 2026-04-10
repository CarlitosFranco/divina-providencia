<?php
namespace Controladores;

use Modelos\Rol;

class RolControlador {
    private $rolModel;

    public function __construct() {
        $this->rolModel = new Rol();
    }

    public function listar() {
        $roles = $this->rolModel->obtenerTodos();
        echo json_encode($roles);
    }
}