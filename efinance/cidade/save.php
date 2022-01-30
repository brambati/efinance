<?php

    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');
    header('Access-Control-Allow-Methods: POST');
    header("Access-Control-Max-Age: 3600");
    header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

    include_once '../conn/ConexaoMysql.php';

    $conn = new ConexaoMysql();

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        $data = json_decode( file_get_contents('php://input') );

        $id     = $data->id;
        $cidade = $data->cidade;
        $estado = $data->estado;

  
        if( strlen($cidade) < 0 ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O campo cidade deve ser preenchido'
            ]);
          return;
        }

        if( !$estado ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O campo estado deve ser preenchido'
            ]);
          return;
        }

        if($id == 0) {
            $query = $conn->dbCon()->prepare("INSERT INTO cidade (cidade, idUF) values (?, ?)");
            $query->bindParam(1, $cidade);
            $query->bindParam(2, $estado);
        } else {
            $query = $conn->dbCon()->prepare("UPDATE cidade SET cidade = ?, idUF = ? WHERE id = ?");
            $query->bindParam(1, $cidade);
            $query->bindParam(2, $estado);
            $query->bindParam(3, $id);
        }

        if( $query->execute() ) {

            echo json_encode([
                'status' => 'success',
                'message' => 'Cadastro realizado com sucesso'
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
