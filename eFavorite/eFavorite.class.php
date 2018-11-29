<?php namespace eFavorite;

class eFavorite
{
    public function __construct($modx, $params = array())
    {
        $this->modx = $modx;
        $this->params = $params;
    }

    public function init($id = false)
    {
        if ($id) {
            $this->id = $id;
        } else {
            $this->id = isset($this->params['id']) ? $this->modx->db->escape($this->params['id']) : 'favorite';
        }
        $this->cookieName = 'eFavorite_' . $this->id;
        $this->f = $this->getFavorites();
        $this->lifetime = isset($this->params['lifetime']) ? (int)$this->params['lifetime'] : 60 * 60 * 24 * 30;
        return $this;
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
        if (isset($_POST['docid']) && (int)$_POST['docid'] > 0) {
            $docid = (int)$_POST['docid'];
            if (!in_array($docid, $f)) {
                $f[] = $docid;
            } else {
                if (count($f) == 1) {
                        $f = array();
                } else {
                    $key = array_search($docid, $f);
                    unset($f[$key]);
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
        return !empty($this->f) ? implode(',', $this->f) : '4294967295';
    }

    public function initJS()
    {
        $params = $this->params;
        if (!isset($this->modx->loadedjscripts['efavorite'])) {
            //prevent double loading
            $this->modx->regClientScript("assets/snippets/eFavorite/js/eFavorite.js", array("name" => "efavorite"));
        }
        $script = "var eFavorite_" . $this->id . " = new eFavorite();" . PHP_EOL;
        $defaults = array('lifetime' => $this->lifetime, 'id' => $this->id);
        $params = array_merge($defaults, $params);
        $js_params = array('addText', 'removeText', 'elementTotalId', 'elementClass', 'elementActiveClass', 'lifetime', 'className', 'id');
        foreach ($params as $k => $v) {
            if (in_array($k, $js_params)) {
                $script .= 'eFavorite_' . $this->id .'.params.' . $k . ' = "' . $v . '";' . PHP_EOL;
            }
        }
        $this->modx->regClientScript("<script>" . $script . "$(document).ready(function(){eFavorite_" . $this->id . ".init()})</script>", array('plaintext' => true));
        
        if (isset($params['eFilterCallback'])) {
            $this->modx->regClientScript("<script>" . 
                "function afterFilterComplete(_form) {
                    $(document).ready(function(){
                        eFavorite_" . $this->id . ".init();
                    })
                }</script>", array('plaintext' => true));
        }
    } 
}
