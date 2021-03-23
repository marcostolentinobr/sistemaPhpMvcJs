<?php

class Model extends Conexao {

    protected $valorBuscar;
    public $pagina_total = 2;
    public $buscarCampos = [];
    public $paginacao = true;
    public $valorChave = '';
    public $keyChave = false;

    public function __construct($pdo = '', $valorBuscar = '', $paginacao = true) {
        parent::__construct($pdo);
        $this->valorChave = @$_POST[$this->ID_CHAVE];
        $this->valorBuscar = $valorBuscar;
        $this->paginacao = $paginacao;
    }

    protected function setBuscarLista() {
        if (campo($this->valorBuscar)) {
            $busca = '';
            foreach ($this->buscarCampos as $ind => $campo) {
                if ($ind == 0) {
                    $busca = "( $campo LIKE '%$this->valorBuscar%'";
                } else {
                    $busca .= " OR $campo LIKE '%$this->valorBuscar%'";
                }
            }
            $this->addWhere('', $busca . ' )', ' ');
        }
    }

    protected function listaRetorno($sql) {
        $this->setBuscarLista();
        $limit = '';
        if ($this->paginacao) {
            $inicio = (coalesce(@$_GET['pagina'], 1) - 1) * $this->pagina_total;
            $limit = " LIMIT $inicio,$this->pagina_total ";
        }
        $retorno = $this->getListar($sql, true, $limit);
        if ($retorno && $this->keyChave) {
            $retornoNovo = [];
            foreach ($retorno as $dado) {
                $retornoNovo[$dado[$this->ID_CHAVE]] = $dado;
            }
            $retorno = $retornoNovo;
        }

        $this->linhasTotalMomento = $this->linhasTotal;

        //Apenas para declarar a quantidade de totais de linhas sem a paginação
        if ($this->paginacao) {
            $this->getListar($sql);
        }
        return $retorno;
    }

    public function descricaoExistente($valores = []) {
        if ($this->valorChave) {
            $this->addWhere($this->ID_CHAVE, $this->valorChave, '!=');
        }

        foreach ($valores as $campo => $valor) {
            $this->addWhere($campo, $valor);
        }

        return count($this->listar());
    }

    public function listar() {
        $sql = "SELECT * FROM $this->tabela";
        return $this->listaRetorno($sql);
    }

    public function incluir($valores, $tabela = '') {
        return parent::incluir(coalesce($tabela, $this->tabela), $valores);
    }

    public function alterar($valores, $tabela = '') {
        $this->addWhere($this->ID_CHAVE, $this->valorChave, 'updateExcluir');
        return parent::alterar(coalesce($tabela, $this->tabela), $valores);
    }

    public function excluir($tabela = '') {
        $this->addWhere($this->ID_CHAVE, $this->valorChave, 'updateExcluir');
        return parent::excluir(coalesce($tabela, $this->tabela));
    }

}
