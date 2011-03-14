<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Bundle\SecurityBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpFoundation\RequestMatcher;

/**
 * SecurityExtension.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class SecurityExtension extends Extension
{
    protected $requestMatchers = array();
    protected $contextListeners = array();
    protected $listenerPositions = array('pre_auth', 'form', 'http', 'remember_me');
    protected $configuration;
    protected $factories;

    public function __construct()
    {
        $this->configuration = new Configuration();
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        if (!array_filter($configs)) {
            return;
        }

        $processor = new Processor();

        // first assemble the factories
        $factories = $this->createListenerFactories($container, $processor->process($this->configuration->getFactoryConfigTree(), $configs));

        // normalize and merge the actual configuration
        $tree = $this->configuration->getMainConfigTree($factories);
        $config = $processor->process($tree, $configs);

        // load services
        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config', __DIR__.'/Resources/config')));
        $loader->load('security.xml');
        $loader->load('security_listeners.xml');
        $loader->load('security_rememberme.xml');
        $loader->load('templating_php.xml');
        $loader->load('templating_twig.xml');
        $loader->load('collectors.xml');

        // set some global scalars
        $container->setParameter('security.access.denied_url', $config['access_denied_url']);
        $container->setParameter('security.authentication.session_strategy.strategy', $config['session_fixation_strategy']);

        $this->createFirewalls($config, $container);
        $this->createAuthorization($config, $container);
        $this->createRoleHierarchy($config, $container);

        if ($config['encoders']) {
            $this->createEncoders($config['encoders'], $container);
        }

        // load ACL
        if (isset($config['acl'])) {
            $this->aclLoad($config['acl'], $container);
        }

        // add some required classes for compilation
        $this->addClassesToCompile(array(
            'Symfony\\Component\\Security\\Http\\Firewall',
            'Symfony\\Component\\Security\\Http\\FirewallMapInterface',
            'Symfony\\Component\\Security\\Core\\SecurityContext',
            'Symfony\\Component\\Security\\Core\\SecurityContextInterface',
            'Symfony\\Component\\Security\\Core\\User\\UserProviderInterface',
            'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationProviderManager',
            'Symfony\\Component\\Security\\Core\\Authentication\\AuthenticationManagerInterface',
            'Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManager',
            'Symfony\\Component\\Security\\Core\\Authorization\\AccessDecisionManagerInterface',
            'Symfony\\Component\\Security\\Core\\Authorization\\Voter\\VoterInterface',

            'Symfony\\Bundle\\SecurityBundle\\Security\\FirewallMap',
            'Symfony\\Bundle\\SecurityBundle\\Security\\FirewallContext',

            'Symfony\\Component\\HttpFoundation\\RequestMatcher',
            'Symfony\\Component\\HttpFoundation\\RequestMatcherInterface',
        ));
    }

    protected function aclLoad($config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(array(__DIR__.'/../Resources/config', __DIR__.'/Resources/config')));
        $loader->load('security_acl.xml');

        if (isset($config['connection'])) {
            $container->setAlias('security.acl.dbal.connection', sprintf('doctrine.dbal.%s_connection', $config['connection']));
        }

        if (isset($config['cache'])) {
            $container->setAlias('security.acl.cache', sprintf('security.acl.cache.%s', $config['cache']));
        }
    }

    /**
     * Loads the web configuration.
     *
     * @param array            $config    An array of configuration settings
     * @param ContainerBuilder $container A ContainerBuilder instance
     */

    protected function createRoleHierarchy($config, ContainerBuilder $container)
    {
        if (!isset($config['role_hierarchy'])) {
            $container->remove('security.access.role_hierarchy_voter');

            return;
        }

        $container->setParameter('security.role_hierarchy.roles', $config['role_hierarchy']);
        $container->remove('security.access.simple_role_voter');
    }

    protected function createAuthorization($config, ContainerBuilder $container)
    {
        if (!$config['access_control']) {
            return;
        }

        $this->addClassesToCompile(array(
            'Symfony\\Component\\Security\\Http\\AccessMap',
        ));

        foreach ($config['access_control'] as $access) {
            $matcher = $this->createRequestMatcher(
                $container,
                $access['path'],
                $access['host'],
                count($access['methods']) === 0 ? null : $access['methods'],
                $access['ip'],
                $access['attributes']
            );

            $container->getDefinition('security.access_map')
                      ->addMethodCall('add', array($matcher, $access['roles'], $access['requires_channel']));
        }
    }

    protected function createFirewalls($config, ContainerBuilder $container)
    {
        if (!isset($config['firewalls'])) {
            return;
        }

        $firewalls = $config['firewalls'];
        $providerIds = $this->createUserProviders($config, $container);

        // make the ContextListener aware of the configured user providers
        $definition = $container->getDefinition('security.context_listener');
        $arguments = $definition->getArguments();
        $userProviders = array();
        foreach ($providerIds as $userProviderId) {
            $userProviders[] = new Reference($userProviderId);
        }
        $arguments[1] = $userProviders;
        $definition->setArguments($arguments);

        // create security listener factories
        $factories = $this->createListenerFactories($container, $config);

        // load firewall map
        $mapDef = $container->getDefinition('security.firewall.map');
        $map = $authenticationProviders = array();
        foreach ($firewalls as $name => $firewall) {
            list($matcher, $listeners, $exceptionListener) = $this->createFirewall($container, $name, $firewall, $authenticationProviders, $providerIds, $factories);

            $contextId = 'security.firewall.map.context.'.$name;
            $context = $container->setDefinition($contextId, new DefinitionDecorator('security.firewall.context'));
            $context
                ->setArgument(0, $listeners)
                ->setArgument(1, $exceptionListener)
            ;
            $map[$contextId] = $matcher;
        }
        $mapDef->setArgument(1, $map);

        // add authentication providers to authentication manager
        $authenticationProviders = array_map(function($id) {
            return new Reference($id);
        }, array_values(array_unique($authenticationProviders)));
        $container
            ->getDefinition('security.authentication.manager')
            ->setArgument(0, $authenticationProviders)
        ;
    }

    protected function createFirewall(ContainerBuilder $container, $id, $firewall, &$authenticationProviders, $providerIds, array $factories)
    {
        // Matcher
        $i = 0;
        $matcher = null;
        if (isset($firewall['request_matcher'])) {
            $matcher = new Reference($firewall['request_matcher']);
        } else if (isset($firewall['pattern'])) {
            $matcher = $this->createRequestMatcher($container, $firewall['pattern']);
        }

        // Security disabled?
        if (false === $firewall['security']) {
            return array($matcher, array(), null);
        }

        // Provider id (take the first registered provider if none defined)
        if (isset($firewall['provider'])) {
            $defaultProvider = $this->getUserProviderId($firewall['provider']);
        } else {
            $defaultProvider = reset($providerIds);
        }

        // Register listeners
        $listeners = array();
        $providers = array();

        // Channel listener
        $listeners[] = new Reference('security.channel_listener');

        // Context serializer listener
        if (false === $firewall['stateless']) {
            $contextKey = $id;
            if (isset($firewall['context'])) {
                $contextKey = $firewall['context'];
            }

            $listeners[] = new Reference($this->createContextListener($container, $contextKey));
        }

        // Logout listener
        if (isset($firewall['logout'])) {
            $listenerId = 'security.logout_listener.'.$id;
            $listener = $container->setDefinition($listenerId, new DefinitionDecorator('security.logout_listener'));
            $listener->setArgument(1, $firewall['logout']['path']);
            $listener->setArgument(2, $firewall['logout']['target']);
            $listeners[] = new Reference($listenerId);

            // add logout success handler
            if (isset($firewall['logout']['success_handler'])) {
                $listener->setArgument(3, new Reference($firewall['logout']['success_handler']));
            }

            // add session logout handler
            if (true === $firewall['logout']['invalidate_session'] && false === $firewall['stateless']) {
                $listener->addMethodCall('addHandler', array(new Reference('security.logout.handler.session')));
            }

            // add cookie logout handler
            if (count($firewall['logout']['delete_cookies']) > 0) {
                $cookieHandlerId = 'security.logout.handler.cookie_clearing.'.$id;
                $cookieHandler = $container->setDefinition($cookieHandlerId, new DefinitionDecorator('security.logout.handler.cookie_clearing'));
                $cookieHandler->addArgument($firewall['logout']['delete_cookies']);

                $listener->addMethodCall('addHandler', array(new Reference($cookieHandlerId)));
            }

            // add custom handlers
            foreach ($firewall['logout']['handlers'] as $handlerId) {
                $listener->addMethodCall('addHandler', array(new Reference($handlerId)));
            }
        }

        // Authentication listeners
        list($authListeners, $defaultEntryPoint) = $this->createAuthenticationListeners($container, $id, $firewall, $authenticationProviders, $defaultProvider, $factories);

        $listeners = array_merge($listeners, $authListeners);

        // Access listener
        $listeners[] = new Reference('security.access_listener');

        // Switch user listener
        if (isset($firewall['switch_user'])) {
            $listeners[] = new Reference($this->createSwitchUserListener($container, $id, $firewall['switch_user'], $defaultProvider));
        }

        // Determine default entry point
        if (isset($firewall['entry_point'])) {
            $defaultEntryPoint = $firewall['entry_point'];
        }

        // Exception listener
        $exceptionListener = new Reference($this->createExceptionListener($container, $firewall, $id, $defaultEntryPoint));

        return array($matcher, $listeners, $exceptionListener);
    }

    protected function createContextListener($container, $contextKey)
    {
        if (isset($this->contextListeners[$contextKey])) {
            return $this->contextListeners[$contextKey];
        }

        $listenerId = 'security.context_listener.'.count($this->contextListeners);
        $listener = $container->setDefinition($listenerId, new DefinitionDecorator('security.context_listener'));
        $listener->setArgument(2, $contextKey);

        return $this->contextListeners[$contextKey] = $listenerId;
    }

    protected function createAuthenticationListeners($container, $id, $firewall, &$authenticationProviders, $defaultProvider, array $factories)
    {
        $listeners = array();
        $hasListeners = false;
        $defaultEntryPoint = null;

        foreach ($this->listenerPositions as $position) {
            foreach ($factories[$position] as $factory) {
                $key = str_replace('-', '_', $factory->getKey());

                if (isset($firewall[$key])) {
                    $userProvider = isset($firewall[$key]['provider']) ? $this->getUserProviderId($firewall[$key]['provider']) : $defaultProvider;

                    list($provider, $listenerId, $defaultEntryPoint) = $factory->create($container, $id, $firewall[$key], $userProvider, $defaultEntryPoint);

                    $listeners[] = new Reference($listenerId);
                    $authenticationProviders[] = $provider;
                    $hasListeners = true;
                }
            }
        }

        // Anonymous
        if (isset($firewall['anonymous'])) {
            $listeners[] = new Reference('security.authentication.listener.anonymous');
            $authenticationProviders[] = 'security.authentication.provider.anonymous';
            $hasListeners = true;
        }

        if (false === $hasListeners) {
            throw new \LogicException(sprintf('No authentication listener registered for pattern "%s".', isset($firewall['pattern']) ? $firewall['pattern'] : ''));
        }

        return array($listeners, $defaultEntryPoint);
    }

    protected function createEncoders($encoders, ContainerBuilder $container)
    {
        $encoderMap = array();
        foreach ($encoders as $class => $encoder) {
            $encoderMap[$class] = $this->createEncoder($class, $encoder, $container);
        }

        $container
            ->getDefinition('security.encoder_factory.generic')
            ->setArguments(array($encoderMap))
        ;
    }

    protected function createEncoder($accountClass, $config, ContainerBuilder $container)
    {
        // a custom encoder service
        if (isset($config['id'])) {
            return new Reference($config['id']);
        }

        // plaintext encoder
        if ('plaintext' === $config['algorithm']) {
            $arguments = array($config['ignore_case']);

            return array(
                'class' => new Parameter('security.encoder.plain.class'),
                'arguments' => $arguments,
            );
        }

        // message digest encoder
        $arguments = array(
            $config['algorithm'],
            $config['encode_as_base64'],
            $config['iterations'],
        );

        return array(
            'class' => new Parameter('security.encoder.digest.class'),
            'arguments' => $arguments,
        );
    }

    // Parses user providers and returns an array of their ids
    protected function createUserProviders($config, ContainerBuilder $container)
    {
        $providerIds = array();
        foreach ($config['providers'] as $name => $provider) {
            $id = $this->createUserDaoProvider($name, $provider, $container);
            $providerIds[] = $id;
        }

        return $providerIds;
    }

    // Parses a <provider> tag and returns the id for the related user provider service
    protected function createUserDaoProvider($name, $provider, ContainerBuilder $container, $master = true)
    {
        $name = $this->getUserProviderId(strtolower($name));

        // Existing DAO service provider
        if (isset($provider['id'])) {
            $container->setAlias($name, new Alias($provider['id'], false));

            return $provider['id'];
        }

        // Chain provider
        if ($provider['providers']) {
            $providers = array();
            foreach ($provider['providers'] as $providerName) {
                $providers[] = new Reference($this->getUserProviderId(strtolower($providerName)));
            }

            $container
                ->setDefinition($name, new DefinitionDecorator('security.user.provider.chain'))
                ->addArgument($providers)
            ;

            return $name;
        }

        // Doctrine Entity DAO provider
        if (isset($provider['entity'])) {
            $container
                ->setDefinition($name, new DefinitionDecorator('security.user.provider.entity'))
                ->addArgument($provider['entity']['class'])
                ->addArgument($provider['entity']['property'])
            ;

            return $name;
        }

        // In-memory DAO provider
        $definition = $container->setDefinition($name, new DefinitionDecorator('security.user.provider.in_memory'));
        foreach ($provider['users'] as $username => $user) {
            $userId = $name.'_'.$username;

            $container
                ->setDefinition($userId, new DefinitionDecorator('security.user.provider.in_memory.user'))
                ->setArguments(array($username, $user['password'], $user['roles']))
            ;

            $definition->addMethodCall('createUser', array(new Reference($userId)));
        }

        return $name;
    }

    protected function getUserProviderId($name)
    {
        return 'security.user.provider.concrete.'.$name;
    }

    protected function createExceptionListener($container, $config, $id, $defaultEntryPoint)
    {
        $exceptionListenerId = 'security.exception_listener.'.$id;
        $listener = $container->setDefinition($exceptionListenerId, new DefinitionDecorator('security.exception_listener'));
        $listener->setArgument(2, null === $defaultEntryPoint ? null : new Reference($defaultEntryPoint));

        // access denied handler setup
        if (isset($config['access_denied_handler'])) {
            $listener->setArgument(4, new Reference($config['access_denied_handler']));
        } else if (isset($config['access_denied_url'])) {
            $listener->setArgument(3, $config['access_denied_url']);
        }

        return $exceptionListenerId;
    }

    protected function createSwitchUserListener($container, $id, $config, $defaultProvider)
    {
        $userProvider = isset($config['provider']) ? $this->getUserProviderId($config['provider']) : $defaultProvider;

        $switchUserListenerId = 'security.authentication.switchuser_listener.'.$id;
        $listener = $container->setDefinition($switchUserListenerId, new DefinitionDecorator('security.authentication.switchuser_listener'));
        $listener->setArgument(1, new Reference($userProvider));
        $listener->setArgument(3, $id);
        $listener->addArgument($config['parameter']);
        $listener->addArgument($config['role']);

        return $switchUserListenerId;
    }

    protected function createRequestMatcher($container, $path = null, $host = null, $methods = null, $ip = null, array $attributes = array())
    {
        $serialized = serialize(array($path, $host, $methods, $ip, $attributes));
        $id = 'security.request_matcher.'.md5($serialized).sha1($serialized);

        if (isset($this->requestMatchers[$id])) {
            return $this->requestMatchers[$id];
        }

        // only add arguments that are necessary
        $arguments = array($path, $host, $methods, $ip, $attributes);
        while (count($arguments) > 0 && !end($arguments)) {
            array_pop($arguments);
        }

        $container
            ->register($id, '%security.matcher.class%')
            ->setPublic(false)
            ->setArguments($arguments)
        ;

        return $this->requestMatchers[$id] = new Reference($id);
    }

    protected function createListenerFactories(ContainerBuilder $container, $config)
    {
        if (null !== $this->factories) {
            return $this->factories;
        }

        // load service templates
        $c = new ContainerBuilder();
        $parameterBag = $container->getParameterBag();
        $loader = new XmlFileLoader($c, new FileLocator(array(__DIR__.'/../Resources/config', __DIR__.'/Resources/config')));
        $loader->load('security_factories.xml');

        // load user-created listener factories
        foreach ($config['factories'] as $factory) {
            $loader->load($parameterBag->resolveValue($factory));
        }

        $tags = $c->findTaggedServiceIds('security.listener.factory');

        $factories = array();
        foreach ($this->listenerPositions as $position) {
            $factories[$position] = array();
        }

        foreach (array_keys($tags) as $tag) {
            $factory = $c->get($tag);
            $factories[$factory->getPosition()][] = $factory;
        }

        return $this->factories = $factories;
    }


    /**
     * Returns the base path for the XSD files.
     *
     * @return string The XSD base path
     */
    public function getXsdValidationBasePath()
    {
        return __DIR__.'/../Resources/config/schema';
    }

    public function getNamespace()
    {
        return 'http://symfony.com/schema/dic/security';
    }
}
