<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class historico_atividadeModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function inserir($campos) {
        
        if ($campos['id_usuario_avaliador'] == '') {
            $campos['id_usuario_avaliador'] = 'NULL';
        }
        
        $sql = "INSERT INTO 
                    historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador) 
                VALUES
                (
                    {$campos['id_atividade_docente']},
                    '{$campos['etapa']}',
                    '{$campos['situacao']}',
                    '{$campos['observacao']}',
                    '".date("Y-m-d H:i:s")."',
                    {$campos['id_usuario_avaliador']} 
                )
        ";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function getSituacaoAtividade($id_atividade_docente, $etapa) {
        $sql = "SELECT 
                    MAX(id_historico_atividade) as id_historico_atividade,
                    COUNT(id_historico_atividade) as quantidade
                FROM 
                    historico_atividade
                WHERE 
                    historico_atividade.id_atividade_docente = $id_atividade_docente AND 
                    etapa = '$etapa'
                GROUP BY
                    historico_atividade.id_atividade_docente
                ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        
        if (mysqli_num_rows($result) > 0) {
            $linha = mysqli_fetch_assoc($result);
            $sql2 = "SELECT 
                    historico_atividade.situacao,
                    historico_atividade.data_situacao
                FROM 
                    historico_atividade
                WHERE 
                    historico_atividade.id_historico_atividade = {$linha['id_historico_atividade']}";

            $stmt2 = $this->bd->prepare($sql2);
            $stmt2->execute() or die($this->bd->error);
            $result2 = $stmt2->get_result();

            return $result2;
        } else {
            return false;
        }
    }

    public function listar($id_atividade_docente, $etapa, $parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "
                SELECT
                    tipo_atividade.descrica AS tipo_atividade,
                    atividade.descricao AS atividade,
                    atividade_docente.descricao AS atividade_docente,
                    historico_atividade.etapa,
                    historico_atividade.situacao,
                    historico_atividade.data_situacao,
                    historico_atividade.observacao,
                    historico_atividade.id_usuario,
                    usuario.nome
                FROM 
                    historico_atividade INNER JOIN atividade_docente
                        ON historico_atividade.id_atividade_docente = atividade_docente.id_atividade_docente
                    INNER JOIN atividade 
                        ON atividade_docente.id_atividade = atividade.id_atividade
                    INNER JOIN tipo_atividade
                        ON atividade.id_tipo_atividade = tipo_atividade.id_tipo_atividade
                WHERE
                    historico_atividade.etapa = '{$etapa}' AND
                    historico_atividade.id_atividade_docente = $id_atividade_docente
        ";

        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' AND (';
            foreach ($ordenacao as $key => $value) {
                if ($i > 0)
                    $sql .= "OR ";
                $sql .= "($key = $value)";
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

        if (count($limit) > 0) {
            $sql .= " LIMIT {$limit['inicio']},{$limit['quantidade']}";
        }
        //echo "<pre>".$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

}
