<?php

$state = Plugin::state('share');
Route::set($state['path'] . '%s%', function($id = "") use($site, $state, $url) {
    $id = urldecode($id);
    $a = require PLUGIN . DS . 'share' . DS . 'lot' . DS . 'state' . DS . 'a.php';
    if (!isset($a[$id]['url'])) {
        Shield::abort(); // Service does not exist
    }
    $path = Request::get('path');
    if (!$path || !$page = File::exist([
        PAGE . DS . $path . '.page',
        PAGE . DS . $path . '.archive',
        // Home page
        PAGE . DS . $site->path . '.page',
        PAGE . DS . $site->path . '.archive'
    ])) {
        Shield::abort(); // Page does not exist
    }
    if ($state['counter']) {
        $f = PAGE . DS . $path . DS . 'share.data';
        $i = e(File::open($f)->read([]));
        $i[$id] = (isset($i[$id]) ? $i[$id] : 0) + 1;
        File::write(To::json($i))->saveTo($f, 0600);
    }
    $page = Page::open($page)->get([
        'title' => $site->title,
        'description' => $site->description,
        'url' => $url . ""
    ]);
    foreach ($page as &$v) $v = trim(strip_tags($v));
    if (isset($a[$id]['fn']) && is_callable($a[$id]['fn'])) {
        $page = call_user_func($a[$id]['fn'], $page);
    }
    foreach ($page as &$v) $v = urlencode($v);
    $v = __replace__($a[$id]['url'], $page);
    if ($id === 'e-mail' || $id === 'whats-app') {
        HTTP::header('Location', $v);
        exit;
    }
    Guardian::kick($v);
});