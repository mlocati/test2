<?php

namespace Concrete\Package\LoginLogoutBlock\Block\LoginLogout;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Editor\LinkAbstractor;
use Concrete\Core\Error\UserMessageException;
use Concrete\Core\Http\ResponseFactoryInterface;
use Concrete\Core\Localization\Localization;
use Concrete\Core\Page\Page;
use Concrete\Core\Url\Resolver\Manager\ResolverManagerInterface;
use Concrete\Core\User\PostLoginLocation;
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
     * @var int
     */
    const WHEREAFTER_DEFAULT = 1;

    /**
     * @var int
     */
    const WHEREAFTER_SAMEPAGE = 2;

    /**
     * @var int
     */
    const WHEREAFTER_SAMEURL = 3;

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
    protected $btInterfaceHeight = 600;

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
     * @var int|string|null
     */
    protected $whereAfterLogin;

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
        return t('Link to login/logout users');
    }

    public function view()
    {
        $me = $this->app->make(User::class);
        $url = '';
        $plainText = '';
        $plainTag = '';
        $rich = '';
        $preLinkAction = null;
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
                if ((int) $this->whereAfterLogin !== static::WHEREAFTER_DEFAULT) {
                    $token = $this->app->make(Token::class);
                    $preLinkAction = [
                        'url' => (string) $this->getActionURL('store_page_for_login'),
                        'params' => '&' . rawurlencode($token::DEFAULT_TOKEN_NAME) . '=' . rawurlencode($token->generate('store_page_for_login')),
                    ];
                }
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
        $this->set('uniqueBlockID', $this->getUniqueBlockID());
        $this->set('preLinkAction', $preLinkAction);
    }

    public function action_store_page_for_login($bID = '')
    {
        if ((int) $this->bID !== (int) $this->bID) {
            return $this->view();
        }
        $token = $this->app->make(Token::class);
        if (!$token->validate('store_page_for_login')) {
            throw new UserMessageException($token->getErrorMessage());
        }
        $postLoginUrl = '';
        switch ((int) $this->whereAfterLogin) {
            case static::WHEREAFTER_SAMEPAGE:
                $page = Page::getCurrentPage();
                if ($page && !$page->isError()) {
                    $postLoginUrl = (string) $this->app->make(ResolverManagerInterface::class)->resolve([$page]);
                }
                break;
            case static::WHEREAFTER_SAMEURL:
                $currentUrl = $this->request->request->get('currentUrl');
                if (is_string($currentUrl)) {
                    $postLoginUrl = $currentUrl;
                }
                break;
        }
        if ($postLoginUrl !== '') {
            $postLoginLocation = $this->app->make(PostLoginLocation::class);
            $postLoginLocation->setSessionPostLoginUrl($postLoginUrl);
        }

        return $this->app->make(ResponseFactoryInterface::class)->json($postLoginUrl !== '');
    }

    public function add()
    {
        $this->loginFormat = static::FORMAT_TEXT;
        $this->whereAfterLogin = static::WHEREAFTER_DEFAULT;
        $this->loginPlainTag = static::DEFAULT_PLAINTEXT_TAG;
        $this->logoutFormat = static::FORMAT_TEXT;
        $this->logoutPlainTag = static::DEFAULT_PLAINTEXT_TAG;

        return $this->edit();
    }

    public function edit()
    {
        $this->set('form', $this->app->make('helper/form'));
        $this->set('editor', $this->app->make('editor'));
        $this->set('formatDictionary', $this->getFormatDictionary());
        $this->set('whereAfterDictionary', $this->getWhereAfterDictionary());
        $this->set('tagDictionary', $this->getTagDictionary());
        $this->set('loginFormat', (int) $this->loginFormat);
        $this->set('whereAfterLogin', (int) $this->whereAfterLogin);
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
    protected function getFormatDictionary()
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
    protected function getWhereAfterDictionary()
    {
        return [
            static::WHEREAFTER_DEFAULT => t('The default page'),
            static::WHEREAFTER_SAMEPAGE => t('This same page'),
            static::WHEREAFTER_SAMEURL => t('This same page (including all parameters)'),
        ];
    }

    /**
     * @return array
     */
    protected function getTagDictionary()
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
        $formatDictionary = array_map('intval', array_keys($this->getFormatDictionary()));
        $whereAfterDictionary = array_map('intval', array_keys($this->getWhereAfterDictionary()));
        $tagDictionary = array_keys($this->getTagDictionary());
        $errors = $this->app->make('helper/validation/error');

        $normalized = [];
        foreach (['loginFormat', 'whereAfterLogin', 'logoutFormat'] as $field) {
            $normalized[$field] = isset($args[$field]) && is_numeric($args[$field]) ? (int) $args[$field] : 0;
        }
        foreach (['loginPlainText', 'loginPlainTag', 'loginRich', 'logoutPlainText', 'logoutPlainTag', 'logoutRich'] as $field) {
            $normalized[$field] = isset($args[$field]) && is_string($args[$field]) ? trim($args[$field]) : '';
        }

        if (!in_array($normalized['loginFormat'], $formatDictionary, true)) {
            $errors->add(t('Please specify the format of the login link'));
        }
        if (!in_array($normalized['whereAfterLogin'], $whereAfterDictionary, true)) {
            if ($normalized['loginFormat'] === static::FORMAT_TEXT || $normalized['loginFormat'] === static::FORMAT_HTML) {
                $errors->add(t('Please specify where to redirect users after the login'));
            } else {
                $normalized['loginFormat'] = static::WHEREAFTER_DEFAULT;
            }
        }
        if (!in_array($normalized['loginPlainTag'], $tagDictionary, true)) {
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

        if (!in_array($normalized['logoutFormat'], $formatDictionary, true)) {
            $errors->add(t('Please specify the format of the logout link'));
        }
        if (!in_array($normalized['logoutPlainTag'], $tagDictionary, true)) {
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

    /**
     * @return int
     */
    protected function getUniqueBlockID()
    {
        $proxyBlock = $this->getBlockObject()->getProxyBlock();

        return (int) ($proxyBlock ? $proxyBlock->getBlockID() : $this->bID);
    }
}
