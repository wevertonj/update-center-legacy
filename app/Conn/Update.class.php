<?php

/**
 * Update [ CONN ]
 * Classe responsável pelas atualizações genéricas no banco de dados 
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
class Update extends Conn
{

    private $table;
    private $data;
    private $terms;
    private $places;
    private $result;

    /** @var PDOStatement */
    private $update;

    /** @var PDO */
    private $conn;

    /**
     * <b>Exe Update:</b> Executa uma atualização simplificada com Prepared Statments. Basta informar o 
     * nome da tabela, os dados a serem atualizados em um Attay Atribuitivo, as condições e uma 
     * analize em cadeia (ParseString) para executar.
     * @param STRING $table = Nome da tabela
     * @param ARRAY $data = [ NomeDaColuna ] => Valor ( Atribuição )
     * @param STRING $terms = WHERE coluna = :link AND.. OR..
     * @param STRING $parseString = link={$link}&link2={$link2}
     */
    public function exeUpdate($table, array $data, $terms, $parseString)
    {
        $this->table = (string) $table;
        $this->data = $data;
        $this->terms = (string) $terms;

        parse_str($parseString, $this->places);
        $this->getSyntax();
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
        return $this->update->rowCount();
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
        $this->getSyntax();
        parse_str($parseString, $this->places);
        $this->execute();
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    // Obtém a PDO e prepara as queries.   
    private function connect()
    {
        $this->conn = parent::getConn();
        $this->update = $this->conn->prepare($this->update);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax()
    {
        foreach ($this->data as $key => $value) {
            $places[] = $key . ' = :' . $key;
        }

        $places = implode(', ', $places);
        $this->update = "UPDATE {$this->table} SET {$places} {$this->terms}";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function execute()
    {
        $this->connect();
        try {
            $this->update->execute(array_merge($this->data, $this->places));
            $this->result = true;
        } catch (PDOException $e) {
            $this->result = null;
            Msg("<b>Error</b> {$e->getMessage()}", $e->getCode());
        }
    }
}
