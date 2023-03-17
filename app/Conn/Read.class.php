<?php

/**
 * Read [ CONN ]
 * Classe responsável leituras genéricas no banco de dados
 * 
 * 
 * copyright (c) 2017 NOME_FANTASIA
 *
 * NOTICE OF LICENSE
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *  @author    Weverton J da Silva.
 *  @copyright 2017 NOME_FANTASIA
 *  @license   MIT License
 */
class Read extends Conn
{

    private $select;
    private $places;
    private $result;

    /** @var PDOStatement */
    private $read;

    /** @var PDO */
    private $conn;

    /**
     * <b>Exe Read:</b> Executa uma leitura simplificada com Prepared Statments. Basta informar o nome da tabela,
     * os termos da seleção e uma analize em cadeia (ParseString) para executar.
     * @param STRING $table = Nome da tabela
     * @param STRING $terms = WHERE | ORDER | LIMIT :limit | OFFSET :offset
     * @param STRING $parseString = link={$link}&link2={$link2}
     */
    public function exeRead($table, $terms = null, $parseString = null)
    {
        if (!empty($parseString)) {
            parse_str($parseString, $this->places);
        }

        $this->select = "SELECT * FROM {$table} {$terms}";
        $this->execute();
    }

    /**
     * <b>Obter resultado:</b> Retorna um array com todos os resultados obtidos. Envelope primário númérico. Para obter
     * um resultado chame o índice getResult()[0]!
     * @return ARRAY $this = Array ResultSet
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * <b>Contar Registros: </b> Retorna o número de registros encontrados pelo select!
     * @return INT $Var = Quantidade de registros encontrados
     */
    public function getRowCount()
    {
        return $this->read->rowCount();
    }

    public function fullRead($query, $parseString = null)
    {
        $this->select = (string) $query;
        if (!empty($parseString)) {
            parse_str($parseString, $this->places);
        }
        $this->execute();
    }

    /**
     * <b>Full Read:</b> Executa leitura de dados via query que deve ser montada manualmente para possibilitar
     * seleção de multiplas tabelas em uma única query!
     * @param STRING $Query = Query Select Syntax
     * @param STRING $ParseString = link={$link}&link2={$link2}
     */
    //Obtém o PDO e Prepara a query
    public function setPlaces($parseString)
    {
        parse_str($parseString, $this->places);
        $this->execute();
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    private function Connect()
    {
        $this->conn = parent::getConn();
        $this->read = $this->conn->prepare($this->select);
        $this->read->setFetchMode(PDO::FETCH_ASSOC);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax()
    {
        if ($this->places) {
            foreach ($this->places as $vinculo => $value) {
                if ($vinculo == 'limit' || $vinculo == 'offset') {
                    $value = (int) $value;
                }

                $this->read->bindValue(":{$vinculo}", $value, (is_int($value) ? PDO::PARAM_INT : PDO::PARAM_STR));
            }
        }
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function execute()
    {
        $this->connect();
        try {
            $this->getSyntax();
            $this->read->execute();
            $this->result = $this->read->fetchAll();
        } catch (PDOException $e) {
            $this->result = null;
            Msg("<b>Error</b> {$e->getMessage()}", $e->getCode());
        }
    }
}
