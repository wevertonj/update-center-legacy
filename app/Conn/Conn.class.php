<?php

/**
 * Conn [ CONN ]
 * Classe abstrata de conexão. Padrão SingleTon.
 *
 *  @author    Weverton J da Silva.
 *  @copyright 2017 NOME_FANTASIA
 */
class Conn
{

    private static $host = DB_HOST;
    private static $user = DB_USER;
    private static $pass = DB_PASSWORD;
    private static $schema = DB_NAME;

    /** @var PDO */
    private static $connect = null;

    /** Conecta ao banco de dados com o Pattern Singleton
     * Retorna um PDO! */
    private static function Conectar()
    {
        try {
            if (self::$connect == null) :
                $dsn = 'mysql:host=' . self::$host . ';dbname=' . self::$schema;
                $options = [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'];
                self::$connect = new PDO($dsn, self::$user, self::$pass, $options);
            endif;
        } catch (Exception $e) {
            PHPerror($e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
            //            header('Location: ' . BASE . DIRECTORY_SEPARATOR . 'error');
            die;
        }

        self::$connect->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return self::$connect;
    }

    /** Retorna um objeto PDO Singleton Pattern. */
    public static function getConn()
    {
        return self::Conectar();
    }
}
