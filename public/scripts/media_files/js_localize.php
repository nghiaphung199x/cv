<?php

require "core/autoload.php";
if (function_exists('set_magic_quotes_runtime'))
    @set_magic_quotes_runtime(false);
$input = new input();
if (!isset($input->get['lng']) || ($input->get['lng'] == 'en')) {
    header("Content-Type: text/javascript");
    die;
}
$file = "lang/" . $input->get['lng'] . ".php";
$files = dir::content("lang", array(
    'types' => "file",
    'pattern' => '/^.*\.php$/'
));
if (!in_array($file, $files)) {
    header("Content-Type: text/javascript");
    die;
}
$mtime = @filemtime($file);
if ($mtime) httpCache::checkMTime($mtime);
require $file;
header("Content-Type: text/javascript; charset={$lang['_charset']}");
foreach ($lang as $english => $native)
    if (substr($english, 0, 1) != "_")
        echo "browser.labels['" . text::jsValue($english) . "']=\"" . text::jsValue($native) . "\";";

?>
