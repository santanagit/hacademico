<?php

require_once $_SESSION['diretorio_base'] . '/model/conexaoModel.php';

class atividade_docenteModel {

    private $bd;

    public function __construct() {
        $conexao = new conexaoModel();
        $this->bd = $conexao->getConexao();
    }
    
    public function atividadesNaoComprovadas ($id_pid) {
        $sql = "SELECT 
                    atividade_docente.id_atividade_docente,
                    atividade_docente.id_pid,
                    atividade_docente.id_atividade,
                    atividade_docente.descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante,
                    ultimo_historico_atividade.`id_historico_atividade`,
                    historico_atividade.`situacao`
                FROM 
                    atividade_docente INNER JOIN historico_atividade
                            ON atividade_docente.`id_atividade_docente` = historico_atividade.`id_atividade_docente`
                    INNER JOIN ultimo_historico_atividade
                            ON  ultimo_historico_atividade.`id_historico_atividade` = historico_atividade.`id_historico_atividade`
                WHERE 
                    atividade_docente.id_pid = $id_pid AND
                    historico_atividade.`etapa` = 'RID' AND
                    historico_atividade.`situacao` <> 'CANCELADA' AND
                    historico_atividade.`situacao` <> 'NÃO EXECUTADA' AND
                    atividade_docente.id_comprovante IS NULL
        ";
        
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        
        return mysqli_num_rows($result);
    }    
    
    public function atividadesExecutadas($id_pid) {
        
        $sql = "SELECT 
                    atividade_docente.id_atividade_docente,
                    atividade_docente.id_pid,
                    atividade_docente.id_atividade,
                    atividade_docente.descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante
                FROM 
                    atividade_docente
                WHERE 
                    atividade_docente.id_pid = $id_pid AND
                    atividade_docente.horas_executadas is NULL
                
        ";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        
        return mysqli_num_rows($result);
    }    
    
