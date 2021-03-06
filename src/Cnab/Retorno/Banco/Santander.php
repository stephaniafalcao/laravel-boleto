<?php
namespace Eduardokum\LaravelBoleto\Cnab\Retorno\Banco;

use Carbon\Carbon;
use Eduardokum\LaravelBoleto\Cnab\Contracts\Retorno;
use Eduardokum\LaravelBoleto\Cnab\Retorno\AbstractCnab;
use Eduardokum\LaravelBoleto\Cnab\Retorno\Detalhe;
use Eduardokum\LaravelBoleto\Util;

class Santander extends AbstractCnab implements Retorno
{

    public $agencia;
    public $conta;

    private $ocorrencias = array(
        '01' => 'Título não existe',
        '02' => 'Entrada título confirmada',
        '03' => 'Entrada título rejeitada',
        '06' => 'Liquidação',
        '07' => 'Liquidação por conta',
        '08' => 'Liquidação por saldo',
        '09' => 'Baixa automática',
        '10' => 'Títutlo baixado conforme instrução ou por título protestado',
        '11' => 'Em ser',
        '12' => 'Abatimento concedido',
        '13' => 'Abatimento cancelado',
        '14' => 'Prorrogação de vencimento',
        '15' => 'Enviado para Cartório',
        '16' => 'Título já baixado/liquidado',
        '17' => 'Liquidado em cartório',
        '21' => 'Entrada em Cartório',
        '22' => 'Retirado de cartório',
        '24' => 'Custas de Cartório',
        '25' => 'Protestar Título',
        '26' => 'Sustar Protesto',
    );

    private $especies = array(
        '01' => 'Duplicata',
        '02' => 'Nota promissória',
        '03' => 'Apólice ou nota de seguro',
        '05' => 'Recibo',
        '06' => 'Duplicata de serviço',
        '07' => 'Letra de câmbio',
        'XX' => 'Desconhecida',
    );

    private $indicativosCredito = [
        'D' => 'Débito',
        'C' => 'Crédito',
        'X' => 'Desconhecido',
    ];

