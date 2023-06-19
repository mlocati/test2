<?php

use Concrete\Core\Editor\CkeditorEditor;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @var Concrete\Core\Block\View\BlockView $view
 * @var Concrete\Package\LoginLogoutBlock\Block\LoginLogout\Controller $controller
 * @var Concrete\Core\Form\Service\Form $form
 * @var Concrete\Core\Editor\EditorInterface $editor
 * @var array $formats
 * @var array $tags
 * @var int $loginFormat
 * @var string $loginPlainText
 * @var string $defaultLoginPlainText
 * @var string $loginPlainTag
 * @var string $loginRich
 * @var int $logoutFormat
 * @var string $logoutPlainText
 * @var string $defaultLogoutPlainText
 * @var string $logoutPlainTag
 * @var string $logoutRich
 */

$generateHtmlEditor = function ($name, $value) use ($editor) {
    if ($editor instanceof CkeditorEditor && method_exists($editor, 'outputEditorWithOptions')) {
        return $editor->outputEditorWithOptions(
            $name,
            [
                'enterMode' => 2, // CKEDITOR.ENTER_BR'
            ],
            $value,
        );
    }

    return $editor->outputBlockEditModeEditor($name, $value);
};

?>
<table class="table">
    <colgroup>
        <col width="50%" />
    </colgroup>
    <thead>
        <tr>
            <th><?= t('Login Link') ?></th>
            <th><?= t('Logout Link') ?></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>
                <div class="form-group">
                    <?= $form->label('loginFormat', t('Format')) ?>
                    <?= $form->select('loginFormat', $formats, $loginFormat) ?>
                </div>
                <div id="loginFormat-plainText"<?= $loginFormat === $controller::FORMAT_TEXT ? '' : ' style="display: none"' ?>>
                    <div class="form-group">
                        <?= $form->label('loginPlainText', t('Text')) ?>
                        <?= $form->text('loginPlainText', $loginPlainText, ['maxlength' => '255', 'placeholder' => t('Default: %s', $defaultLoginPlainText)]) ?>
                    </div>
                    <div class="form-group">
                        <?= $form->label('loginPlainTag', t('HTML Tag')) ?>
                        <?= $form->select('loginPlainTag', $tags, $loginPlainTag) ?>
                    </div>
                </div>
                <div id="loginFormat-righText"<?= $loginFormat === $controller::FORMAT_HTML ? '' : ' style="display: none"' ?>>
                    <div class="form-group">
                        <?= $form->label('loginRich', t('Text')) ?>
                        <?= $generateHtmlEditor('loginRich', $loginRich) ?>
                    </div>
                </div>
            </td>
            <td>
                <div class="form-group">
                    <?= $form->label('logoutFormat', t('Format')) ?>
                    <?= $form->select('logoutFormat', $formats, $logoutFormat) ?>
                </div>
                <div id="logoutFormat-plainText"<?= $logoutFormat === $controller::FORMAT_TEXT ? '' : ' style="display: none"' ?>>
                    <div class="form-group">
                        <?= $form->label('logoutPlainText', t('Text')) ?>
                        <?= $form->text('logoutPlainText', $logoutPlainText, ['maxlength' => '255', 'placeholder' => t('Default: %s', $defaultLogoutPlainText)]) ?>
                    </div>
                    <div class="form-group">
                        <?= $form->label('logoutPlainTag', t('HTML Tag')) ?>
                        <?= $form->select('logoutPlainTag', $tags, $logoutPlainTag) ?>
                    </div>
                </div>
                <div id="logoutFormat-righText"<?= $logoutFormat === $controller::FORMAT_HTML ? '' : ' style="display: none"' ?>>
                    <div class="form-group">
                        <?= $form->label('logoutRich', t('Text')) ?>
                        <?= $generateHtmlEditor('logoutRich', $logoutRich) ?>
                    </div>
                </div>
            </td>
        </tr>
    </tbody>
</table>
<fieldset>
</fieldset>

<script> $(document).ready(function() {

function updateView()
{
    var L = ['login', 'logout'];
    for (var i = 0; i < L.length; i++) {
        var v = parseInt($('#' +  L[i] + 'Format').val());
        $('#' +  L[i] + 'Format-plainText').toggle(v === <?= $controller::FORMAT_TEXT ?>);
        $('#' +  L[i] + 'Format-righText').toggle(v === <?= $controller::FORMAT_HTML ?>);
    }
}

$('#loginFormat,#logoutFormat').on('change', function () { updateView(); });

updateView();

}); </script>