    public function getAtividadesAssociadas($inicio_periodo, $id_usuario) {
        $sql = "SELECT
                    atividade.descricao AS atividade,
                    comprovante.`descricao` AS comprovante,
                    comprovante.`inicio_vigencia`,
                    comprovante.`fim_vigencia`,
                    comprovante_docente.horas AS horas,
                    comprovante_docente.id_atividade AS id_atividade,
                    comprovante_docente.id_comprovante
                FROM
                    comprovante_docente INNER JOIN comprovante
                            ON comprovante_docente.`id_comprovante` = comprovante.`id_comprovante`
                    INNER JOIN atividade
                            ON comprovante_docente.`id_atividade` = atividade.id_atividade
                WHERE
                    comprovante_docente.`id_usuario` = $id_usuario AND
                    '$inicio_periodo' BETWEEN comprovante.inicio_vigencia AND comprovante.fim_vigencia;";
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function getAtividade_docente($id_atividade_docente) {
        
        $sql = "SELECT 
                    atividade.descricao as atividade,
                    atividade_docente.id_atividade_docente,
                    atividade_docente.id_pid,
                    atividade_docente.id_atividade,
                    atividade_docente.descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante,
                    historico_atividade.id_historico_atividade,
                    historico_atividade.etapa,
                    historico_atividade.situacao,
                    historico_atividade.observacao,
                    DATE_FORMAT(historico_atividade.data_situacao,'%d/%m/%Y %H:%i:%s') AS data_situacao,
                    historico_atividade.id_usuario_avaliador
                FROM 
                    atividade_docente INNER JOIN historico_atividade
                        ON atividade_docente.`id_atividade_docente` = historico_atividade.`id_atividade_docente`
                    INNER JOIN atividade
                        ON atividade_docente.id_atividade = atividade.id_atividade
                WHERE 
                    atividade_docente.id_atividade_docente = $id_atividade_docente
                ORDER BY
                    historico_atividade.id_historico_atividade ASC
        ";
        //echo '<pre>'.$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        
        return $result;
    }

    public function getUltimaAtividadeDdocente($id_atividade_docente) {
        
        $sql = "SELECT 
                    atividade.descricao as atividade,
                    atividade_docente.id_atividade_docente,
                    atividade_docente.id_pid,
                    atividade_docente.id_atividade,
                    atividade_docente.descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante,
                    historico_atividade.id_historico_atividade,
                    historico_atividade.etapa,
                    historico_atividade.situacao,
                    historico_atividade.observacao,
                    DATE_FORMAT(historico_atividade.data_situacao,'%d/%m/%Y %H:%i:%s') AS data_situacao,
                    historico_atividade.id_usuario_avaliador
                FROM 
                    atividade_docente INNER JOIN historico_atividade
                        ON atividade_docente.`id_atividade_docente` = historico_atividade.`id_atividade_docente`
                    INNER JOIN ultimo_historico_atividade
                        ON historico_atividade.id_historico_atividade = ultimo_historico_atividade.id_historico_atividade
                    INNER JOIN atividade
                        ON atividade_docente.id_atividade = atividade.id_atividade
                WHERE 
                    atividade_docente.id_atividade_docente = $id_atividade_docente
                ORDER BY
                    historico_atividade.id_historico_atividade ASC
        ";
        //echo '<pre>'.$sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        
        return $result;
    }
    
    public function listar_atividades_pid($id_pid, $parametros = array(), $ordenacao = array(), $limit = array()) {
        $sql = "SELECT
                    DISTINCT(historico_atividade.etapa) AS etapa,
                    atividade_docente.id_atividade_docente,                    
                    atividade_docente.id_atividade,
                    atividade.id_tipo_atividade,
                    atividade.descricao AS atividade,
                    tipo_atividade.descricao AS tipo_atividade,
                    pid.id_usuario,
                    pid.id_periodo,
                    CONCAT(periodo.ano,'/',periodo.semestre) AS periodo,
                    usuario.nome,
                    atividade_docente.descricao AS descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante
                FROM 
                    atividade_docente INNER JOIN atividade
                        ON atividade_docente.id_atividade = atividade.id_atividade  
                    INNER JOIN pid
                        ON atividade_docente.id_pid = pid.id_pid
                    INNER JOIN usuario    
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo 
                    INNER JOIN tipo_atividade
                        ON atividade.id_tipo_atividade = tipo_atividade.id_tipo_atividade
                    INNER JOIN historico_atividade
                            ON atividade_docente.id_atividade_docente = historico_atividade.id_atividade_docente
                WHERE
                    atividade_docente.id_pid = $id_pid AND
                    etapa = 'PID' 
                ";

        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' AND (';
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
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
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;        
    }
    
    
    public function listar($id_pid, $parametros = array(), $ordenacao = array(), $limit = array()) {

        $sql = "SELECT
                    atividade_docente.id_atividade_docente,                    
                    atividade_docente.id_atividade,
                    atividade.id_tipo_atividade,
                    atividade.descricao AS atividade,
                    tipo_atividade.descricao AS tipo_atividade,
                    pid.id_usuario,
                    pid.id_periodo,
                    CONCAT(periodo.ano,'/',periodo.semestre) as periodo,
                    usuario.nome,
                    atividade_docente.descricao AS descricao,
                    atividade_docente.horas_planejadas,
                    atividade_docente.horas_executadas,
                    atividade_docente.id_comprovante
                FROM 
                    atividade_docente INNER JOIN atividade
                        ON atividade_docente.id_atividade = atividade.id_atividade  
                    INNER JOIN pid
                        ON atividade_docente.id_pid = pid.id_pid
                    INNER JOIN usuario    
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo 
                    INNER JOIN tipo_atividade
                        ON atividade.id_tipo_atividade = tipo_atividade.id_tipo_atividade
                WHERE
                    atividade_docente.id_pid = $id_pid 
                ";

        if (count($parametros) > 0) {
            $i = 0;
            $sql .= ' AND (';
            foreach ($parametros as $key => $value) {
                if ($i > 0)
                    $sql .= " OR ";
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
        //echo $sql;
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();
        return $result;
    }

    public function inserir_atividade_rid($campos) {
        $sql = '';
        $id_atividade_docente = '';

        $this->bd->begin_transaction();
        try {
            if ($campos['id_comprovante'] == '') {
                $campos['id_comprovante'] = 'NULL';
            }
            
            if ($campos['id_tipo_atividade'] < 3) {
                $sql5 = "SELECT DISTINCT(id_comprovante) AS id_comprovante
                        FROM atividade_docente INNER JOIN atividade ON atividade_docente.id_atividade = atividade.id_atividade 
                        WHERE 
                            (atividade.id_tipo_atividade = 1 || atividade.id_tipo_atividade = 2) AND 
                            atividade_docente.id_pid = {$campos['id_pid']}";
                
                $stmt5 = $this->bd->prepare($sql5);
                $stmt5->execute() or die($this->bd->error);
                $result5 = $stmt5->get_result();
                if (mysqli_num_rows($result5) > 0) {         
                    $linha5 = mysqli_fetch_assoc($result5);
                    if ($linha5['id_comprovante'] != '' && $linha5['id_comprovante'] != NULL) {
                        $campos['id_comprovante'] = $linha5['id_comprovante'];                       
                    } 
                }
            }
            $sql = "INSERT INTO
                        atividade_docente(id_pid,id_atividade,descricao,horas_executadas,id_comprovante)
                    VALUES
                    (
                        {$campos['id_pid']},
                        {$campos['id_atividade']},
                        '{$campos['descricao']}',
                        {$campos['horas_executadas']},
                        {$campos['id_comprovante']}
                    )";
            //print_r($campos);
            //echo $sql;
            //die();
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $id_atividade_docente = mysqli_stmt_insert_id($stmt);

            $sql = "INSERT INTO 
                        historico_atividade(id_atividade_docente,etapa,situacao,data_situacao,observacao) 
                    VALUES 
                    (
                        $id_atividade_docente,
                        '{$campos['etapa']}',    
                        'AGUARDANDO AVALIAÇÃO',
                        '" . date('Y-m-d H:i:s') . "',
                        '{$campos['observacao']}'    
                    )";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $id_pid = false;
            throw $exception;
        }
        return $id_atividade_docente;
    }

    public function inserir_atividade_pid($campos) {
        $sql = '';
        $id_atividade_docente = '';

        $this->bd->begin_transaction();
        try {
            if ($campos['id_comprovante'] == '') {
                $campos['id_comprovante'] = 'NULL';
            }
            $sql = "INSERT INTO
                        atividade_docente(id_pid,id_atividade,descricao,horas_planejadas,id_comprovante)
                    VALUES
                    (
                        {$campos['id_pid']},
                        {$campos['id_atividade']},
                        '{$campos['descricao']}',
                        {$campos['horas_planejadas']},
                        {$campos['id_comprovante']}
                    )";
            //echo $sql;
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $id_atividade_docente = mysqli_stmt_insert_id($stmt);

            $sql = "INSERT INTO 
                        historico_atividade(id_atividade_docente,etapa,situacao,data_situacao,observacao) 
                    VALUES 
                    (
                        $id_atividade_docente,
                        '{$campos['etapa']}',    
                        'AGUARDANDO AVALIAÇÃO',
                        '" . date('Y-m-d H:i:s') . "',
                        '{$campos['observacao']}'    
                    )";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            /*
             * Se não tiver ativdiades do grupo 2 no pid, adiciona todas com 
             * horas = 0
             */
            $sql5 = "SELECT 
                        atividade_docente.id_atividade_docente 
                    FROM 
                        atividade_docente INNER JOIN atividade 
                            ON atividade_docente.id_atividade = atividade.id_atividade
                    WHERE 
                        atividade.id_tipo_atividade = 2 AND 
                        atividade_docente.id_pid = {$campos['id_pid']}";
            //echo $sql5;
            $stmt5 = $this->bd->prepare($sql5);
            $stmt5->execute() or die($this->bd->error);
            $result5 = $stmt5->get_result();
            if (mysqli_num_rows($result5) == 0) {

                $sql4 = "SELECT * FROM atividade WHERE id_tipo_atividade = 2";
                $stmt4 = $this->bd->prepare($sql4);
                $stmt4->execute() or die($this->bd->error);
                $result4 = $stmt4->get_result();
                while ($linha4 = mysqli_fetch_assoc($result4)) {
                    $sql2 = "INSERT INTO
                                atividade_docente(id_pid,id_atividade,descricao,horas_planejadas)
                            VALUES
                            (
                                {$campos['id_pid']},
                                {$linha4['id_atividade']},
                                '',
                                0
                            )";
                    $stmt2 = $this->bd->prepare($sql2);
                    $result2 = $stmt2->execute() or die($this->bd->error);
                    $id_atividade_docente2 = mysqli_stmt_insert_id($stmt2);

                    $sql3 = "INSERT INTO 
                                historico_atividade(id_atividade_docente,etapa,situacao,data_situacao,observacao) 
                            VALUES 
                            (
                                $id_atividade_docente2,
                                '{$campos['etapa']}',    
                                'AGUARDANDO AVALIAÇÃO',
                                '" . date('Y-m-d H:i:s') . "',
                                'Atividade inserida pelo sistema'    
                            )";
                    $stmt3 = $this->bd->prepare($sql3);
                    $result3 = $stmt3->execute() or die($this->bd->error);
                }
            }

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            $id_pid = false;
            throw $exception;
        }
        return $id_atividade_docente;
    }

    public function atualizar_atividade_pid($campos) {

        $sql = " 
            UPDATE 
                atividade_docente
            SET
                id_atividade = {$campos['id_atividade']},
                descricao = '{$campos['descricao']}',
                horas_planejadas = {$campos['horas_planejadas']}
            WHERE 
                id_atividade_docente = '{$campos['id_atividade_docente']}'";

        $result = false;
        if ($this->atividade_avaliada($campos['id_atividade_docente'])) {

            $sql5 = "SELECT 
                        * 
                    FROM 
                        historico_atividade 
                    WHERE 
                        id_atividade_docente = {$campos['id_atividade_docente']}
                    ORDER BY
                        id_historico_atividade DESC 
                    ";
            //echo $sql5;
            $stmt5 = $this->bd->prepare($sql5);
            $stmt5->execute() or die($this->bd->error);
            $result5 = $stmt5->get_result();
            $linha5 = mysqli_fetch_assoc($result5);

            if (($linha5['situacao'] == 'APROVADA') || ($linha5['situacao'] == 'REPROVADA')) {

                $this->bd->begin_transaction();
                try {
                    
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $sql = "INSERT INTO 
                                historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao) 
                            VALUES
                            (
                                {$campos['id_atividade_docente']},
                                'PID',
                                'AGUARDANDO AVALIAÇÃO',
                                '{$campos['observacao']}',
                                '" . date("Y-m-d H:i:s") . "' 
                            )
                    ";
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $this->bd->commit();
                } catch (mysqli_sql_exception $exception) {
                    $this->bd->rollback();
                    throw $exception;
                }
                return $result;
            
            } else {

                $this->bd->begin_transaction();
                try {
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $sql5 = "SELECT
                                MAX(id_historico_atividade) AS id_historico_atividade 
                            FROM 
                                historico_atividade 
                            WHERE 
                                id_atividade_docente = {$campos['id_atividade_docente']} AND
                                etapa = 'PID'
                    ";
                    $stmt5 = $this->bd->prepare($sql5);
                    $stmt5->execute() or die($this->bd->error);
                    $result5 = $stmt5->get_result();
                    $linha5 = mysqli_fetch_assoc($result5);                                
                    
                    $sql = "UPDATE
                                historico_atividade
                            SET
                                observacao = '{$campos['observacao']}',
                                data_situacao = '" . date("Y-m-d H:i:s") . "'
                            WHERE
                                id_historico_atividade = {$linha5['id_historico_atividade']}
                    ";
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $this->bd->commit();
                } catch (mysqli_sql_exception $exception) {
                    $this->bd->rollback();
                    throw $exception;
                }
                return $result;
            }
        } else {
            $this->bd->begin_transaction();
            try {
                $stmt = $this->bd->prepare($sql);
                $result = $stmt->execute() or die($this->bd->error);

                $sql5 = "SELECT
                            MAX(id_historico_atividade) AS id_historico_atividade 
                        FROM 
                            historico_atividade 
                        WHERE 
                            id_atividade_docente = {$campos['id_atividade_docente']} AND
                            etapa = 'PID'
                ";
                $stmt5 = $this->bd->prepare($sql5);
                $stmt5->execute() or die($this->bd->error);
                $result5 = $stmt5->get_result();
                $linha5 = mysqli_fetch_assoc($result5);                                

                $sql = "UPDATE
                            historico_atividade
                        SET
                            observacao = '{$campos['observacao']}',
                            data_situacao = '" . date("Y-m-d H:i:s") . "'
                        WHERE
                            id_historico_atividade = {$linha5['id_historico_atividade']}
                ";
                $stmt = $this->bd->prepare($sql);
                $result = $stmt->execute() or die($this->bd->error);

                $this->bd->commit();
            } catch (mysqli_sql_exception $exception) {
                $this->bd->rollback();
                throw $exception;
            }
            return $result;
        }
    }

    public function atualizar_atividade_rid($campos) {

        $sql_atividade = " 
            UPDATE 
                atividade_docente
            SET
                id_atividade = {$campos['id_atividade']},
                descricao = '{$campos['descricao']}',
                horas_executadas = {$campos['horas_executadas']}
            WHERE 
                id_atividade_docente = '{$campos['id_atividade_docente']}'";

        $sql_historico_atividade = "
                INSERT INTO 
                    historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao) 
                VALUES
                (
                    {$campos['id_atividade_docente']},
                    'RID',
                    '{$campos['situacao']}',
                    '{$campos['observacao']}',
                    '" . date("Y-m-d H:i:s") . "' 
                )
        ";
           
        //echo $sql_atividade."\n".$sql_historico_atividade;
                    
        $result = false;
        $this->bd->begin_transaction();
        try {

            $stmt_atividade = $this->bd->prepare($sql_atividade);
            $result_atividade = $stmt_atividade->execute() or die($this->bd->error);

            $stmt_historico_atividade = $this->bd->prepare($sql_historico_atividade);
            $result_historico_atividade = $stmt_historico_atividade->execute() or die($this->bd->error);

            $result = true;
            $this->bd->commit();
            
        } catch (mysqli_sql_exception $exception) {
            
            $this->bd->rollback();
            throw $exception;
            
        }
        return $result;
    }

    public function avaliar_atividade($campos) {
       
        $resultado = false;
        $this->bd->begin_transaction();
        try {

            $sql = " 
                UPDATE 
                    atividade_docente
                SET
                    id_atividade = {$campos['id_atividade']},
                    descricao = '{$campos['descricao']}',
                    horas_planejadas = {$campos['horas_planejadas']}
                WHERE 
                    id_atividade_docente = '{$campos['id_atividade_docente']}'";            
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $resultado = $result;
            
            $sql2 = "INSERT INTO 
                        historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador) 
                    VALUES
                    (
                        {$campos['id_atividade_docente']},
                        '{$campos['etapa']}',
                        '{$campos['situacao']}',
                        '{$campos['observacao']}',
                        '".date("Y-m-d H:i:s")."',
                        {$_SESSION['id_usuario']}
                    )
            ";
            
            $stmt2 = $this->bd->prepare($sql2);
            $result2 = $stmt2->execute() or die($this->bd->error);                
            $resultado = $result2;

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $resultado;
    }

    public function avaliar_atividade_rid($campos) {
       
        $resultado = false;
        $this->bd->begin_transaction();
        try {

            $sql = " 
                UPDATE 
                    atividade_docente
                SET
                    id_atividade = {$campos['id_atividade']},
                    descricao = '{$campos['descricao']}',                
                    horas_executadas = {$campos['horas_executadas']}
                WHERE 
                    id_atividade_docente = '{$campos['id_atividade_docente']}'";            
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            $resultado = $result;
            
            $sql2 = "INSERT INTO 
                        historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao,id_usuario_avaliador) 
                    VALUES
                    (
                        {$campos['id_atividade_docente']},
                        '{$campos['etapa']}',
                        '{$campos['situacao']}',
                        '{$campos['observacao']}',
                        '".date("Y-m-d H:i:s")."',
                        {$_SESSION['id_usuario']}
                    )
            ";
            
            $stmt2 = $this->bd->prepare($sql2);
            $result2 = $stmt2->execute() or die($this->bd->error);                
            $resultado = $result2;

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $resultado;
    }    
    
    public function deletar($id_atividade_docente) {
        $sql = "DELETE FROM atividade_docente WHERE id_atividade_docente = $id_atividade_docente";
        $stmt = $this->bd->prepare($sql);
        $result = $stmt->execute() or die($this->bd->error);
        return $result;
    }

    public function atividade_avaliada($id_atividade_docente) {
        $sql = "SELECT id_atividade_docente FROM historico_atividade WHERE id_atividade_docente = $id_atividade_docente";

        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $stmt->store_result();
        $stmt->num_rows;
        if ($stmt->num_rows > 1) {
            return true;
        } else {
            return false;
        }
    }

    public function atualizar_horas_executadas($campos) {
        $sql = "
                UPDATE 
                    atividade_docente
                SET
                    atividade_docente.horas_executadas = {$campos['horas_executadas']}
                WHERE
                    atividade_docente.id_atividade_docente = {$campos['id_atividade_docente']}
        ";
        $result = null;            
        $this->bd->begin_transaction();
        try {
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            $situacao = '';
            if ($campos['horas_executadas'] == 0) {
                $situacao = 'NÃO EXECUTADA';
            } else {
                $situacao = 'AGUARDANDO AVALIAÇÃO';
            }
            
            $sql = "INSERT INTO 
                        historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao) 
                    VALUES
                    (
                        {$campos['id_atividade_docente']},
                        'RID',
                        '$situacao',
                        'Alteração no valor das horas executadas',
                        '" . date("Y-m-d H:i:s") . "' 
                    )
            ";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $result;
    }

    public function atualizar_chs($campos) {
        $sql = "
                UPDATE 
                    atividade_docente
                SET
                    atividade_docente.horas_planejadas = {$campos['horas_planejadas']}
                WHERE
                    atividade_docente.id_atividade_docente = {$campos['id_atividade_docente']}
        ";

        $result = false;
        if ($this->atividade_avaliada($campos['id_atividade_docente'])) {

            $sql5 = "SELECT 
                        * 
                    FROM 
                        historico_atividade 
                    WHERE 
                        id_atividade_docente = {$campos['id_atividade_docente']}
                    ORDER BY
                        id_historico_atividade DESC 
                    ";
            $stmt5 = $this->bd->prepare($sql5);
            $stmt5->execute() or die($this->bd->error);
            $result5 = $stmt5->get_result();
            $linha5 = mysqli_fetch_assoc($result5);

            if (($linha5['situacao'] == 'APROVADA') || ($linha5['situacao'] == 'REPROVADA')) {

                $this->bd->begin_transaction();
                try {
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $sql = "INSERT INTO 
                                historico_atividade(id_atividade_docente,etapa,situacao,observacao,data_situacao) 
                            VALUES
                            (
                                {$campos['id_atividade_docente']},
                                'PID',
                                'AGUARDANDO AVALIAÇÃO',
                                'Alteração no valor das horas planejadas',
                                '" . date("Y-m-d H:i:s") . "' 
                            )
                    ";
                    $stmt = $this->bd->prepare($sql);
                    $result = $stmt->execute() or die($this->bd->error);

                    $this->bd->commit();
                } catch (mysqli_sql_exception $exception) {
                    $this->bd->rollback();
                    throw $exception;
                }
                return $result;
            } else {
                $stmt = $this->bd->prepare($sql);
                $result = $stmt->execute() or die($this->bd->error);
                return $result;
            }
        } else {
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);
            return $result;
        }
    }
    
