<?php
//die('Em manutencao');

ini_set('display_errors', 1);
require_once 'model/usuarioModel.php';
require_once 'model/log_acaoModel.php';

session_start();
session_unset();
session_destroy();
session_abort();

session_start();

$msg = '';
if (isset($_GET['erro'])) {
    if ($_GET['erro'] == 1) {
        $msg = '<div class="alert alert-danger">Somente professores do IF Sudeste MG campus Avançado Bom Sucesso podem acessar o sistema!</div>';
    } else if ($_GET['erro'] == 2) {
        $msg = '<div class="alert alert-danger">Usuário não autenticado!</div>';
    } else if ($_GET['erro'] == 3) {
        $msg = '<div class="alert alert-danger">Perfil do usuário não definido!</div>';
    } else if ($_GET['erro'] == 4) {
        $msg = '<div class="alert alert-danger">Sem Permissão de Acesso!</div>';
    }
}

if (isset($_POST['usuario'])) {
    $usuarioM = new usuarioModel();
    $result = $usuarioM->getUsuarioSenha($_POST);
    if (mysqli_num_rows($result) > 0) {
        $linha_usuario = mysqli_fetch_assoc($result);
        $_SESSION['ativo'] = true;
        $_SESSION['perfil'] = $linha_usuario['perfil'];
        $_SESSION['id_usuario'] = $linha_usuario['id_usuario'];
        $_SESSION['matricula'] = $linha_usuario['matricula'];
        $_SESSION['nome'] = $linha_usuario['nome'];
        $_SESSION['email'] = $linha_usuario['email'];
        $_SESSION['diretorio_base'] = realpath('./');
        $_SESSION['diretorio_base'] = str_replace('\\', '/', realpath('./'));
        if (($_SESSION['perfil'] == 'Coordenador de Ensino') || ($_SESSION['perfil'] == 'Coordenador de Curso')) {
            $_SESSION['topo'] = 'topo_ensino.php';
        } else if ($_SESSION['perfil'] == 'Registro Escolar') {
            $_SESSION['topo'] = 'topo_registro_escolar.php';
        } else if ($_SESSION['perfil'] == 'NAP') {
            $_SESSION['topo'] = 'topo_nap.php';
        } else if ($_SESSION['perfil'] == 'Aluno') {
            $_SESSION['topo'] = 'topo_aluno.php';              
        } else {
            $_SESSION['topo'] = 'topo_professor.php';
        }
        $logM = new log_acaoModel();
        $logM->inserir(array('id_usuario'=>$_SESSION['id_usuario'],'acao'=>'Login','data_hora'=>date('Y-m-d H:i:s')));
        
        header('Location: principal.php');
    } else {
        $msg = '<div class="alert alert-danger">Usuário ou senha inválidos!</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
    <head>    
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Horário Acadêmico">
        <meta name="author" content="Antonio Rafael Santana">

        <title>hAcademico</title>

        <!-- Bootstrap CSS file -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">

        <!-- Jquery and Bootstrap Script files -->
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>

    </head>
    <body>
        <div class="container">
            <div class="row" style="margin-top:60px">
                <div class="col-md-4">&nbsp;</div>
                <div class="col-md-4">
                    <center>
                        <div class="panel">
                            <div class="panel-heading bg-info">Autenticação</div>
                            <div class="panel-body" style="text-align: left">
                                <form action="index.php" method="post">
                                    <div class="form-group">
                                        <div id="msg"><?= $msg ?></div>
                                        <label>Email</label>
                                        <input type="text" class="form-control" id="usuario" name="usuario" required="">
                                        <label>Senha</label>
                                        <input type="password" class="form-control" id="senha" name="senha" required="">
                                        <input type="submit" value="Entrar" class="btn btn-success" style="margin-top: 10px">
                                    </div>
                                </form>
                            </div>
                        </div>
                    </center>
                </div>
                <div class="col-md-4">&nbsp;</div>
            </div>
        </div>
    </body>
</html>