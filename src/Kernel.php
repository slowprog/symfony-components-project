<?php

namespace App;

use Doctrine\Migrations\Configuration\EntityManager\ExistingEntityManager;
use Doctrine\Migrations\Configuration\Migration\ConfigurationArray;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Console\Application;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ContainerControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\Migrations\Tools\Console\Command;
use Exception;

class Kernel
{
    /**
     * @var array
     */
    private $parameters;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    /**
     * @throws ORMException
     */
    public function __construct()
    {
        $this->initParameters();
        $this->initEntityManager();
    }

    /**
     * @return self
     *
     * @throws Exception
     */
    public function web(): self
    {
        $this->initContainerBuilder();
        $this->initRouteCollection();

        return $this;
    }

    /**
     * @return Application
     */
    public function cli(): Application
    {
        $helperSet = ConsoleRunner::createHelperSet($this->entityManager);

        $dependencyFactory = DependencyFactory::fromEntityManager(
            new ConfigurationArray($this->parameters['migration']),
            new ExistingEntityManager($this->entityManager)
        );

        $cli = ConsoleRunner::createApplication($helperSet, [
            new Command\DiffCommand($dependencyFactory),
            new Command\DumpSchemaCommand($dependencyFactory),
            new Command\ExecuteCommand($dependencyFactory),
            new Command\GenerateCommand($dependencyFactory),
            new Command\LatestCommand($dependencyFactory),
            new Command\ListCommand($dependencyFactory),
            new Command\MigrateCommand($dependencyFactory),
            new Command\RollupCommand($dependencyFactory),
            new Command\StatusCommand($dependencyFactory),
            new Command\SyncMetadataCommand($dependencyFactory),
            new Command\VersionCommand($dependencyFactory),
        ]);

        return $cli;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    public function run(): void
    {
        $request = Request::createFromGlobals();
        /** @var HttpKernel $kernel */
        $kernel = $this->containerBuilder->get('kernel');

        try {
            $response = $kernel->handle($request);
        } catch (Exception $e) {
            $status = 200;

            if ($e instanceof HttpException) {
                $status = $e->getStatusCode();
            }

            $response = new Response(sprintf("Error : %s : %s", $e->getMessage(), $e->getTraceAsString()), $status);
        }

        $response->send();

        $kernel->terminate($request, $response);
    }

    /**
     * @return void
     */
    private function initParameters(): void
    {
        if ($this->parameters === null) {
            $this->parameters = include(__DIR__ . '/../config/parameters.php');
        }
    }

    /**
     * @throws ORMException
     */
    private function initEntityManager(): void
    {
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . '/Entity'],
            $this->parameters['debug'],
            null,
            null,
            false
        );

        $this->entityManager = EntityManager::create($this->parameters['database'], $config);
    }

    /**
     * @return void
     */
    private function initRouteCollection(): void
    {
        $routes = require __DIR__ . '/../config/routes.php';

        $routeCollection = new RouteCollection();

        foreach ($routes as $alias => $route) {
            $controller = new $route['class']($this->containerBuilder);

            $routeCollection->add(
                $alias,
                new Route(
                    $route['path'],
                    ['_controller' => [$controller, $route['action']]],
                    [],
                    [],
                    null,
                    [],
                    $route['method']
                )
            );
        }

        $this->containerBuilder->register('matcher', UrlMatcher::class)
            ->setArguments([$routeCollection, new Reference('context')])
        ;
    }

    /**
     * @return void
     *
     * @throws Exception
     */
    private function initContainerBuilder(): void
    {
        $this->containerBuilder = new ContainerBuilder();

        $this->containerBuilder->register('context', RequestContext::class);
        $this->containerBuilder->register('request_stack', RequestStack::class);
        $this->containerBuilder->register('controller_resolver', ContainerControllerResolver::class)
            ->setArguments([new Reference('service_container')])
        ;
        $this->containerBuilder->register('argument_resolver', ArgumentResolver::class);

        $this->containerBuilder->register('listener.router', RouterListener::class)
            ->setArguments([new Reference('matcher'), new Reference('request_stack')])
        ;

        $this->containerBuilder->register('dispatcher', EventDispatcher::class)
            ->addMethodCall('addSubscriber', [new Reference('listener.router')])
        ;

        $this->containerBuilder->register('kernel', HttpKernel::class)
            ->setArguments([
                new Reference('dispatcher'),
                new Reference('controller_resolver'),
                new Reference('request_stack'),
                new Reference('argument_resolver'),
            ])
        ;

        $this->containerBuilder->set('entity_manager', $this->entityManager);

        (new PhpFileLoader($this->containerBuilder, new FileLocator(__DIR__ . '/../config')))->load('services.php');
    }
}