    public function deletar_atividade_pid($id_atividade_docente) {
        $sql = '';
        $result = false;

        $this->bd->begin_transaction();
        try {
            $sql = "DELETE FROM historico_atividade WHERE id_atividade_docente = $id_atividade_docente";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            $sql = "DELETE FROM atividade_docente WHERE id_atividade_docente = $id_atividade_docente";
            $stmt = $this->bd->prepare($sql);
            $result = $stmt->execute() or die($this->bd->error);

            $this->bd->commit();
        } catch (mysqli_sql_exception $exception) {
            $this->bd->rollback();
            throw $exception;
        }
        return $result;
    }
    
    public function comprovantesPeriodo($campos) {
        $sql = "SELECT
                    pid.id_periodo,
                    CONCAT(periodo.ano,'-',periodo.semestre) AS periodo,
                    periodo.ano,
                    periodo.semestre,
                    pid.id_pid,
                    usuario.id_usuario,
                    usuario.cor,
                    usuario.nome,
                    ultimo_historico_atividade.id_atividade_docente,
                    atividade.descricao AS tipo_atividade,
                    IF(atividade_docente.descricao = '',atividade.descricao,atividade_docente.descricao) AS atividade,
                    atividade_docente.horas_executadas,
                    comprovante.id_comprovante,
                    CONCAT('comprovante_',comprovante.id_comprovante) AS arquivo
                FROM 
                    pid INNER JOIN historico_pid
                        ON pid.id_pid = historico_pid.`id_pid`
                    INNER JOIN periodo
                        ON pid.id_periodo = periodo.id_periodo
                    INNER JOIN usuario	
                        ON pid.id_usuario = usuario.id_usuario
                    INNER JOIN atividade_docente
                        ON pid.id_pid = atividade_docente.id_pid
                    INNER JOIN atividade
                        ON atividade_docente.id_atividade = atividade.id_atividade
                    INNER JOIN ultimo_historico_atividade
                        ON atividade_docente.id_atividade_docente = ultimo_historico_atividade.id_atividade_docente
                    INNER JOIN historico_atividade
                        ON ultimo_historico_atividade.id_historico_atividade = historico_atividade.`id_historico_atividade`
                    INNER JOIN comprovante
                        ON atividade_docente.id_comprovante = comprovante.id_comprovante		
                WHERE
                    historico_pid.etapa = 'RID' AND
                    historico_pid.situacao = 'APROVADO' AND
                    historico_atividade.`situacao` = 'APROVADA' AND
                    historico_atividade.`etapa` = 'RID' AND
                    pid.id_periodo = {$campos['id_periodo']}
               "; 
        if (trim($campos['id_usuario']) != '') {
            $sql .= " AND usuario.id_usuario = {$campos['id_usuario']}";
        }
        $sql .= " ORDER BY usuario.nome";
  
        $stmt = $this->bd->prepare($sql);
        $stmt->execute() or die($this->bd->error);
        $result = $stmt->get_result();      
        return $result;
    }

}
