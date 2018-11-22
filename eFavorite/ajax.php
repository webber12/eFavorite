<?php
if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {

    define('MODX_API_MODE', true);
    include_once(dirname(__FILE__) . "../../../../index.php");
    $modx->db->connect();
    if (empty($modx->config)) {
        $modx->getSettings();
    }
    $output = '';

    if (isset($_POST['action'])) {
        $action = $modx->db->escape($_POST['action']);
        switch ($action) {
            case 'eFavorite':
                $params = array();
                if (isset($_POST['lifetime'])) {
                    $params['lifetime'] = (int)$_POST['lifetime'];
                }
                $className = isset($_POST['className']) ? $modx->db->escape($_POST['className']) : 'eFavorite';
                $class = "eFavorite\\" . $className;
                require_once $className . ".class.php";
                $eFavorite = new $class($modx, $params);
                $eFavorite->recountFavorites();
                $output .= $eFavorite->getFavoriteDocRows();
            break;
                
            default:
            break;
        }
        echo $output;
    }
exit;
}
exit;
