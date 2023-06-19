<?php

namespace Concrete\Package\LoginLogoutBlock\Block\LoginLogout;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Core\User\User;
use Concrete\Core\Validation\CSRF\Token;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends BlockController
{
    /**
     * @var int
     */
    const FORMAT_NONE = 1;

    /**
     * @var int
     */
    const FORMAT_TEXT = 2;

    /**
     * @var int
     */
    const FORMAT_HTML = 3;

    /**
     * @var string
     */
    const DEFAULT_PLAINTEXT_TAG = 'span';

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$helpers
     */
    protected $helpers = [];

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btInterfaceWidth
     */
    protected $btInterfaceWidth = 800;

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btInterfaceHeight
     */
    protected $btInterfaceHeight = 400;

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btCacheBlockOutput
     */
    protected $btCacheBlockOutput = true;

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btCacheBlockOutputOnPost
     */
    protected $btCacheBlockOutputOnPost = true;

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btCacheBlockOutputForRegisteredUsers
     */
    protected $btCacheBlockOutputForRegisteredUsers = false;

    /**
     * {@inheritdoc} see \Concrete\Core\Block\BlockController::$btTable
     */
    protected $btTable = 'btLoginLogout';

    /**
     * @var int|string|null
     */
    protected $loginFormat;

    /**
     * @var string|null
     */
    protected $loginPlainText;

    /**
     * @var string|null
     */
    protected $loginPlainTag;

    /**
     * @var string|null
     */
    protected $loginRich;

    /**
     * @var int|string|null
     */
    protected $logoutFormat;

    /**
     * @var string|null
     */
    protected $logoutPlainText;

    /**
     * @var string|null
     */
    protected $logoutPlainTag;

    /**
     * @var string|null
     */
    protected $logoutRich;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeName()
     */
    public function getBlockTypeName()
    {
        return t('Login/Logout');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::getBlockTypeDescription()
     */
    public function getBlockTypeDescription()
    {
        return t('Adds a Login/Logout block type');
    }

    public function view()
    {
        $me = $this->app->make(User::class);
        $url = '';
        $plainText = '';
        $plainTag = '';
        $rich = '';
        if ($me->isRegistered()) {
            $operation = 'logout';
            $format = (int) $this->logoutFormat;
            if ($format !== static::FORMAT_NONE) {
                $token = $this->app->make(Token::class);
                $url = (string) $this->app->make('url/manager')->resolve(['/login', 'do_logout', $token->generate('do_logout')]);
            }
            switch ($format) {
                case static::FORMAT_TEXT:
                    $plainText = (string) $this->logoutPlainText;
                    if ($plainText === '') {
                        $plainText = $this->getDefaultLogoutPlainText();
                    }
                    $plainTag = (string) $this->logoutPlainTag;
                    break;
                case static::FORMAT_HTML:
                    $rich = LinkAbstractor::translateFrom((string) $this->logoutRich);
                    break;
            }
        } else {
            $operation = 'login';
            $format = (int) $this->loginFormat;
            if ($format !== static::FORMAT_NONE) {
                $url = (string) $this->app->make('url/manager')->resolve(['/login']);
            }
            switch ($format) {
                case static::FORMAT_TEXT:
                    $plainText = (string) $this->loginPlainText;
                    if ($plainText === '') {
                        $plainText = $this->getDefaultLoginPlainText();
                    }
                    $plainTag = (string) $this->loginPlainTag;
                    break;
                case static::FORMAT_HTML:
                    $rich = LinkAbstractor::translateFrom((string) $this->loginRich);
                    break;
            }
        }
        $page = Page::getCurrentPage();
        $editMode = $page && !$page->isError() && $page->isEditMode();
        $this->set('editMode', $editMode);
        if ($editMode) {
            $this->set('localization', $this->app->make(Localization::class));
        }
        $this->set('operation', $operation);
        $this->set('format', $format);
        $this->set('url', $url);
        $this->set('plainText', $plainText);
        $this->set('plainTag', $plainTag);
        $this->set('rich', $rich);
    }

    public function add()
    {
        $this->loginFormat = static::FORMAT_TEXT;
        $this->loginPlainTag = static::DEFAULT_PLAINTEXT_TAG;
        $this->logoutFormat = static::FORMAT_TEXT;
        $this->logoutPlainTag = static::DEFAULT_PLAINTEXT_TAG;

        return $this->edit();
    }

    public function edit()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('editor', $this->app->make('editor'));
        $this->set('formats', $this->getFormatsDictionary());
        $this->set('tags', $this->getTagsDictionary());
        $this->set('loginFormat', (int) $this->loginFormat);
        $this->set('loginPlainText', (string) $this->loginPlainText);
        $this->set('defaultLoginPlainText', $this->getDefaultLoginPlainText());
        $this->set('loginPlainTag', (string) $this->loginPlainTag);
        $this->set('loginRich', LinkAbstractor::translateFromEditMode((string) $this->loginRich));
        $this->set('logoutFormat', (int) $this->logoutFormat);
        $this->set('logoutPlainText', (string) $this->logoutPlainText);
        $this->set('defaultLogoutPlainText', $this->getDefaultLogoutPlainText());
        $this->set('logoutPlainTag', (string) $this->logoutPlainTag);
        $this->set('logoutRich', LinkAbstractor::translateFromEditMode((string) $this->logoutRich));
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::validate()
     */
    public function validate($args)
    {
        $check = $this->normalizeArgs(is_array($args) ? $args : []);

        return is_array($check) ? true : $check;
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Block\BlockController::save()
     */
    public function save($args)
    {
        $normalized = $this->normalizeArgs(is_array($args) ? $args : []);
        if (!is_array($normalized)) {
            throw new UserMessageException(implode("\n", $normalized->getList()));
        }

        return parent::save($normalized);
    }

    /**
     * @return string
     */
    protected function getDefaultLoginPlainText()
    {
        return t('Login');
    }

    /**
     * @return string
     */
    protected function getDefaultLogoutPlainText()
    {
        return t('Logout');
    }

    /**
     * @return array
     */
    protected function getFormatsDictionary()
    {
        return [
            static::FORMAT_NONE => t('Hidden'),
            static::FORMAT_TEXT => t('Plain Text'),
            static::FORMAT_HTML => t('Rich Text'),
        ];
    }

    /**
     * @return array
     */
    protected function getTagsDictionary()
    {
        $result = [];
        foreach (['span', 'div', 'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6'] as $tag) {
            $result[$tag] = tc('HTML', '%s HTML tag', "<{$tag}>");
        }

        return $result;
    }

    /**
     * @param array $args
     *
     * @return \Concrete\Core\Error\Error|\Concrete\Core\Error\ErrorList\ErrorList|array
     */
    protected function normalizeArgs(array $args)
    {
        $formats = array_map('intval', array_keys($this->getFormatsDictionary()));
        $tags = array_keys($this->getTagsDictionary());
        $errors = $this->app->make('helper/validation/error');

        $normalized = [];
        foreach (['loginFormat', 'logoutFormat'] as $field) {
            $normalized[$field] = isset($args[$field]) && is_numeric($args[$field]) ? (int) $args[$field] : 0;
        }
        foreach (['loginPlainText', 'loginPlainTag', 'loginRich', 'logoutPlainText', 'logoutPlainTag', 'logoutRich'] as $field) {
            $normalized[$field] = isset($args[$field]) && is_string($args[$field]) ? trim($args[$field]) : '';
        }

        if (!in_array($normalized['loginFormat'], $formats, true)) {
            $errors->add(t('Please specify the format of the login link'));
        }
        if (!in_array($normalized['loginPlainTag'], $tags, true)) {
            if ($normalized['loginFormat'] === static::FORMAT_TEXT) {
                $errors->add(t('Please specify the HTML tag for the login link'));
            } else {
                $normalized['loginPlainTag'] = static::DEFAULT_PLAINTEXT_TAG;
            }
        }
        if ($normalized['loginRich'] === '') {
            if ($normalized['loginFormat'] === static::FORMAT_HTML) {
                $errors->add(t('Please specify the rich text for the login link'));
            }
        } else {
            $normalized['loginRich'] = LinkAbstractor::translateTo($normalized['loginRich']);
        }

        if (!in_array($normalized['logoutFormat'], $formats, true)) {
            $errors->add(t('Please specify the format of the logout link'));
        }
        if (!in_array($normalized['logoutPlainTag'], $tags, true)) {
            if ($normalized['logoutFormat'] === static::FORMAT_TEXT) {
                $errors->add(t('Please specify the HTML tag for the logout link'));
            } else {
                $normalized['logoutPlainTag'] = static::DEFAULT_PLAINTEXT_TAG;
            }
        }
        if ($normalized['logoutRich'] === '') {
            if ($normalized['logoutFormat'] === static::FORMAT_HTML) {
                $errors->add(t('Please specify the rich text for the logout link'));
            }
        } else {
            $normalized['logoutRich'] = LinkAbstractor::translateTo($normalized['logoutRich']);
        }

        return $errors->has() ? $errors : $normalized;
    }
}
