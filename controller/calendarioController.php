<?php

require_once $_SESSION['diretorio_base'] . '/model/horarioModel.php';
require_once $_SESSION['diretorio_base'] . '/model/periodoModel.php';
require_once $_SESSION['diretorio_base'] . '/model/feriadoModel.php';

class calendarioController {

    public $dia;
    public $mes;
    public $ano;
    public $tstamp;
    public $dtmanip;
    public $dsprimdia;
    public $linhafechada;
    private $horario;
    private $periodo;
    private $dias = array();
    private $sabados = array();
    private $feriados = array();
    private $feriadoM;

    public function __construct($pmes, $pano, $id_oferta_disciplina) {
        $this->linhafechada = true;
        $this->dia = 1;
        $this->mes = $pmes;
        $this->ano = $pano;
        $this->calcula_tstamp();
        $this->data_manipulavel();
        $this->primeiro_dia_mes();
        $this->horario = new horarioModel();
        
        
        $result = $this->horario->getDiaOferta($id_oferta_disciplina);
        while ($linha = mysqli_fetch_assoc($result)) {
            $this->dias[] = $linha['id_dia'];
        }

        $result = $this->horario->getSabadosOferta($id_oferta_disciplina);
        while ($linha = mysqli_fetch_assoc($result)) {
            $this->sabados[$linha['data']] = $linha['descricao'];
        }
        //print_r($this->sabados);
        
        $this->feriadoM = new feriadoModel();
        $result = $this->feriadoM->getFeriado($_POST['id_periodo']);
        while ($linha = mysqli_fetch_assoc($result)) {
            $this->feriados[] = $linha['data_feriado'];
        }
        //$this->feriados = array('2022-09-07','2022-09-08','2022-09-09','2022-10-12','2022-10-28','2022-11-02','2022-11-14','2022-11-15','2022-12-08','2022-12-09');
        
        //print_r($this->sabados);
        $this->periodo = new periodoModel();
    }

    public function calcula_tstamp() {
        $this->tstamp = mktime(0, 0, 0, $this->mes, $this->dia, $this->ano);
    }

    public function data_manipulavel() {
        $this->dtmanip = getdate($this->tstamp);
    }

    public function primeiro_dia_mes() {
        $this->dsprimdia = $this->dtmanip["wday"];
    }

    public function proximo_dia() {
        $this->dia++;
        $this->calcula_tstamp();
    }

    public function exibe_calendario() {

        $meses = array(
            1 => 'Janeiro',
            'Fevereiro',
            'Março',
            'Abril',
            'Maio',
            'Junho',
            'Julho',
            'Agosto',
            'Setembro',
            'Outubro',
            'Novembro',
            'Dezembro'
        );

        $larg = 50 / 7.0;

        $tabela = '<div class="container">';
        $tabela .= '<div class="row">';
        $tabela .= '<div class="col-md-12">';
        $tabela .= "<table class='table table-bordered'>\n";
        $tabela .= '<thead>';
        $tabela .= "<tr>\n";
        $tabela .= "<th colspan='8'>{$meses[$this->mes]}</th>\n";
        $tabela .= "</tr>\n";
        $tabela .= "<tr>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Dom</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Seg</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Ter</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Qua</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Qui</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Sex</th>\n";
        $tabela .= "<th style='text-align:center' width='" . $larg . "%'>Sab</th>\n";
        $tabela .= "<th style='text-align:center' width='50%'>Dia e horários do sábado letivo</th>\n";
        $tabela .= "</tr>\n";
        $tabela .= '</thead>';

        $ccol = 0;
        $casa = 0;
        
        $result_periodo = $this->periodo->getPeriodo($_POST['id_periodo']);
        $linha_periodo = mysqli_fetch_assoc($result_periodo);
        //print_r($linha_periodo);
        $vet_inicio = explode("-", $linha_periodo['data_inicio']);
        $vet_fim = explode("-", $linha_periodo['data_fim']);

        while (checkdate($this->mes, $this->dia, $this->ano)) {
            if ($this->linhafechada) {
                $tabela .= "<tr>\n";
                $this->linhafechada = false;
            }
            if ($casa < $this->dsprimdia) {
                $tabela .= "<td> </td>\n";
            } else {
                
                $dia_semana = $ccol + 1;
                
                //echo $this->ano.'-'.str_pad($this->mes,'2','0',STR_PAD_LEFT).'-'.str_pad($this->dia,'2','0',STR_PAD_LEFT);
                //print_r($this->feriados);
                
                if (in_array($this->ano.'-'.str_pad($this->mes,'2','0',STR_PAD_LEFT).'-'.str_pad($this->dia,'2','0',STR_PAD_LEFT),$this->feriados)) {    
                    
                    $tabela .= "<td style='background-color:red' align='center'>\n";
                    $tabela .= $this->dia . "\n";
                    $tabela .= "</td>\n";                    
                
                
                } else if (
                        
                        (in_array($dia_semana, $this->dias) || (array_key_exists($this->ano.'-'.str_pad($this->mes,'2','0',STR_PAD_LEFT).'-'.str_pad($this->dia,'2','0',STR_PAD_LEFT),$this->sabados))  ) && 
                        
                        (!(($this->dia >= 26 && $this->dia <= 30) && $this->mes == 12)) &&
                        
                        (
                            (mktime(0, 0, 0, $this->mes, $this->dia, $this->ano) >= mktime(0, 0, 0, $vet_inicio[1], $vet_inicio[2], $vet_inicio[0]) ) &&
                            (mktime(0, 0, 0, $this->mes, $this->dia, $this->ano) <= mktime(0, 0, 0, $vet_fim[1], $vet_fim[2], $vet_fim[0]) )  
                        
                        )
                    ) {
                    $tabela .= "<td style='background-color:lightblue' align='center'>\n";
                    $tabela .= $this->dia . "\n";
                    $tabela .= "</td>\n";
                    
                    if (array_key_exists($this->ano.'-'.str_pad($this->mes,'2','0',STR_PAD_LEFT).'-'.str_pad($this->dia,'2','0',STR_PAD_LEFT),$this->sabados)) {
                        $tabela .= "<td>\n";
                        $tabela .= $this->sabados[$this->ano.'-'.str_pad($this->mes,'2','0',STR_PAD_LEFT).'-'.str_pad($this->dia,'2','0',STR_PAD_LEFT)] . "\n";
                        $tabela .= "</td>\n";
                    }
                    
                    
                } else {
                    $tabela .= "<td align='center'>\n";
                    $tabela .= $this->dia . "\n";
                    $tabela .= "</td>\n";
                }
                $this->proximo_dia();
            }
            $ccol++;
            $ccol = $ccol % 7;
            $casa++;
            if (( $casa % 7 ) == 0) {
                $tabela .= "</tr>\n";
                $this->linhafechada = true;
            }
        }
        while ($ccol != 0) {
            $ccol++;
            $ccol = $ccol % 7;
            $tabela .= "<td> </td>\n";
        }
        $tabela .= "</tr>\n";
        $tabela .= "</table>\n";
        $tabela .= "</div>\n";
        $tabela .= "</div>\n";
        $tabela .= "</div>\n";

        return $tabela;
    }

}
