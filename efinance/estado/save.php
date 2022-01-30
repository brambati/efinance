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

        $uf     = $data->uf;
        $sigla  = $data->sigla;
        $id     = $data->id;

  
        if( strlen($uf) < 0 ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O campo estado deve ser preenchido'
            ]);
          return;
        }

        if( strlen($sigla) < 2 || strlen($sigla) > 2 ) {
            echo json_encode([
                'status' => 'error',
                'message' => 'O campo sigle deve conter 2 caracteres'
            ]);
          return;
        }

        if($id == 0) {
            $query = $conn->dbCon()->prepare("INSERT INTO estado (uf, sigla) values (?, ?)");
            $query->bindParam(1, $uf);
            $query->bindParam(2, $sigla);
        } else {
            $query = $conn->dbCon()->prepare("UPDATE estado SET uf = ?, sigla = ? WHERE id = ?");
            $query->bindParam(1, $uf);
            $query->bindParam(2, $sigla);
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
