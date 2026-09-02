<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class comprovante_docenteModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function listar($id_comprovante,$parametros = array(), $ordenacao = array()) {

        $sql = "SELECT 
                    comprovante_docente.id_comprovante_docente,
                    comprovante_docente.id_atividade,
                    comprovante_docente.id_usuario,
                    usuario.nome AS professor,
                    atividade.descricao AS atividade,
                    comprovante_docente.horas
                FROM 
                    comprovante_docente INNER JOIN atividade
                        ON comprovante_docente.id_atividade = atividade.id_atividade
                    INNER JOIN usuario
                        ON comprovante_docente.id_usuario = usuario.id_usuario
                WHERE
                    id_comprovante = $id_comprovante

        ";
        if (count($parametros) > 0) {
            $i = 0;
            $sql .= 'AND (';
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
                $sql .= "($key like '%$value%')";
                $i++;
            }
            $sql .= ')';
        }

        if (count($ordenacao) > 0) {
            $i = 0;
            $sql .= ' ORDER BY ';
            foreach ($ordenacao as $key => $value) {
                if ($i > 0)
                    $sql .= ", ";
                $sql .= "$key $value";
                $i++;
            }
        }

        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {
        $sql = "INSERT INTO "
            . "comprovante_docente(id_comprovante,id_atividade,id_usuario,horas) "
            . "VALUES "
            . "("
            . "{$campos['id_comprovante']},"                
            . "{$campos['id_atividade']},"
            . "{$campos['id_usuario']},"
            . "{$campos['horas']}"
            . ")";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            return mysqli_stmt_insert_id($stmt);
        }
    }
    
    public function deletar($id_comprovante_docente) {
        $sql = "DELETE FROM comprovante_docente WHERE id_comprovante_docente = $id_comprovante_docente";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function existeAssociacao($id_atividade, $id_usuario) {
        $sql = "SELECT id_comprovante_docente FROM comprovante_docente WHERE id_atividade = ? AND id_usuario = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("ii", $descricao, $id_disciplina);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 0) {
            return true;
        } else {
            return false;
        }
    }

}