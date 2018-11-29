//<?php
/**
 * eFavorite
 * 
 * Избранное
 *
 * @author      webber (web-ber12@yandex.ru)
 * @category    snippet
 * @version     0.2
 * @license     http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal    @modx_category Content
 * @internal    @installset base, sample
*/
 
/*
Параметры
&className - имя класса обработчика. По-умолчанию eFavorite - подключается класс eFavorite\eFavorite из файла eFavorite.class.php
&lifetime - время жизни куки. По-умолчанию 2592000 = 30 суток
&elementTotalId - id элемента, в котором отображается общее количество избранных. По-умолчанию - favorits_cnt
&elementClass - класс элемента-кнопки для добавления/удаления из избранного. По-умолчанию - favorite. Данный элемент должен иметь атрибут data-id=docid, для добавления документа
&elementActiveClass - класс активного элемента-кнопки для добавления/удаления из избранного. По-умолчанию - active
&addText - подсказка при наведении на неактивный элемент. По-умолчанию "добавить в избранное"
&removeText - подсказка при наведении на активный элемент. По-умолчанию "удалить из избранного"

интеграция с eFilter
&setDocsForeFilterOnPage - если избранные элементы будут фильтроваться с помощью eFilter, то задать id нужной страницы тут.
&eFilterCallback=`1` - если eFilter вызывается в режиме ajax, не забудьте указать данный параметр

вызов
[!eFavorite!] - где-нибудь в хидере
сниппет установит плейсхолдер [+eFavoriteDocs+], который в дальнейшем можно использовать в выводе [!DocLister? &documents=`[+eFavoriteDocs+]`!]
[!eFavorite? &setDocsForeFilterOnPage=`5`!]
установит дополнительный плейсхолдер [+eFilter_search_ids+] на странице с id=5, который будет использоваться при формировании вывода [!eFilter!]

при необходимости вывода списка - ПОВТОРНО вызвать в нужном месте с обычными параметрами DocLister
[!eFavorite? &tpl=`@CODE:[+pagetitle+]<hr>`!]


для организации нескольких списков - избранное, вишлист, сравнение - можно вызывать несколько сниппетов с разными id и разными параметрами
в хидере
[!eFavorite!] //- для организации избранного
[!eFavorite? &id=`wishlist` &elementTotalId=`total` &elementClass=`wishlist` &addText=`добавить в wishlist` &removeText=`удалить из wishlist` &lifetime=`2592002`!] //- для wishlist

на странице показа избранного дополнительный вызов
[!eFavorite!]

на странице показа wishlist дополнительный вызов
[!eFavorite? &id=`wishlist`!]

*/


$out = '';
$id = isset($id) ? $id :  'favorite';

//повторный вызов - выводим список
$eFavoriteDocs = $modx->getPlaceholder('eFavoriteDocs_' . $id);
if ($eFavoriteDocs && !empty($eFavoriteDocs)) {
    if (isset($params['parents'])) {
        //задан parents - передаем список id как доп.условие - для показа "избранного из категории"
        $params['addWhereList'] = isset($params['addWhereList']) ? $params['addWhereList'] . ' AND c.id IN(' . $eFavoriteDocs . ')' : ' c.id IN(' . $eFavoriteDocs . ') ';
    } else {
        //иначе передаем в documents - для вывода полного списка избранного
        $params['documents'] = $eFavoriteDocs;
    }
    $out .= $modx->runSnippet("DocLister", $params);
    return $out;
}

//иначе - первичный вызов, инициализируем скрипты и плейсхолдеры
$className = isset($params['className']) ? $params['className'] : 'eFavorite';
require_once MODX_BASE_PATH . "assets/snippets/eFavorite/" . $className . ".class.php";
$class = "eFavorite\\" . $className;
$eFavorite = new $class($modx, $params);
$eFavorite->init($id);
$eFavorite->initJS();
$docs = $eFavorite->getDocList();
$modx->setPlaceholder('eFavoriteDocs_' . $id, $docs);
if (isset($params['setDocsForeFilterOnPage']) && $params['setDocsForeFilterOnPage'] == $modx->documentIdentifier) {
    $modx->setPlaceholder('eFilter_search_ids', $docs);
}
return $out;
