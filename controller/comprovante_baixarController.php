<?php

session_start();

require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/usuarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/atividade_docenteModel.php';
require_once $_SESSION['diretorio_base'] . '/model/log_acaoModel.php';
require_once $_SESSION['diretorio_base'] . '/controller/xlsxwriter.class.php';

class comprovante_baixarController {

    private $msg;

    public function __construct() {
        $this->msg = '';
    }
    
    public function carregarPeriodo() {
        $select = '<label for="id_periodo">Periodo:</label>';
        $select .= '<select id="id_periodo" name="id_periodo" class="form-control" style="width:100%" onChange="carregarDocente()">';
        $periodoM = new periodoModel();
        $resultado_periodos = $periodoM->listar(array(), array('id_periodo' => 'DESC'));
        $select .= "<option value=''>";
        $select .= "SELECIONE O PERÍODO";
        $select .= '</option>';
        while ($linha = mysqli_fetch_assoc($resultado_periodos)) {
            $select .= "<option value='{$linha['id_periodo']}'>";
            $select .= $linha['ano'] .'/'. $linha['semestre'];
            $select .= '</option>';
        }
        $select .= '</select>';
        $resposta = array('select' => $select);
        return json_encode($resposta);
    }
    
    public function carregarDocente() {
        $usuarioM = new usuarioModel();
        $resultado_docentes = $usuarioM->docentesRidPeriodo($_POST['id_periodo']);
        $select = '';
        $registros = mysqli_num_rows($resultado_docentes);
        if ($registros > 0) {
            $select = '<label for="id_usuario">Docentes:</label>';
            $select .= '<select id="id_usuario" name="id_usuario" class="form-control" style="width:100%">';
            $select .= "<option value=''>Todos</option>";            
            while ($linha = mysqli_fetch_assoc($resultado_docentes)) {
                $select .= "<option value='{$linha['id_usuario']}'>";
                $select .= $linha['nome'];
                $select .= '</option>';
            }
            $select .= '</select>';
        } else {
           $select = '<div class="alert alert-warning" style="margin-top:12px">Não há RIDs aprovados no período!</div>'; 
        }
        $resposta = array('select' => $select,'registros'=>$registros);
        return json_encode($resposta);
    }    
    
    public function grava_xlsx($rows,$nome,$periodo,$dir){
        $sheet1 = 'Comprovantes';
        $header = array("string","string","string");
        $writer = new XLSXWriter();
        $writer->setAuthor('hAcademico');
        $writer->writeSheetHeader($sheet1, $header, $col_options = ['suppress_row'=>true,'font-style'=>'bold','widths'=>[60,10,20]] );
        $i = 0;
        foreach($rows as $row) {
            if ($i == 0) {
                $format = $format = array('font'=>'Arial','font-style'=>'bold','font-size'=>10,'height'=>30,'wrap_text'=>true);
            } else {
                $format = array('font'=>'Arial','font-size'=>10,'height'=>30,'wrap_text'=>true);
            }
            $writer->writeSheetRow($sheet1, $row, $format);
        }
        $writer->markMergedCell($sheet1, $start_row=0, $start_col=0, $end_row=0, $end_col=3);
        $writer->writeToFile($dir.'/'.$nome.'-'.$periodo.'.xlsx');            
    }
    
    public function adicionar_zip($arquivo_zip,$dir,$arquivo) {
        $zip = new ZipArchive();
        if ($zip->open($dir.'/'.$arquivo_zip, ZipArchive::CREATE)!==TRUE) {
            die("Não foi possível abrir <$arquivo_zip>");
        }
        $vet_arquivo = explode(".", $arquivo);
        if ($vet_arquivo[1] == 'pdf') {
             if (!file_exists($_SESSION['diretorio_base'] . '/comprovantes/'.$arquivo)) {
                $zip->close();
                return "Comprovante não encontrado: ". $arquivo;
             } else {
                $zip->addFile($_SESSION['diretorio_base'] . '/comprovantes/'.$arquivo,$arquivo);
                $zip->close();
                return "ok";
             }
        } else {
            if (file_exists($dir.'/'.$arquivo)) {
                $zip->addFile($dir.'/'.$arquivo,$arquivo);
                $zip->close();
                return "ok";
            } else {
                return $dir.'/'.$arquivo;
            }
            
        }
    }
    
