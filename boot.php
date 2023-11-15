<?php

$addon = rex_addon::get('ai');

rex_extension::register('META_NAVI', function (rex_extension_point $ep) {
    $subject = $ep->getSubject();
    array_unshift($subject, '<li><button id="aiMenuBtn"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><g fill="none" stroke="white" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"><path d="M14 3v4a1 1 0 0 0 1 1h4"/><path d="M10 21H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h7l5 5v3.5M9 9h1m-1 4h2.5M9 17h1"/><path d="M14 21v-4a2 2 0 1 1 4 0v4m-4-2h4m3-4v6"/></g></svg></button></li>');

    $ep->setSubject($subject);
});


rex_extension::register('PAGES_PREPARED', static function () {
    if (rex_be_controller::getCurrentPageObject() && rex_be_controller::getCurrentPageObject()->hasLayout() && !rex_be_controller::getCurrentPageObject()->isPopup()) {
        // rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) {
        //     $ep->setSubject(str_replace('<body', '<body x-data="ai"', $ep->getSubject()));
        // });
        rex_extension::register('OUTPUT_FILTER', function (\rex_extension_point $ep) {
            $panel = rex_file::get(rex_path::addon("ai", "templates/modal.html"));

            $ep->setSubject(str_replace('</body>', $panel . '</body>', $ep->getSubject()));
        });
    }
});


rex_view::addCssFile($this->getAssetsUrl("styles.css"));
rex_view::addJsFile($this->getAssetsUrl("dist/ai.js"), [rex_view::JS_IMMUTABLE => false, rex_view::JS_ASYNC => false, rex_view::JS_DEFERED => true]);


// add credentials in backend
if (rex::isBackend()) {
    rex_view::setJsProperty("ai_openaiKey", rex_config::get("ai", "openaiKey"));
    rex_view::setJsProperty("ai_gcpKey", rex_config::get("ai", "gcpKey"));
}
