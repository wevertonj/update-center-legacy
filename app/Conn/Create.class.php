<?php

/**
 * Create [ CONN ]
 * Classe responsável pelos cadastros genéricos no banco de dados
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
class Create extends Conn
{

    private $table;
    private $data;
    private $result;

    /** @var PDOStatement */
    private $create;

    /** @var PDO */
    private $conn;

    /**
     * <b>exeCreate:</b> Executa um cadastro simplificado no banco de dados utilizando prepared stantements.
     * Basta informar o nome da tabela e um array atribuitivo com nome da coluna e valor!
     * 
     * @param string $table = informe o nome da tabela no banco!
     * @param array $data = Informe um array atribuitivo. (Nome da Coluna => Valor).
     */
    public function exeCreate($table, array $data)
    {
        $this->table = (string) $table;
        $this->data = $data;

        $this->getSyntax();
        $this->execute();
    }

    /**
     * <b>getResult:</b> Retorna FALSE ou o último ID inseredo na tabela, caso o cadastro seja executado com sucesso!
     * @return boolean
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    //Obtém o PDO e Prepara a query
    private function connect()
    {
        $this->conn = parent::getConn();
        $this->create = $this->conn->prepare($this->create);
    }

    //Cria a sintaxe da query para Prepared Statements
    private function getSyntax()
    {
        $fileds = implode(', ', array_keys($this->data));
        $places = ':' . implode(', :', array_keys($this->data));
        $this->create = "INSERT INTO {$this->table} ({$fileds}) VALUES ({$places})";
    }

    //Obtém a Conexão e a Syntax, executa a query!
    private function execute()
    {
        $this->connect();
        try {
            $this->create->execute($this->data);
            $this->result = $this->conn->lastInsertId();
        } catch (PDOException $e) {
            $this->result = null;
            Msg("<b>Error</b> {$e->getMessage()}", $e->getCode());
        }
    }
}
