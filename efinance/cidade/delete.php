<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET');
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once '../conn/ConexaoMysql.php';

    $conn = new ConexaoMysql();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {

        $id = $_GET['id'];

        if(!$id) {

            echo json_encode([
                'status' => 'error',
                'message' => 'ID do estado não enviado'
            ]);
            return;
        }

        $query = $conn->dbCon()->prepare("DELETE FROM cidade WHERE id = $id");
        if( $query->execute() ) {

            echo json_encode([
                'status' => 'success',
                'message' => 'Registro deletado com sucesso'
            ]);

        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Houve um erro ao inserir os dados, tente novamente'
            ]);
        }

    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Tipo de requisição invalida'
        ]);
    }
