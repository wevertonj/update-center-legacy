<?php

/**
 * Check.class [ HELPER ]
 * Classe repponsável por manipular e validar os dados do sistema.
 * @copyright (c) 2016, Weverton J. da Silva
 */
class Check
{

    private static $data;
    private static $format;

    /**
     * <b>Verifica E-mail:</b> Executa validação de formato de e-mail. Se for um email válido retorna true, ou retorna false.
     * @param STRING $Email = Uma conta de e-mail
     * @return BOOL = True para um email válido, ou false
     */
    public static function email($email)
    {
        self::$data = (string) $email;
        self::$format = '/[a-z0-9_\.\-]+@[a-z0-9_\.\-]*[a-z0-9_\.\-]+\.[a-z]{2,4}$/';

        if (preg_match(self::$format, self::$data)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * <b>Tranforma URL:</b> Tranforma uma string no formato de URL amigável e retorna o a string convertida!
     * @param STRING $Name = Uma string qualquer
     * @return STRING = $Data = Uma URL amigável válida
     */
    public static function name($name)
    {
        self::$format = array();
        self::$format['a'] = 'ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜüÝÞßàáâãäåæçèéêëìíîïðñòóôõöøùúûýýþÿRr"!@#$%&*()_-+={[}]/?;:.,\\\'<>°ºª';
        self::$format['b'] = 'aaaaaaaceeeeiiiidnoooooouuuuuybsaaaaaaaceeeeiiiidnoooooouuuyybyRr                                 ';

        self::$data = strtr(utf8_decode($name), utf8_decode(self::$format['a']), self::$format['b']);
        self::$data = strip_tags(trim(self::$data));
        self::$data = str_replace(' ', '-', self::$data);
        self::$data = str_replace(array('-----', '----', '---', '--'), '-', self::$data);

        return strtolower(utf8_encode(self::$data));
    }

    /**
     * <b>Tranforma Data:</b> Transforma uma data no formato DD/MM/YY em uma data no formato TIMESTAMP!
     * @param STRING $Name = Data em (d/m/Y) ou (d/m/Y H:i:s)
     * @return STRING = $Data = Data no formato timestamp!
     */
    public static function date($data)
    {
        self::$format = explode(' ', $data);
        self::$data = explode('/', self::$format[0]);

        if (empty(self::$format[1])) {
            self::$format[1] = date('H:i:s');
        }


        self::$data = self::$data[2] . '-' . self::$data[1] . '-' . self::$data[0] . ' ' . self::$format[1];
        return self::$data;
    }

    public static function ValiDate($date)
    {

        $data = explode("/", "$date"); // fatia a string $dat em pedados, usando / como referência
        $d = $data[0];
        $m = $data[1];
        $y = $data[2];

        // verifica se a data é válida!
        // 1 = true (válida)
        // 0 = false (inválida)
        $res = checkdate($m, $d, $y);


        if ($res) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * <b>Limita os Palavras:</b> Limita a quantidade de palavras a serem exibidas em uma string!
     * @param STRING $String = Uma string qualquer
     * @return INT = $Limite = String limitada pelo $Limite
     */
    public static function words($String, $Limite, $Pointer = null)
    {
        self::$data = strip_tags(trim($String));
        self::$format = (int) $Limite;

        $ArrWords = explode(' ', self::$data);
        $NumWords = count($ArrWords);
        $NewWords = implode(' ', array_slice($ArrWords, 0, self::$format));

        $Pointer = (empty($Pointer) ? '...' : ' ' . $Pointer);
        $Result = (self::$format < $NumWords ? $NewWords . $Pointer : self::$data);
        return $Result;
    }

    /**
     * <b>Limita os Caracteres:</b> Limita a quantidade de caracteres a serem exibidas em uma string!
     * @param STRING $String = Uma string qualquer
     * @return INT = $Limite = String limitada pelo $Limite
     */
    public static function characters($String, $Limite, $Pointer = null)
    {
        self::$data = strip_tags(trim($String));
        self::$format = (int) $Limite;

        $NumCharacters = strlen(self::$data);
        $NewCharacters = substr(self::$data, 0, self::$format);

        $Pointer = (empty($Pointer) ? '...' : ' ' . $Pointer);
        $Result = (self::$format < $NumCharacters ? $NewCharacters . $Pointer : self::$data);
        return $Result;
    }

    /**
     * <b>Obter categoria:</b> Informe o name (url) de uma categoria para obter o ID da mesma.
     * @param STRING $category_name = URL da categoria
     * @return INT $category_id = id da categoria informada
     */
    public static function catByName($categoryName)
    {
        $read = new Read;
        $read->exeRead('categories', "WHERE category_name = :name", "name={$categoryName}");
        if ($read->getRowCount()) {
            return $read->getResult()[0]['category_id'];
        } else {
            echo "A categoria {$categoryName} não foi encontrada!";
            die;
        }
    }

    /**
     * <b>Usuários Online:</b> Ao executar este HELPER, ele automaticamente deleta os usuários expirados. Logo depois
     * executa um READ para obter quantos usuários estão realmente online no momento!
     * @return INT = Qtd de usuários online
     */
    public static function userOnline()
    {
        $now = date('Y-m-d H:i:s');
        $deleteUserOnline = new Delete;
        $deleteUserOnline->exeDelete('siteviews_online', "WHERE online_endview <= :now", "now={$now}");

        $readUserOnline = new Read;
        $readUserOnline->exeRead('siteviews_online');
        return $readUserOnline->getRowCount();
    }

    /**
     * <b>Imagem Upload:</b> Ao executar este HELPER, ele automaticamente verifica a existencia da imagem na pasta
     * uploads. Se existir retorna a imagem redimensionada!
     * @return HTML = imagem redimencionada!
     */
    public static function image($imageUrl, $imageDesc, $imageW = null, $imageH = null)
    {
        self::$data = $imageUrl;

        if (file_exists(self::$data) && !is_dir(self::$data)) {
            $patch = BASE;
            $imagem = self::$data;
            return "<img src=\"{$patch}/tim.php?src={$patch}/{$imagem}&w={$imageW}&h={$imageH}\" alt=\"{$imageDesc}\" title=\"{$imageDesc}\"/>";
        } else {
            return false;
        }
    }

    /**
     * <b>Subtraction Date:</b> Realiza a subtração de datas!
     * @return ARRAY = Diferença de ano, mês, dia, entre uma data e outra
     */
    public static function SubDate($initialDate, $endDdate)
    {

        $dateA = new DateTime(date('Y-m-d H:i:s', strtotime($initialDate)));
        $dateB = new DateTime(date('Y-m-d H:i:s', strtotime($endDdate)));
        $dateResult = $dateB->diff($dateA);
        return $dateResult;
    }

    /**
     * <b>Get User IP:</b> Pega o IP do usuário!
     * Font @link http://stackoverflow.com/questions/13646690/how-to-get-real-ip-from-visitor
     */
    public static function getUserIP()
    {
        $client = (isset($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : false);
        $forward = (isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : false);
        $remote = (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : false);
        if (filter_var($client, FILTER_VALIDATE_IP)) {
            $ip = $client;
        } elseif (filter_var($forward, FILTER_VALIDATE_IP)) {
            $ip = $forward;
        } else {
            $ip = $remote;
        }
        return $ip;
    }
}
