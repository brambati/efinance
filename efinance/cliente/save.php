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

        $id         = $data->id; 
        $nome       = $data->nome; 
        $email      = $data->email;
        $telefone   = $data->telefone;
        $cidade     = $data->cidade;


        if(strlen($nome) <= 0) {
           echo json_encode([
                'status' => 'error',
                'message' => 'O campo nome deve ser preenchido'
            ]);
           return;
        }

        if(!strpos($email, '@') || !strpos($email, '.com')) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Campo e-mail inválido'
            ]);
           return;
        }
  
        if(strlen(preg_replace('/[^0-9]/', '', $telefone)) < 11) {
           echo json_encode([
                'status' => 'error',
                'message' => 'Você precisa informar o telefone do cliente'
            ]);
           return;
        }       

        if(empty($cidade)) {
          echo json_encode([
                'status' => 'error',
                'message' => 'O campo cidade deve ser preenchido' 
            ]);
          return;
        }


        if($id == 0) {
            $query = $conn->dbCon()->prepare("INSERT INTO clientes (nome, email, telefone, idCidade) values (?, ?, ?, ?)");
            $query->bindParam(1, $nome);
            $query->bindParam(2, $email);
            $query->bindParam(3, $telefone);
            $query->bindParam(4, $cidade);
        } else {
            $query = $conn->dbCon()->prepare("UPDATE clientes SET nome = ?, email = ?, telefone = ?, idCidade = ? WHERE id = ?");
            $query->bindParam(1, $nome);
            $query->bindParam(2, $email);
            $query->bindParam(3, $telefone);
            $query->bindParam(4, $cidade);
            $query->bindParam(5, $id);
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
