<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: GET');
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once '../conn/ConexaoMysql.php';

    $conn = new ConexaoMysql();

    if ($_SERVER['REQUEST_METHOD'] == 'GET') {

        $data = json_decode( file_get_contents('php://input') );

        $query = $conn->dbCon()->prepare("SELECT c.id AS idCidade, cidade, c.idUF, e.uf FROM cidade c INNER JOIN estado e ON (c.idUF = e.id)");
        if( $query->execute() ) {

            $query = $query->fetchAll(PDO::FETCH_CLASS);

            $dataReturn = [];

            foreach ($query as $key => $value) {
                array_push($dataReturn, [
                    'idCidade'  => $value->idCidade,
                    'cidade'    => $value->cidade,
                    'idUF'      => $value->idUF,
                    'estado'    => $value->uf
                ]);
            }
           

            echo json_encode([
                'status' => 'success',
                'list' => $dataReturn
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
