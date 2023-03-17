<?php

/**
 * Router [ MODEL ]
 * Responsável por realizar a navegação!
 * @author    Weverton J. da Silva <email>
 * @copyright 2018 NOME_FANTASIA
 *
 * @version 2.0
 */
class Router
{

    /** DATA */
    private $Local;
    private $Patch;
    private $Properties;
    private $URI;
    private $Primary;
    private $Secondary;
    private $Param;

    public function __construct()
    {
        $this->Local = strtok(strip_tags(trim(filter_input_fix(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT))), '?');
        $this->Local = ($this->Local ? $this->Local : null);
        $this->URI = $this->Local;
        $this->Local = explode('/', substr($this->Local, 1));
        $this->Primary = (isset($this->Local[0]) ? $this->Local[0] : null);
        $this->Secondary = (isset($this->Local[1]) ? $this->Local[1] : null);
        $this->Param = (isset($this->Local[2]) ? $this->Local[2] : null);

        if (!$this->Primary) {
            require VIEWS_DIR_URI . DIRECTORY_SEPARATOR . 'home.php';
            exit();
        }

        switch ($this->Primary) {
            case 'c':
                $this->check();
                break;
            case 'n':
                $this->notes();
                break;
            case 'd':
                $this->download();
                break;
            case '404':
                $this->notFound();
                break;
            case 'views':
                $this->notFoundRedirect();
                break;
            default:
                $this->setProperty();
                break;
        }
    }

    public function getLocal()
    {
        return $this->Local;
    }

    public function getSecond()
    {
        return $this->Secondary;
    }

    public function getPatch()
    {
        return $this->Patch;
    }

    public function getProperty()
    {
        return $this->Properties;
    }

    /**
     * Consulta tema no banco de dados.
     *
     * @since 2.0
     *
     * @param string $query termo da consulta.
     * @param string $searchBy Opcional. Define em qual coluna deve ser feita consulta.
     * Por padrão é a coluna `id`. Também pode ser passada a coluna `token`;
     *
     * @return array|false Array com as informações do tema ou false, caso não haja resultados.
     */
    public function getTheme($query, $searchBy = 'id')
    {
        $read = new Read();

        if ($searchBy == 'token') {
            $read->exeRead('themes', 'WHERE token = :token', "token={$query}");
        } else {
            $read->exeRead('themes', 'WHERE id = :id', "id={$query}");
        }

        if ($read->getResult()) {
            return $read->getResult()[0];
        } else {
            return false;
        }
    }

    /**
     * ****************************************
     * *********** PRIVATE METHODS ************
     * ****************************************
     */

