<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class comprovanteModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }

    public function getArquivo($id_comprovante) {
        $sql = "SELECT  
                    arquivo
                FROM 
                    comprovante
                WHERE 
                     id_comprovante = $id_comprovante";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getComprovante($id_comprovante) {
        $sql = "SELECT 
                    id_comprovante,
                    descricao,
                    DATE_FORMAT(inicio_vigencia,'%d/%m/%Y') AS inicio_vigencia,
                    DATE_FORMAT(fim_vigencia,'%d/%m/%Y') AS fim_vigencia 
                FROM 
                    comprovante WHERE id_comprovante = ?";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("i", $id_comprovante);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getComprovanteDisciplinas($id_pid) {
        
        $sql = "SELECT 
                    comprovante.id_comprovante AS id_comprovante, 
                    comprovante.descricao AS descricao
                FROM 
                    atividade_docente INNER JOIN atividade 
                            ON atividade_docente.id_atividade = atividade.id_atividade 
                    INNER JOIN comprovante
                            ON atividade_docente.`id_comprovante` = comprovante.`id_comprovante`
                WHERE 
                    (atividade.id_tipo_atividade = 1 || atividade.id_tipo_atividade = 2) AND 
                    atividade_docente.id_pid = $id_pid
                GROUP BY	
                    comprovante.id_comprovante,comprovante.descricao";
        //echo '<pre>'.$sql.'</pre>';
        
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }
    
    public function getComprovanteAtividade($id_atividade_docente) {
        $sql = "SELECT 
                    atividade_docente.id_comprovante,
                    comprovante.descricao AS descricao
                FROM 
                    atividade_docente INNER JOIN comprovante
                        ON atividade_docente.`id_comprovante` = comprovante.`id_comprovante`
                WHERE 
                    atividade_docente.id_atividade_docente = $id_atividade_docente";
                
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }    

    public function listar($criterios = array(), $parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT
                    DISTINCT(comprovante.id_comprovante),
                    descricao,
                    DATE_FORMAT(inicio_vigencia,'%d/%m/%Y') AS inicio_vigencia,
                    DATE_FORMAT(fim_vigencia,'%d/%m/%Y') AS fim_vigencia
                FROM comprovante";

        if (count($criterios) > 0) {
            if (isset($criterios['grupo'])) {
                $sql .= '   
                    INNER JOIN comprovante_docente 
                        ON comprovante.id_comprovante = comprovante_docente.id_comprovante ';
            }
            if (isset($criterios['vigencia'])) {
                $sql .= ' 
                WHERE (inicio_vigencia is not null)';
            }
        }
        
        
        if (count($parametros) > 0) {
            
            if (!isset($criterios['vigencia'])) { 
                $sql .= ' WHERE ';
            } else {
                $sql .= ' AND (';
            }
            $i = 0;
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
                $sql .= "$key like '%$value%'";
                $i++;
            }
            if (isset($criterios['vigencia'])) { 
                $sql .= ' )';
            }
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
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir($campos) {

        $id_comprovante = 0;
        
        if ($campos['descricao'] == '') {
            $campos['descricao'] = $_FILES['arquivo']['name'];
        }
        
        $sql = "INSERT INTO 
                comprovante(descricao,inicio_vigencia,fim_vigencia) 
                VALUES (?,?,?)";
        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("sss",
                $campos['descricao'],
                $campos['inicio_vigencia'],
                $campos['fim_vigencia']
        );
        $result = $stmt->execute() or die($this->bd->error);
        if (!$result) {
            return false;
        } else {
            $id_comprovante = mysqli_insert_id($this->bd);
            $arquivo_temp = $_FILES['arquivo']['tmp_name'];
            if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$id_comprovante.'.pdf')) {
                return mysqli_insert_id($this->bd);
            } else {
                return false;
            }            
        }
    }

    public function inserirComprovanteDisciplinas($campos) {

        $id_comprovante = 0;
        
        $this->bd->begin_transaction();
        try {

            if ($campos['descricao'] == '') {
                $campos['descricao'] = $_FILES['arquivo']['name'];
            }

            $sql = "INSERT INTO 
                    comprovante(descricao) 
                    VALUES (?)";
            $stmt = $this->bd->prepare($sql);
            $stmt->bind_param("s",$campos['descricao']);
            $result = $stmt->execute() or die($this->bd->error);

            $id_comprovante = mysqli_insert_id($this->bd);

            $sql = "UPDATE atividade_docente INNER JOIN atividade ON atividade_docente.id_atividade = atividade.id_atividade 
                    SET id_comprovante = $id_comprovante
                    WHERE 
                        (atividade.id_tipo_atividade = 1 || atividade.id_tipo_atividade = 2) AND 
                        atividade_docente.id_pid = {$campos['id_pid']}";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);            
            
            if ($id_comprovante > 0) {
                $arquivo_temp = $_FILES['arquivo']['tmp_name'];
                move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$id_comprovante.'.pdf');
            }            
            
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $id_comprovante = 0;
            throw $exception;
        }

        return $id_comprovante;
    }
    
    public function inserirComprovanteAtividade($campos) {

        $id_comprovante = 0;
        
        $this->bd->begin_transaction();
        try {

            if ($campos['descricao'] == '') {
                $campos['descricao'] = $_FILES['arquivo']['name'];
            }

            $sql = "INSERT INTO 
                    comprovante(descricao) 
                    VALUES (?)";
            $stmt = $this->bd->prepare($sql);
            $stmt->bind_param("s",$campos['descricao']);
            $result = $stmt->execute() or die($this->bd->error);

            $id_comprovante = mysqli_insert_id($this->bd);

            $sql = "UPDATE atividade_docente 
                    SET id_comprovante = $id_comprovante
                    WHERE 
                        atividade_docente.id_atividade_docente = {$campos['id_atividade_docente']}";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);            
            
            if ($id_comprovante > 0) {
                $arquivo_temp = $_FILES['arquivo']['tmp_name'];
                move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$id_comprovante.'.pdf');
            }            
            
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $id_comprovante = 0;
            throw $exception;
        }

        return $id_comprovante;
    }
    
    public function atualizarComprovanteAtividade($campos) {

        $result = false;
        $this->bd->begin_transaction();
        try {        
            $sql = "UPDATE comprovante SET 
                        descricao = ? 
                    WHERE 
                        id_comprovante = ?";

            $stmt = $this->bd->prepare($sql);
            $stmt->bind_param("si",
                    $campos['descricao'],
                    $campos['id_comprovante']
            );
            $result = $stmt->execute() or die($this->bd->error);

            if (isset($campos['situacao'])) {
                if ($campos['situacao'] == 'REPROVADA') {
                   
                    $campos['id_usuario_avaliador'] = 'NULL';

                    $sql = "INSERT INTO 
                                historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador) 
                            VALUES
                            (
                                {$campos['id_atividade_docente']},
                                '{$campos['etapa']}',
                                'AGUARDANDO AVALIAÇÃO',
                                'Comprovante atualizado',
                                '".date("Y-m-d H:i:s")."',
                                {$campos['id_usuario_avaliador']} 
                            )
                    ";
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);
                }
            }
            
            unlink($_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf');
            $arquivo_temp = $_FILES['arquivo']['tmp_name'];
            move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf');
            
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $result;
    }
    
    public function atualizarComprovanteDisciplinas($campos) {

        $result = false;
        $this->bd->begin_transaction();
        try {        
            $sql = "UPDATE comprovante SET 
                        descricao = ? 
                    WHERE 
                        id_comprovante = ?";

            $stmt = $this->bd->prepare($sql);
            $stmt->bind_param("si",
                    $campos['descricao'],
                    $campos['id_comprovante']
            );
            $result = $stmt->execute() or die($this->bd->error);

            unlink($_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf');
            $arquivo_temp = $_FILES['arquivo']['tmp_name'];
            move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf');
            
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $result;
    }
    
    public function atualizar($campos) {

        $sql = "UPDATE comprovante SET 
                    descricao = ?, 
                    inicio_vigencia = ?, 
                    fim_vigencia = ?
                WHERE 
                    id_comprovante = ?";

        $stmt = $this->bd->prepare($sql);
        $stmt->bind_param("sssi",
                $campos['descricao'],
                $campos['inicio_vigencia'],
                $campos['fim_vigencia'],
                $campos['id_comprovante']
        );
        $result = $stmt->execute() or die($this->bd->error);

        unlink($_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf');
        $arquivo_temp = $_FILES['arquivo']['tmp_name'];
        if (move_uploaded_file($_FILES["arquivo"]["tmp_name"], $_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$campos['id_comprovante'].'.pdf')) {
            return $result;
        } else {
            return false;
        }         
       
    }

    public function deletar($id_comprovante) {
        $sql = "DELETE FROM comprovante WHERE id_comprovante = $id_comprovante";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        unlink($_SESSION['diretorio_base'] . '/comprovantes/comprovante_'.$id_comprovante.'.pdf');
        return $result;
    }

    public function existeVinculo($id_comprovante) {
        $sql = "SELECT id_atividade_docente FROM atividade_docente WHERE id_comprovante = $id_comprovante UNION ";
        $sql .= "SELECT id_comprovante_docente FROM comprovante_docente WHERE id_comprovante = $id_comprovante";
        $stmt = $this->bd->prepare($sql);
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
