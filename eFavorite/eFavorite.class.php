<?php namespace eFavorite;

class eFavorite
{
    public function __construct($modx, $params = array())
    {
        $this->modx = $modx;
        $this->params = $params;
        $this->cookieName = isset($this->params['cookieName']) ? $this->modx->db->escape($this->params['cookieName']) : 'eFavorite';
        $this->lifetime = isset($this->params['lifetime']) ? (int)$this->params['lifetime'] : 60 * 60 * 24 * 30;
        $this->f = $this->getFavorites();
    }
    
    public function getFavorites()
    {
        $tmp = isset($_COOKIE[$this->cookieName]) ? $_COOKIE[$this->cookieName] : '';
        return ($tmp != '') ? json_decode($tmp, TRUE) : array();
    }
    
    public function setFavorites($f = false)
    {
        $f = $f ? $f : $this->f;
        setcookie($this->cookieName, json_encode($f), time() + $this->lifetime, '/');
    }
    
    public function recountFavorites()
    {
        $f = $this->f;
        if (isset($_POST['id']) && (int)$_POST['id'] > 0) {
            $id = (int)$_POST['id'];
            if (!in_array($id, $f)) {
                $f[] = $id;
            } else {
                $key = array_search($id, $f);
                if ($key || $key == 0) {
                    if (count($f) == 1) {
                        $f = array();
                    } else {
                        unset($f[$key]);
                    }
                }
            }
        }
        $this->f = $f;
        $this->setFavorites();
    }

    public function getFavoriteDocRows()
    {
        $output = '';
        if (!empty($this->f) && is_array($this->f)) {//есть что-то в избранном
            $p = array('documents' => implode(',', $this->f), 'sortType' => 'documents', 'JSONformat' => 'new', 'api' => 'id');
            //берем только актуальное - опубликованное, неудаленное на момент просмотра
            $ff = $this->modx->runSnippet("DocLister", $p);
            $output = $ff;
        } else {
            $output = json_encode(array('rows' => array(), 'total' => 0));
        }
        return $output;
    }

    public function getDocList()
    {
        return !empty($this->f) ? implode(',', $this->f) : '1200000000';
    }

    public function initJS($params)
    {
        $script = 'var eFavoriteParams = {};';
        $defaults = array('lifetime' => $this->lifetime);
        $params = array_merge($defaults, $params);
        $js_params = array('addText', 'removeText', 'elementTotalId', 'elementClass', 'elementActiveClass', 'lifetime');
        foreach ($params as $k => $v) {
            if (in_array($k, $js_params)) {
                $script .= 'eFavoriteParams.' . $k . ' = "' . $v . '";';
            }
        }
        $this->modx->regClientScript("<script>" . $script . "</script>", array('plaintext' => true));
        $this->modx->regClientScript("assets/snippets/eFavorite/js/eFavorite.js");
        if (isset($params['eFilterCallback'])) {
            $this->modx->regClientScript("<script>" . 
                "function afterFilterComplete(_form) {
                    $(document).ready(function(){
                        eFavorite.init();
                    })
                }</script>", array('plaintext' => true));
        }
    } 
}