    /**
     * Gera o jSON da página de checagem de update.
     *
     * @since 1.0
     * @deprecated 2.0
     */
    private function setProperty()
    {
        if (md5('LGDPMKAGOGWAPDHGETUFN12MA196DA') == $this->Primary) {
            if (!$this->Secondary) {
                $this->Secondary = $this->Primary;
                $this->check();
            } else {
                $this->Secondary = $this->Primary;
                $this->notes();
            }

            exit();
        } elseif (md5('LGDPSBLKKUKDSETUD8LS12CUP18A02') == $this->Primary) {
            if (!$this->Secondary) {
                $this->Secondary = $this->Primary;
                $this->check();
            } else {
                $this->Secondary = $this->Primary;
                $this->notes();
            }

            exit();
        } elseif (md5('LGDPSET18MMSUNMIKLJAQ18SAOADFG') == $this->Primary) {
            if (!$this->Secondary) {
                $this->Secondary = $this->Primary;
                $this->check();
            } else {
                $this->Secondary = $this->Primary;
                $this->notes();
            }

            exit();
        } else {
            $this->notFoundRedirect();
        }

        if ($this->getSecond()) {
            if (!require($this->getPatch())) {
                require VIEWS_DIR_URI . DIRECTORY_SEPARATOR . 'home.php';
            }

            exit();
        }

        echo '{
	"version": "' . $this->Properties['version'] . '",
	"details_url": "' . $this->Properties['details'] . '",
	"download_url": "' . $this->Properties['download'] . '",
        "author_homepage": "site"
        }';
    }

    /**
     * Gera o jSON da página de checagem de update.
     *
     * @since 2.0
     */
    private function check()
    {
        $theme = $this->getTheme($this->Secondary, 'token');

        if ($theme) {
            $read = new Read();
            $read->exeRead('updates', 'WHERE theme_id = :id AND version = :version ORDER BY id DESC', "id={$theme['id']}&version={$theme['version']}");

            if ($read->getResult()) {
                $download = BASE . "/d/{$read->getResult()[0]['token']}";
            } elseif (file_exists(VIEWS_DIR_URI . "/{$theme['slug']}/{$theme['slug']}-v" . str_replace('.', '-', $theme['version']) . ".zip")) {

                $new_download = array(
                    'theme_id' => $theme['id'],
                    'version' => $theme['version'],
                    'token' => $this->generateUpdateToken(),
                    'downloads' => 0,
                );

                $create = new Create();
                $create->exeCreate('updates', $new_download);

                $download = BASE . "/d/{$new_download['token']}";
            } else {
                $download = 'Link expirado!';
            }

            if (isset($theme['description']) && !empty($theme['description'])) {
                $check = array(
                    'name' => $theme['name'],
                    'version' => $theme['version'],
                    'requires' => '4.0',
                    'tested' => '5.3',
                    'details_url' => BASE . "/n/{$this->Secondary}",
                    'download_url' => $download,
                    'author_homepage' => 'site',
                    'sections' => array(
                        'description' => $theme['description']
                    )
                );
            } else {
                $check = array(
                    'version' => $theme['version'],
                    'details_url' => BASE . "/n/{$this->Secondary}",
                    'download_url' => $download,
                    'author_homepage' => 'site',
                );
            }

            echo json_encode($check, JSON_UNESCAPED_SLASHES);
            return true;
        } else {
            $subject = "Tentativa de acesso a token inválido";
            $message = "<b>Token:</b> {$this->Secondary} <br/>";
            $this->sendMail($subject, $message);

            $this->notFoundRedirect();
        }
    }

    /**
     * Carrega a página Notas de Lançamento.
     *
     * @since 2.0
     */
    private function notes()
    {
        $theme = $this->getTheme($this->Secondary, 'token');

        if ($theme) {
            $site_url = $theme['url'];

            if ($theme['slug'] != 'base') {
                $subject = "Acesso a página de detalhes do tema {$theme['name']}";
                $message = "<b>Tema:</b> {$theme['name']} <br/>";
                $message .= "<b>Versão:</b> {$theme['version']} <br/>";
                $message .= "<b>Token:</b> {$this->Secondary} <br/>";
                $this->sendMail($subject, $message);
            }

            require VIEWS_DIR_URI . "/{$theme['slug']}/details.php";
        } else {
            $this->notFoundRedirect();
        }
    }

    /**
     * Carrega a página de download.
     *
     * @since 2.0
     */
    private function download()
    {
        $read = new Read();
        $read->exeRead('updates', 'WHERE token = :token ORDER BY id DESC', "token={$this->Secondary}");

        if ($read->getResult()) {
            $update = $read->getResult()[0];
            $theme = $this->getTheme($update['theme_id'], 'id');

            if ($theme) {
                $file = VIEWS_DIR_URI . "/{$theme['slug']}/{$theme['slug']}-v" . str_replace('.', '-', $theme['version']) . ".zip";

                if ($update['downloads'] < 2 && file_exists($file)) {

                    $file_name = basename($file);

                    header("Content-Type: application/zip");
                    header("Content-Disposition: attachment; filename=$file_name");
                    header("Content-Length: " . filesize($file));
                    readfile($file);
                } else {
                    echo 'Link expirado!';
                }

                $update['downloads']++;
                $count = new Update();
                $count->exeUpdate('updates', $update, 'WHERE id = :id', "id={$update['id']}");

                $subject = "Download do Tema {$theme['name']}";
                $message = "<b>Tema:</b> {$theme['name']} <br/>";
                $message .= "<b>Versão:</b> {$theme['version']} <br/>";
                $message .= "<b>Downloads:</b> {$update['downloads']} <br/>";
                $message .= "<b>Token:</b> {$this->Secondary} <br/>";
                $this->sendMail($subject, $message);
            } else {
                $delete = new Delete();
                $delete->exeDelete('updates', 'WHERE id = :id', $update['id']);

                $subject = 'Link de download sem tema associado';
                $message = "<p>O link foi removido de updates.<p>";
                $message .= "<b>Token:</b> {$this->Secondary} <br/>";
                $message .= "<b>Theme ID:</b> {$update['theme_id']} <br/>";
                $this->sendMail($subject, $message);

                $this->notFoundRedirect();
            }
        } else {
            $subject = 'Tentativa de download com token inválido';
            $message = "<b>Token:</b> {$this->Secondary} <br/>";
            $this->sendMail($subject, $message);

            $this->notFoundRedirect();
        }
    }

    /**
     * Processa o redirecionamento para a página 404.
     *
     * @since 2.0
     */
    private function notFoundRedirect()
    {
        switch ($this->Primary) {
            case '8cd0fdac1b6023a4f0ced3c149586e22':
            case '0eedb23364575a37f606700581cd198a':
            case 'e2344bd18261a4e3cc054eee38c98c50':
            case 'd41d8cd98f00b204e9800998ecf8427e':
            case '3f4127f3d3e5103a624510104491f9ac':
            case '90edfbd4ff9e826964d15940bd3b48f7':
                break;

            default:
                $subject = 'Update Center: Página Não Encontrada';
                $message = "<b>Página:</b> " . strip_tags(trim(filter_input_fix(INPUT_SERVER, 'REQUEST_URI', FILTER_DEFAULT))) . " <br/>";
                $this->sendMail($subject, $message);
                break;
        }

        header('Location:' . BASE . '/404');
        exit();
    }

    /**
     * Renderiza a página 404.
     *
     * @since 2.0
     */
    private function notFound()
    {
        http_response_code(404);
        require VIEWS_DIR_URI . '/404.php';
    }

    /**
     * Faz o envio de email de notificação.
     *
     * @since 2.0
     */
    private function sendMail($subject, $text)
    {
        $text .= "<b>Data:</b> " . date('d/m/Y H:i:s') . '<br/>';

        if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) {
            $text .= "<b>IP:</b> {$_SERVER["HTTP_CF_CONNECTING_IP"]}";
        } else {
            $text .= "<b>IP:</b> {$_SERVER["REMOTE_ADDR"]}";
        }

        if (isset($_SERVER["HTTP_CF_IPCOUNTRY"])) {
            $text .= "<br/><b>País:</b> {$_SERVER["HTTP_CF_IPCOUNTRY"]}";
        }

        $text .= "<br/><b>user Agent:</b> {$_SERVER['HTTP_USER_AGENT']}";

        $SendMail = new GetMailer;
        $SendMail->send('ADDRESS_TO', $subject, $text);
    }

    /**
     * Gera o token de downlod.
     *
     * @since 2.0.1
     */
    private function generateUpdateToken()
    {
        $seed = array(
            0 => 'A', 1 => 'B', 2 => 'C', 3 => 'D', 4 => 'E', 5 => 'F', 6 => 'G', 7 => 'H',
            8 => 'I', 9 => 'J', 10 => 'K', 11 => 'L', 12 => 'M', 13 => 'N', 14 => 'O',
            15 => 'P', 16 => 'Q', 17 => 'R', 18 => 'S', 19 => 'T', 20 => 'U', 21 => 'V',
            22 => 'W', 23 => 'X', 24 => 'Y', 25 => 'Z',
        );
        shuffle($seed);

        $block = 'UPDTE';
        $block .= $seed[rand(0, 25)] . date('y') . date('m');
        $block .= $seed[rand(0, 25)] . $seed[rand(0, 25)] . $seed[rand(0, 25)] . $seed[rand(0, 25)] . $seed[rand(0, 25)];
        $block .= date('i') . $seed[rand(0, 25)] . rand(0, 9) . rand(0, 9);

        $encrypt = new getPass();

        return $encrypt->encrypt($block);
    }
}