    private $rejeicoes = array(
        '001' => 'NOSSO NUMERO NAO NUMERICO',
        '002' => 'VALOR DO ABATIMENTO NAO NUMERICO',
        '003' => 'DATA VENCIMENTO NAO NUMERICA',
        '004' => 'CONTA COBRANCA NAO NUMERICA',
        '005' => 'CODIGO DA CARTEIRA NAO NUMERICO',
        '006' => 'CODIGO DA CARTEIRA INVALIDO',
        '007' => 'ESPECIE DO DOCUMENTO INVALIDA',
        '008' => 'UNIDADE DE VALOR NAO NUMERICA',
        '009' => 'UNIDADE DE VALOR INVALIDA',
        '010' => 'CODIGO PRIMEIRA INSTRUCAO NAO NUMERICA',
        '011' => 'CODIGO SEGUNDA INSTRUCAO NAO NUMERICA',
        '012' => 'VALOR DO TITULO EM OUTRA UNIDADE',
        '013' => 'VALOR DO TITULO NAO NUMERICO',
        '014' => 'VALOR DE MORA NAO NUMERICO',
        '015' => 'DATA EMISSAO NÃO NUMERICA ',
        '016' => 'DATA DE VENCIMENTO INVALIDA',
        '017' => 'CODIGO DA AGENCIA COBRADORA NAO NUMERICA',
        '018' => 'VALOR DO IOC NAO NUMERICO',
        '019' => 'NUMERO DO CEP NAO NUMERICO',
        '020' => 'TIPO INSCRICAO NAO NUMERICO',
        '021' => 'NUMERO DO CGC OU CPF NAO NUMERICO',
        '022' => 'CODIGO OCORRENCIA INVALIDO',
        '024' => 'TOTAL PARCELA NAO NUMERICO',
        '025' => 'VALOR DESCONTO NAO NUMERICO',
        '026' => 'CODIGO BANCO COBRADOR INVALIDO',
        '027' => 'NUMERO PARCELAS CARNE NAO NUMERICO',
        '028' => 'NUMERO PARCELAS CARNE ZERADO',
        '029' => 'VALOR DE MORA INVALIDO',
        '030' => 'DT VENC MENOR DE 15 DIAS DA DT PROCES',
        '039' => 'PERFIL NAO ACEITA TITULO EM BCO CORRESP',
        '041' => 'AGENCIA COBRADORA NAO ENCONTRADA',
        '042' => 'CONTA COBRANCA INVALIDA',
        '043' => 'NAO BAIXAR,  COMPL. INFORMADO INVALIDO',
        '044' => 'NAO PROTESTAR, COMPL. INFORMADO INVALIDO',
        '045' => 'QTD DE DIAS DE BAIXA NAO PREENCHIDO',
        '046' => 'QTD DE DIAS PROTESTO NAO PREENCHIDO',
        '047' => 'TOT PARC. INF. NAO BATE C/ QTD PARC GER',
        '048' => 'CARNE COM PARCELAS COM ERRO',
        '049' => 'SEU NUMERO NAO CONFERE COM O CARNE',
        '051' => 'TITULO NAO ENCONTRADO',
        '052' => 'OCOR.  NAO ACATADA, TITULO  LIQUIDADO',
        '053' => 'OCOR. NAO ACATADA, TITULO BAIXADO',
        '054' => 'TITULO COM ORDEM DE PROTESTO JA EMITIDA',
        '055' => 'OCOR. NAO ACATADA, TITULO JA PROTESTADO',
        '056' => 'OCOR. NAO ACATADA, TIT. NAO VENCIDO',
        '057' => 'CEP DO SACADO INCORRETO',
        '058' => 'CGC/CPF INCORRETO',
        '059' => 'INSTRUCAO ACEITA SO P/ COBRANCA SIMPLES',
        '060' => 'ESPECIE DOCUMENTO NAO PROTESTAVEL',
        '061' => 'CEDENTE SEM CARTA DE PROTESTO',
        '062' => 'SACADO NAO PROTESTAVEL',
        '063' => 'CEP NAO ENCONTRADO NA TABELA DE PRACAS',
        '064' => 'TIPO DE COBRANCA NAO PERMITE PROTESTO',
        '064' => 'TIPO DE COBRANCA NAO PERMITE PROTESTO',
        '065' => 'PEDIDO SUSTACAO JA SOLICITADO',
        '066' => 'SUSTACAO PROTESTO FORA DE PRAZO',
        '066' => 'SUSTACAO PROTESTO FORA DE PRAZO',
        '067' => 'CLIENTE NAO TRANSMITE REG. DE OCORRENCIA',
        '068' => 'TIPO DE VENCIMENTO INVALIDO',
        '069' => 'PRODUTO DIFERENTE DE COBRANCA SIMPLES',
        '070' => 'DATA PRORROGACAO MENOR QUE DATA VENCTO',
        '071' => 'DATA ANTECIPACAO MAIOR QUE DATA VENCTO',
        '072' => 'DATA DOCUMENTO SUPERIOR A DATA INSTRUCAO',
        '073' => 'ABATIMENTO MAIOR/IGUAL AO VALOR TITULO',
        '074' => 'PRIM. DESCONTO MAIOR/IGUAL VALOR TITULO',
        '075' => 'SEG. DESCONTO MAIOR/IGUAL VALOR TITULO',
        '076' => 'TERC. DESCONTO MAIOR/IGUAL VALOR TITULO',
        '077' => 'DESC. POR ANTEC. MAIOR/IGUAL VLR TITULO',
        '078' => 'NAO EXISTE ABATIMENTO P/ CANCELAR',
        '079' => 'NAO EXISTE PRIM. DESCONTO P/ CANCELAR',
        '080' => 'NAO EXISTE SEG. DESCONTO P/ CANCELAR',
        '081' => 'NAO EXISTE TERC. DESCONTO P/ CANCELAR',
        '082' => 'NAO EXISTE DESC. POR ANTEC. P/ CANCELAR',
        '084' => 'JA EXISTE SEGUNDO DESCONTO',
        '085' => 'JA EXISTE TERCEIRO DESCONTO',
        '086' => 'DATA SEGUNDO DESCONTO INVALIDA',
        '087' => 'DATA TERCEIRO DESCONTO INVALIDA',
        '089' => 'DATA MULTA MENOR/IGUAL QUE VENCIMENTO',
        '090' => 'JA EXISTE DESCONTO POR DIA ANTECIPACAO',
        '091' => 'JA EXISTE CONCESSAO DE DESCONTO',
        '092' => 'NOSSO NUMERO JA CADASTRADO',
        '093' => 'VALOR DO TITULO NAO INFORMADO',
        '094' => 'VALOR TIT. EM OUTRA MOEDA NAO INFORMADO',
        '095' => 'PERFIL NAO ACEITA VALOR TITULO ZERADO',
        '096' => 'ESPECIE DOCTO NAO PERMITE PROTESTO',
        '097' => 'ESPECIE DOCTO NAO PERMITE IOC ZERADO',
        '098' => 'DATA EMISSAO INVALIDA',
        '099' => 'REGISTRO DUPLICADO NO MOVIMENTO DIARIO',
        '099' => 'REGISTRO DUPLICADO NO MOVIMENTO DIÁRIO',
        '100' => 'DATA EMISSAO MAIOR QUE A DATA VENCIMENTO',
        '101' => 'NOME DO SACADO NÃO INFORMADO ',
        '102' => 'ENDERECO DO SACADO NÃO INFORMADO',
        '103' => 'MUNICIPIO DO SACADO NAO INFORMADO',
        '104' => 'UNIDADE DA FEDERACAO NAO INFORMADA',
        '105' => 'TIPO INSCRICAO NÃO EXISTE',
        '106' => 'CGC/CPF NAO INFORMADO',
        '107' => 'UNIDADE DA FEDERACAO INCORRETA',
        '108' => 'DIGITO CGC/CPF INCORRETO',
        '109' => 'VALOR MORA TEM QUE SER ZERO (TIT = ZERO)',
        '110' => 'DATA PRIMEIRO DESCONTO INVALIDA',
        '111' => 'DATA  DESCONTO NAO NUMERICA',
        '112' => 'VALOR DESCONTO NAO INFORMADO',
        '113' => 'VALOR DESCONTO INVALIDO',
        '114' => 'VALOR ABATIMENTO NAO INFORMADO',
        '114' => 'VALOR ABATIMENTO NAO INFORMADO',
        '115' => 'VALOR ABATIMENTO MAIOR VALOR TITULO',
        '116' => 'DATA MULTA NAO NUMERICA',
        '117' => 'VALOR DESCONTO MAIOR VALOR TITULO',
        '118' => 'DATA MULTA NAO INFORMADA',
        '119' => 'DATA MULTA MAIOR QUE DATA DE VENCIMENTO',
        '120' => 'PERCENTUAL MULTA NAO NUMERICO',
        '121' => 'PERCENTUAL MULTA NAO INFORMADO',
        '122' => 'VALOR IOF MAIOR QUE VALOR TITULO',
        '123' => 'CEP DO SACADO NAO NUMERICO',
        '124' => 'CEP SACADO NAO ENCONTRADO',
        '126' => 'CODIGO P. BAIXA / DEVOL. INVALIDO',
        '127' => 'CODIGO P. BAIXA / DEVOL. NAO NUMERICA',
        '128' => 'CODIGO PROTESTO INVALIDO',
        '129' => 'ESPEC DE DOCUMENTO NAO NUMERICA',
        '130' => 'FORMA DE CADASTRAMENTO NAO NUMERICA',
        '131' => 'FORMA DE CADASTRAMENTO INVALIDA',
        '132' => 'FORMA CADAST. 2 INVALIDA PARA CARTEIRA 3',
        '133' => 'FORMA CADAST. 2 INVALIDA PARA CARTEIRA 4',
        '134' => 'CODIGO DO MOV. REMESSA NAO NUMERICO',
        '135' => 'CODIGO DO MOV. REMESSA INVALIDO',
        '136' => 'CODIGO BCO NA COMPENSACAO NAO NUMERICO',
        '138' => 'NUM. LOTE REMESSA(DETALHE) NAO NUMERICO',
        '140' => 'COD. SEQUEC.DO REG. DETALHE INVALIDO',
        '141' => 'NUM. SEQ. REG. DO LOTE NAO NUMERICO',
        '142' => 'NUM.AG.CEDENTE/DIG.NAO NUMERICO',
        '144' => 'TIPO DE DOCUMENTO NAO NUMERICO',
        '145' => 'TIPO DE DOCUMENTO INVALIDO',
        '146' => 'CODIGO P. PROTESTO NAO NUMERICO',
        '147' => 'QTDE DE DIAS P. PROTESTO INVALIDO',
        '148' => 'QTDE DE DIAS P. PROTESTO NAO NUMERICO',
        '149' => 'CODIGO DE MORA INVALIDO',
        '150' => 'CODIGO DE MORA NAO NUMERICO',
        '151' => 'VL.MORA IGUAL A ZEROS P. COD.MORA 1',
        '152' => 'VL. TAXA MORA IGUAL A ZEROS P.COD MORA 2',
        '154' => 'VL. MORA NAO NUMERICO P. COD MORA 2',
        '155' => 'VL. MORA INVALIDO P. COD.MORA 4',
        '156' => 'QTDE DIAS P.BAIXA/DEVOL. NAO NUMERICO',
        '157' => 'QTDE DIAS BAIXA/DEV. INVALIDO P. COD. 1',
        '158' => 'QTDE DIAS BAIXA/DEV. INVALIDO P.COD. 2',
        '160' => 'BAIRRO DO SACADO NAO INFORMADO',
        '161' => 'TIPO INSC.CPF/CGC SACADOR/AVAL.NAO NUM.',
        '162' => 'INDICADOR DE CARNE NAO NUMERICO',
        '163' => 'NUM. TOTAL DE PARC.CARNE NAO NUMERICO',
        '164' => 'NUMERO DO PLANO NAO NUMERICO',
        '165' => 'INDICADOR DE PARCELAS CARNE INVALIDO',
        '166' => 'N.SEQ. PARCELA INV.P.INDIC. MAIOR 0',
        '167' => 'N. SEQ.PARCELA INV.P.INDIC.DIF.ZEROS',
        '168' => 'N.TOT.PARC.INV.P.INDIC. MAIOR ZEROS',
        '169' => 'NUM.TOT.PARC.INV.P.INDIC.DIFER.ZEROS',
        '170' => 'FORMA DE CADASTRAMENTO 2 INV.P.CART.5',
        '199' => 'TIPO INSC.CGC/CPF SACADOR.AVAL.INVAL.',
        '200' => 'NUM.INSC.(CGC)SACADOR/AVAL.NAO NUMERICO',
        '201' => 'ALT. DO CONTR. PARTICIPANTE INVALIDO',
        '202' => 'ALT. DO SEU NUMERO INVALIDA',
        '218' => 'BCO COMPENSACAO NAO NUMERICO (D3Q)',
        '219' => 'BCO COMPENSACAO INVALIDO (D3Q)',
        '220' => 'NUM. DO LOTE REMESSA NAO NUMERICO(D3Q)',
        '221' => 'NUM. SEQ. REG. NO LOTE (D3Q)',
        '222' => 'TIPO INSC.SACADO NAO NUMERICO (D3Q)',
        '223' => 'TIPO INSC.SACADO INVALIDO (D3Q)',
        '224' => 'NUM.INSC.SACADO NAO NUMERICO (D3Q)',
        '225' => 'NUM.INSC.SAC.INV.P.TIPO INSC.0 E 9(D3Q)',
        '226' => 'NUM.BCO COMPENSACAO NAO NUMERICO (D3R)',
        '228' => 'NUM. LOTE REMESSA NAO NUMERICO (D3R)',
        '229' => 'NUM. SEQ. REG. LOTE NAO NUMERICO (D3R)',
        '246' => 'COD.BCO COMPENSACAO NAO NUMERICO (D3S)',
        '247' => 'COD. BANCO COMPENSACAO INVALIDO (D3S)',
        '248' => 'NUM.LOTE REMESSA NAO NUMERICO (D3S)',
        '249' => 'NUM.SEQ.DO REG.LOTE NAO NUMERICO (D3S)',
        '250' => 'NUM.IDENT.DE IMPRESSAO NAO NUMERICO(D3S)',
        '251' => 'NUM.IDENT.DE IMPRESSAO INVALIDO (D3S)',
        '252' => 'NUM.LINHA IMPRESSA NAO NUMERICO(D3S)',
        '253' => 'COD.MSG. P.REC. SAC. NAO NUMERICO (D3S)',
        '254' => 'COD.MSG.P.REC.SACADO INVALIDO(D3S)',
        '258' => 'VL.MORA NAO NUMERICO P.COD=4(D3P)',
        '259' => 'CAD.TXPERM.SK.INV.P.COD.MORA=4(D3P)',
        '260' => 'VL.TIT(REAL).INV.P.COD.MORA = 1(DEP)',
        '261' => 'VL.OUTROS INV.P.COD.MORA = 1(D3P)',
        '' => '',
    );

