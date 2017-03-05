<?php

namespace Nip\Router;

use Nip\Container\ServiceProviders\Providers\AbstractSignatureServiceProvider;
use Nip\Container\ServiceProviders\Providers\BootableServiceProviderInterface;

/**
 * Class MailServiceProvider
 * @package Nip\Mail
 */
class RoutesServiceProvider extends AbstractSignatureServiceProvider implements BootableServiceProviderInterface
{

    /**
     * @inheritdoc
     */
    public function register()
    {
    }

    /**
     * @inheritdoc
     */
    public function boot()
    {
        $this->loadRoutes();
    }

    public function loadRoutes()
    {
    }

    /**
     * @inheritdoc
     */
    public function provides()
    {
        return [];
    }
}
