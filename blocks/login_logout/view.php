<?php

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Package\LoginLogoutBlock\Block\LoginLogout\Controller $controller
 * @var bool $editMode
 * @var string $operation 'login' or 'logout'
 * @var int $format See $controller::FORMAT_... constants
 * @var string $url
 * @var string $plainText
 * @var string $plainTag
 * @var string $rich
 * @var int $uniqueBlockID
 * @var array|null $preLinkAction
 */

switch ($format) {
    case $controller::FORMAT_TEXT:
        echo '<', $plainTag, ' class="login-logout-link login-logout-link-', $operation, '"><a id="login-logout-block-', $uniqueBlockID, '" href="', h($url), '">', h($plainText), '</a></', $plainTag, '>';
        break;
    case $controller::FORMAT_HTML:
        echo '<a href="', h($url), '" class="login-logout-link login-logout-link-', $operation, '" id="login-logout-block-', $uniqueBlockID, '">', $rich, '</a>';
        break;
    default:
        if ($editMode) {
            /** @var Concrete\Core\Localization\Localization $localization */
            $localization->withContext($localization::CONTEXT_UI, static function () {
                ?>
                <div class="ccm-edit-mode-disabled-item">
                    <?= t('Login/Logout Block') ?>
                </div>
                <?php
            });
        }
        break;
}

if ($preLinkAction !== null) {
    ?>
<script>(function() {
'use strict';

function doPost(postUrl) {
    var responseReceived = false;
    var xhr = new XMLHttpRequest()
    xhr.open('POST', <?= json_encode($preLinkAction['url']) ?>, true);
    xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    xhr.onerror = xhr.onload = function () {
        if (!responseReceived) {
            responseReceived = true;
            window.location.href = postUrl;
        }
    }
    xhr.send('currentUrl=' + window.encodeURI(window.location.href) + <?= json_encode($preLinkAction['params']) ?>);
}

function hook() {
    var link;
    try {
        link = window.document.getElementById('login-logout-block-<?= $uniqueBlockID ?>');
    } catch (_) {
    }
    if (!link) {
        return setTimeout(function() { hook(); }, 100);
    }
    if (!window.XMLHttpRequest) {
        return;
    }
    link.addEventListener('click', function (e) {
        doPost(link.href);
        if (e.preventDefault) {
            e.preventDefault();
        }
        return e.returnValue = false;
    });
}
hook();

})();</script>
    <?php
}