    public function __construct($file)
    {
        parent::__construct($file);

        $this->banco = self::COD_BANCO_SANTANDER;
        $this->agencia = (int)substr($this->file[0], 26, 4);
        $this->conta = (int)substr($this->file[0], 30, 8);
    }

    protected function processarHeader(array $header)
    {
        $this->header->operacaoCodigo = $this->rem(2, 2, $header);
        $this->header->operacao = $this->rem(3, 9, $header);
        $this->header->servicoCodigo = $this->rem(10, 11, $header);
        $this->header->servico = $this->rem(12, 26, $header);
        $this->header->agencia = $this->rem(27, 30, $header);
        $this->header->conta = $this->rem(31, 38, $header);
        $this->header->contaCobranca = $this->rem(39, 46, $header);
        $this->header->cedenteNome = $this->rem(47, 76, $header);
        $this->header->data = $this->rem(95, 100, $header);
        $this->header->cedenteCodigo = $this->rem(111, 117, $header);

        $this->header->data = trim($this->header->data, '0 ') == "" ? null : Carbon::createFromFormat('dmy', $this->header->data)->setTime(0, 0, 0);
    }

    protected function processarDetalhe(array $detalhe)
    {
        $i = $this->i;

        $this->detalhe[$i] = new Detalhe($detalhe);
        $this->detalhe[$i]->numeroControle = Util::controle2array($this->rem(38, 62, $detalhe));
        $this->detalhe[$i]->nossoNumero = $this->rem(63, 70, $detalhe);
        $this->detalhe[$i]->numeroDocumento = $this->rem(117, 126, $detalhe);
        $this->detalhe[$i]->numero = $this->rem(127, 134, $detalhe);
        $this->detalhe[$i]->ocorrencia = $this->rem(109, 110, $detalhe);
        $this->detalhe[$i]->dataOcorrencia = $this->rem(111, 116, $detalhe);
        $this->detalhe[$i]->dataVencimento = $this->rem(147, 152, $detalhe);
        $this->detalhe[$i]->dataCredito = $this->rem(296, 301, $detalhe);
        $this->detalhe[$i]->rejeicao = $this->rem(135, 136, $detalhe);
        $this->detalhe[$i]->erro1 = $this->rem(137, 139, $detalhe);
        $this->detalhe[$i]->erro2 = $this->rem(140, 142, $detalhe);
        $this->detalhe[$i]->erro3 = $this->rem(143, 145, $detalhe);
        $this->detalhe[$i]->bancoCobrador = $this->rem(166, 168, $detalhe);
        $this->detalhe[$i]->agenciaCobradora = $this->rem(169, 173, $detalhe);
        $this->detalhe[$i]->especie = $this->rem(174, 175, $detalhe);
        $this->detalhe[$i]->valor = Util::nFloat($this->rem(153, 165, $detalhe)/100);
        $this->detalhe[$i]->valorTarifa = Util::nFloat($this->rem(176, 188, $detalhe)/100);
        $this->detalhe[$i]->valorOutrosDebitos = Util::nFloat($this->rem(189, 201, $detalhe)/100);
        $this->detalhe[$i]->valorJuros = Util::nFloat($this->rem(202, 214, $detalhe)/100);
        $this->detalhe[$i]->valorIOF = Util::nFloat($this->rem(215, 227, $detalhe)/100);
        $this->detalhe[$i]->valorAbatimento = Util::nFloat($this->rem(228, 240, $detalhe)/100);
        $this->detalhe[$i]->valorDesconto = Util::nFloat($this->rem(241, 253, $detalhe)/100);
        $this->detalhe[$i]->valorRecebido = Util::nFloat($this->rem(254, 266, $detalhe)/100);
        $this->detalhe[$i]->valorMora = Util::nFloat($this->rem(267, 279, $detalhe)/100);
        $this->detalhe[$i]->valorOutrosCreditos = Util::nFloat($this->rem(280, 292, $detalhe)/100);
        $this->detalhe[$i]->valorOutraUnidade = Util::nFloat($this->rem(341, 353, $detalhe)/100);
        $this->detalhe[$i]->valorOutraMoeda = Util::nFloat($this->rem(354, 366, $detalhe)/100);
        $this->detalhe[$i]->valorDebitoCredito = Util::nFloat($this->rem(367, 379, $detalhe)/100);
        $this->detalhe[$i]->aceite = $this->rem(294, 294, $detalhe);
        $this->detalhe[$i]->sacadoNome = $this->rem(302, 337, $detalhe);
        $this->detalhe[$i]->indicativoCredito = $this->rem(380, 380, $detalhe);

        $this->totais['valor'] += $this->detalhe[$i]->valor;

        $this->detalhe[$i]->especieNome = $this->especies[$this->detalhe[$i]->get('especie', 'XX', true)];
        $this->detalhe[$i]->ocorrenciaNome = $this->ocorrencias[$this->detalhe[$i]->get('ocorrencia', 'XX', true)];
        $this->detalhe[$i]->indicativoCreditoNome = $this->indicativosCredito[$this->detalhe[$i]->get('indicativoCredito', 'X', true)];

        $this->detalhe[$i]->dataOcorrencia = $this->detalhe[$i]->get('dataOcorrencia', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataOcorrencia'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataVencimento = $this->detalhe[$i]->get('dataVencimento', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataVencimento'))->setTime(0, 0, 0) : null;
        $this->detalhe[$i]->dataCredito = $this->detalhe[$i]->get('dataCredito', false, true) ? Carbon::createFromFormat('dmy', $this->detalhe[$i]->get('dataCredito'))->setTime(0, 0, 0) : null;