    public function baixarArquivos() {
        
        $nome = '';
        $periodo = '';
        $rows = array();
        $dir = '';
        $id_usuario = $_POST['id_usuario'];
        $id_usuario_anterior = '';
        $arquivo_zip = '';
                
        $atividade_docenteM = new atividade_docenteModel();
        $result = $atividade_docenteM->comprovantesPeriodo($_POST);

        $cht = 0;
        $tabela = '';
        while ($linha = mysqli_fetch_assoc($result)) {

            if ($id_usuario_anterior == '') {
                
                $nome = $linha['nome'];
                $periodo = $linha['periodo'];
                
                // Adiciona o nome na primeira linha da planilha
                $rows[] = array('Atividades RID ('.$periodo.') : '.$nome);
                $rows[] = array('Atividades','CH','Comprovante');
                
                /*
                 * Verifica se existe a pasta para o primeiro docente e para
                 * o períoodo.
                 */                
                $dir = $_SESSION['diretorio_base'].'/comprovantes/usuario_'.$linha['id_usuario'];
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir);
                }              
                
                $tabela .= '<table class="table table-condensed" style="background-color:'.$linha['cor'].'">';
                $tabela .= '<tr>';
                $tabela .= '<th colspan="3" style="text-align:center">'.$linha['nome'].'</th>';
                $tabela .= '</tr>';
                $tabela .= '<tr>';
                $tabela .= '<th width="80%">Atividade</th>';
                $tabela .= '<th width="5%" style="text-align:right">CH</th>';
                $tabela .= '<th width="15%" style="text-align:center">Nome do arquivo</th>';
                $tabela .= '</tr>';                
            
            } else if (($linha['id_usuario'] != $id_usuario_anterior) && ($id_usuario_anterior != '')) { 
                
                
                // Grava o xlsx na pasta antes de passar para o próximo docente
                $this->grava_xlsx($rows,$nome,$periodo,$dir);
                $this->adicionar_zip($arquivo_zip, $dir, $nome.'-'.$periodo.'.xlsx');
                
                $link = '<a href="download_zip.php?arquivo='.$dir.'/'.$arquivo_zip.'">Baixar comprovantes - '.$nome.'</a><br><br>';                
                
                
                // Dados do novo docente
                $nome = $linha['nome'];
                $periodo = $linha['periodo'];
                $rows = array();
                $rows[] = array('Atividades RID ('.$periodo.') : '.$nome);
                $rows[] = array('Atividades','CH','Comprovante');
                
                /*
                 * Verifica se existe a pasta para o próximo docente e para
                 * o períoodo.
                 */
                $dir = $_SESSION['diretorio_base'].'/comprovantes/usuario_'.$linha['id_usuario'];
                if (!file_exists($dir) && !is_dir($dir)) {
                    mkdir($dir);
                }
                
                $tabela .= '<tr>';
                $tabela .= '<th style="text-align:right">Total</th>';
                $tabela .= '<th style="text-align:center">'.$cht.'</th>';
                $tabela .= '<th></th>';
                $tabela .= '</tr>';
                $tabela .= '</table>';
                
                $tabela .= $link;
                
                $tabela .= '<table class="table table-condensed" style="background-color:'.$linha['cor'].'">';
                $tabela .= '<tr>';
                $tabela .= '<th colspan="3" style="text-align:center">'.$linha['nome'].'</th>';
                $tabela .= '</tr>';
                $tabela .= '<tr>';
                $tabela .= '<th width="80%">Atividade</th>';
                $tabela .= '<th width="5%" style="text-align:right">CH</th>';
                $tabela .= '<th width="15%" style="text-align:center">Nome do arquivo</th>';
                $tabela .= '</tr>'; 
                $cht = 0;
            }
            
            $rows[] = array($linha['atividade'],$linha['horas_executadas'],$linha['arquivo'].'.pdf');
            
            $arquivo_zip = $linha['nome'].'-'.$linha['ano'].'-'.$linha['semestre'].'.zip';
            $arquivo_pdf = $linha['arquivo'].'.pdf';
            $inserido = $this->adicionar_zip($arquivo_zip,$dir,$arquivo_pdf);
            
            $tabela .= '<tr>';
            $tabela .= '<td>'.$linha['atividade'].'</td>';
            $tabela .= '<td style="text-align:right">'.$linha['horas_executadas'].'</td>';
            $tabela .= '<td style="text-align:center">'.$linha['arquivo'].'.pdf</td>';
            $tabela .= '<th>'.$inserido.'</th>';
            $tabela .= '</tr>';
            
            $cht = $cht + $linha['horas_executadas'];
            $id_usuario_anterior = $linha['id_usuario'];
        }
        $tabela .= '<tr>';
        $tabela .= '<th style="text-align:right">Total</th>';
        $tabela .= '<th style="text-align:right">'.$cht.'</th>';
        $tabela .= '<th></th>';
        $tabela .= '</tr>';
        $tabela .= '</table>';
        

        // Grava o xlsx na pasta após docente único ou o último docente
        $this->grava_xlsx($rows,$nome,$periodo,$dir);
        $this->adicionar_zip($arquivo_zip, $dir, $nome.'-'.$periodo.'.xlsx');
        
        $link = '<a href="download_zip.php?arquivo='.$dir.'/'.$arquivo_zip.'">Baixar comprovantes - '.$nome.'</a><br>';                
        $tabela .= $link;
        
        
        $resposta = array('tabela' => $tabela);
        return json_encode($resposta);
    }
    
    function download_zip($arquivo) {
        if (isset($arquivo)) {
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Cache-Control: no-cache");
            header("Content-length: " . filesize($arquivo));
            header("Content-Disposition: attachment;filename=\"". basename($arquivo)."\"");
            readfile($arquivo);
        } else {
            die('Acesso inválido!');
        }
    }
}

// Callback
if (isset($_POST['metodo'])) {
    $metodo = $_POST['metodo'];
    $objeto = new comprovante_baixarController();
    echo $objeto->$metodo();
}