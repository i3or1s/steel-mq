<?php

namespace AerialShip\SteelMqBundle\Tests\Functional;

use AerialShip\SteelMqBundle\DataFixtures\Orm\ProjectData;
use AerialShip\SteelMqBundle\DataFixtures\Orm\UserData;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Routing\RouterInterface;

class AbstractFunctionTestCase extends WebTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        static::ensureKernelShutdown();
        static::$kernel = null;
    }

    /**
     * @return \Symfony\Component\HttpKernel\KernelInterface
     */
    protected function getBootedKernel()
    {
        if (false == static::$kernel) {
            $this->bootKernel();
        }

        return static::$kernel;
    }

    /**
     * @param string $name
     * @return object
     */
    protected function getService($name)
    {
        return $this->getBootedKernel()->getContainer()->get($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function hasService($name)
    {
        return $this->getBootedKernel()->getContainer()->has($name);
    }

    /**
     * @return EntityManager
     */
    protected function getEm()
    {
        return $this->getService('doctrine')->getManager();
    }

    /**
     * @param Loader $loader
     */
    protected function loadFixtures(Loader $loader)
    {
        $purger = new ORMPurger();
        $executor = new ORMExecutor( $this->getEm(), $purger );
        $executor->execute( $loader->getFixtures() );
    }

    protected function loadUserAndProjectData()
    {
        $loader = new Loader();
        $loader->addFixture(new UserData());
        $loader->addFixture(new ProjectData());
        $this->loadFixtures($loader);
    }

    /**
     * @param string $route
     * @param array $params
     * @param bool $referenceType
     * @return string
     */
    protected function generateUrl($route, $params = array(), $referenceType = RouterInterface::ABSOLUTE_PATH)
    {
        return $this->getBootedKernel()->getContainer()->get('router')->generate($route, $params, $referenceType);
    }
}