        if(in_array($this->detalhe[$i]->get('ocorrencia'), ['06','07','08','16','17']))
        {
            $this->totais['liquidados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_LIQUIDADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['02']))
        {
            $this->totais['entradas']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ENTRADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['09','10']))
        {
            $this->totais['baixados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_BAIXADA);
        }
        elseif(in_array($this->detalhe[$i]->get('ocorrencia'), ['03']))
        {
            $this->totais['erros']++;

            $erros[] = $this->rejeicoes[$this->detalhe[$i]->get('erro1')];
            $erros[] = $this->rejeicoes[$this->detalhe[$i]->get('erro2')];
            $erros[] = $this->rejeicoes[$this->detalhe[$i]->get('erro3')];

            $this->detalhe[$i]->setErro(implode(', ', $erros));
        }
        else
        {
            $this->totais['alterados']++;
            $this->detalhe[$i]->setTipoOcorrencia(Detalhe::OCORRENCIA_ALTERACAO);
        }

        $this->i++;
    }

    protected function processarTrailer(array $trailer)
    {
        $this->trailer->quantidadeTitulos = (int)$this->i;
        $this->trailer->valorTitulos = Util::nFloat($this->totais['valor']);
        $this->trailer->avisos = (int)$this->rem(40, 47, $trailer) + (int)$this->rem(120, 137, $trailer) + (int)$this->rem(160, 167, $trailer);
        $this->trailer->quantidadeErros = (int)$this->totais['erros'];
        $this->trailer->quantidadeEntradas = (int)$this->totais['entradas'];
        $this->trailer->quantidadeLiquidados = (int)$this->totais['liquidados'];
        $this->trailer->quantidadeBaixados = (int)$this->totais['baixados'];
        $this->trailer->quantidadeAlterados = (int)$this->totais['alterados'];
    }
}