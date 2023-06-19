<?php

namespace Concrete\Package\LoginLogoutBlock;

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Package\Package;

defined('C5_EXECUTE') or die('Access Denied.');

class Controller extends Package
{
    /**
     * @var string
     */
    protected $pkgHandle = 'login_logout_block';

    /**
     * @var string
     */
    protected $pkgVersion = '0.0.1';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::$appVersionRequired
     */
    protected $appVersionRequired = '8.5.0';

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageName()
     */
    public function getPackageName()
    {
        return t('Login/Logout Block');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::getPackageDescription()
     */
    public function getPackageDescription()
    {
        return t('Add a Login/Logout link to your site');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Package\Package::install()
     */
    public function install()
    {
        $pkg = parent::install();

        BlockType::installBlockType('login_logout', $pkg);
    }
}
