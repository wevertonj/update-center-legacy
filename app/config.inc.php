<?php

define('BASE', 'UPDATE_BASE');
define('VIEWS_DIR_URI', './views');

// DEFINE O BANCO DE DADOS ##################
define('DB_HOST', 'localhost');
define('DB_NAME', 'DATABASE');
define('DB_USER', 'USER');
define('DB_PASSWORD', 'PASSWORD');

// DEFINE SERVIDOR DE E-MAIL ##################
define('MAIL_USER', 'USER');
define('MAIL_PASS', 'PASSWORD');
define('MAIL_PORT', '587');
define('MAIL_HOST', 'HOST');

// CLASS AUTOLOAD ##################
spl_autoload_register(function ($Class) {

    $cDir = ['Conn', 'Models', 'Helpers'];
    $iDir = null;

    foreach ($cDir as $dirName) {
        if (!$iDir && file_exists(__DIR__ . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . "{$Class}.class.php") && !is_dir(__DIR__ . DIRECTORY_SEPARATOR . $dirName . "{$Class}.class.php")) {
            include_once(__DIR__ . DIRECTORY_SEPARATOR . $dirName . DIRECTORY_SEPARATOR . "{$Class}.class.php");
            $iDir = true;
        }
    }


    if (!$iDir) {
        trigger_error("não foi possível incluir {$Class}.class.php<br>", E_USER_ERROR);
        die;
    }
});

//PHPerror :: Custom PHP Error Message
function PHPerror($erroNum, $erroMsg, $erroFile, $erroLine)
{
    $subject = "[System Error] [{$erroNum}] {$erroMsg}";
    $text = "<b>Erro na linha {$erroLine} ::</b> {$erroMsg}<br><small>{$erroFile}</small>";

    $SendMail = new GetMailer;
    $SendMail->send('ADDRESS_TO', $subject, $text);

    if ($erroNum == E_USER_ERROR) {
        die;
    }
}

set_error_handler('PHPerror');

function filter_input_fix($type, $variable_name, $filter = FILTER_DEFAULT, $options = NULL)
{
    $checkTypes = [
        INPUT_GET,
        INPUT_POST,
        INPUT_COOKIE
    ];

    if ($options === NULL) {
        // No idea if this should be here or not
        // Maybe someone could let me know if this should be removed?
        $options = FILTER_NULL_ON_FAILURE;
    }

    if (in_array($type, $checkTypes) || filter_has_var($type, $variable_name)) {
        return filter_input($type, $variable_name, $filter, $options);
    } else if ($type == INPUT_SERVER && isset($_SERVER[$variable_name])) {
        return filter_var($_SERVER[$variable_name], $filter, $options);
    } else if ($type == INPUT_ENV && isset($_ENV[$variable_name])) {
        return filter_var($_ENV[$variable_name], $filter, $options);
    } else {
        return NULL;
    }
}
