<?php

/**
 * GetMailer [ HELPER ]
 * Faz envio de emails SMTP.
 * @author    Weverton J. da Silva <email>
 * @copyright 2018 NOME_FANTASIA
 * 
 * @version 1.0.1
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require(__DIR__ . '/PHPMailer/src/Exception.php');
require(__DIR__ . '/PHPMailer/src/PHPMailer.php');
require(__DIR__ . '/PHPMailer/src/SMTP.php');

class GetMailer
{

    private $Result;
    private $SendError;
    // Sender
    private $Host;
    private $User;
    private $Pass;
    private $Port;
    private $Name;
    private $Account;
    // Primary SMTP
    private $PrimaryHost;
    private $PrimaryUser;
    private $PrimaryPass;
    private $PrimaryPort;
    // Email
    private $Email;
    private $Subject;
    private $Body;

    public function __construct()
    {
        $this->SendError = false;

        $this->setSMTP();
    }

    /**
     * @return string Obtém o resultado da última solicitação, se houver.
     */
    public function getResult()
    {
        return $this->Result;
    }

    /**
     * Configura a conta SMTP que fará o envio.
     * 
     * @since 1.0.0
     * 
     * @param array Opcional. $args {
     *      @type string    $host       Endereço do SMTP Host
     *      @type string    $user       E-mail que fará o envio.
     *      @type string    $password   Senha da conta de e-mail que fará o envio.
     *      @type string    $port       Porta da conexão.
     *      Por padrão a conta é definida como primária
     * }
     * 
     * @return bool Retorna <b>verdadeiro</b> em caso de sucesso e <b>falso</b> em caso de erro.
     */
    public function setSMTP($args = '')
    {
        if ($args && !is_array($args)) {
            $this->Result = '$args deve ser um array ou ser um campo vazio';
            return false;
        } elseif (!$args) {
            $this->setPrimarySMTP();
        } else {
            if (!$args['host'] || !$args['user'] || !$args['password'] || !$args['port']) {
                $this->Result = "Os índices 'host', 'user', 'password' e 'port' são obrigatórios em \$args";
                return false;
            }

            $this->PrimaryHost = $args['host'];
            $this->PrimaryUser = $args['user'];
            $this->PrimaryPass = $args['password'];
            $this->PrimaryPort = $args['port'];
        }

        $this->Account = 'primary';

        $this->Host = $this->PrimaryHost;
        $this->User = $this->PrimaryUser;
        $this->Pass = $this->PrimaryPass;
        $this->Port = $this->PrimaryPort;
    }

    /**
     * Processa o envio da mensagem.
     * 
     * @since 1.0.0
     * 
     * @param string | array $$email E-mail do destinatário.
     * @param string $subject Assunto.
     * @param string $message Mensagem.
     * @param string $from_name Nome de quem está enviando a mensagem.
     * 
     * @return bool Retorna <b>verdadeiro</b> em caso de sucesso e <b>falso</b> em caso de erro.
     */
    public function send($email, $subject, $message, $from_name = 'Update Center')
    {
        $this->Email = $email;
        $this->Subject = (string) $subject;
        $this->Body = $message;
        $this->Name = (string) $from_name;

        $mail = $this->sendMail();

        if ($mail) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */
    private function setPrimarySMTP()
    {
        $this->PrimaryHost = MAIL_HOST;
        $this->PrimaryUser = MAIL_USER;
        $this->PrimaryPass = MAIL_PASS;
        $this->PrimaryPort = MAIL_PORT;
    }

    private function sendMail()
    {
        $mail = new PHPMailer(true);
        try {
            $mail->CharSet = 'UTF-8';
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $this->Host;
            $mail->SMTPAuth = true;
            $mail->SMTPSecure = 'tls';
            //            $mail->SMTPAutoTLS = false;
            $mail->Username = $this->User;
            $mail->Password = $this->Pass;
            $mail->Port = $this->Port;
            //Recipients
            $mail->setFrom($this->User, $this->Name);

            if (is_array($this->Email)) {
                for ($i = 0; $i < count($this->Email); $i++) {
                    $mail->addAddress(str_replace(' ', '', $this->Email[$i]));
                }
            } else {
                $mail->addAddress(str_replace(' ', '', $this->Email));
            }

            //Content
            $mail->isHTML(true);
            $mail->Subject = $this->Subject;
            $mail->Body = $this->Body;
            $mail->AltBody = preg_replace("/<([^>]+)>/i", "", $this->Body);

            $mail->send();
            $mail = null;
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
