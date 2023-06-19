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
 */

switch ($format) {
    case $controller::FORMAT_TEXT:
        echo '<', $plainTag, ' class="login-logout-link login-logout-link-', $operation, '"><a href="', h($url), '">', h($plainText), '</a></', $plainTag, '>';
        break;
    case $controller::FORMAT_HTML:
        echo '<a href="', h($url), '" class="login-logout-link login-logout-link-', $operation, '">', $rich, '</a>';
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
