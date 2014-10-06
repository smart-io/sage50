<?php
namespace Sinergi\Sage50\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Tools\Console\ConsoleRunner;
use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Console\Command\Command as DoctrineCommand;
use Symfony\Component\Console\Helper\HelperSet;
use Exception;
use Sinergi\Config\Config;

class Doctrine extends AbstractManagerRegistry
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var HelperSet
     */
    private $helperSet;

    /**
     * @var DoctrineCommand[]
     */
    private $commands;

    /**
     * @var Config
     */
    private $config;

    public function __construct()
    {
    }

    /**
     * @return Config
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param Config $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->config = $config;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getService($name)
    {
        return $this->getEntityManager($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function resetService($name)
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
        /** @var EntityManager $entityManager */
        foreach ([$this->entityManager] as $entityManager) {
            try {
                return $entityManager->getConfiguration()->getEntityNamespace($alias);
            } catch (ORMException $e) {
            }
        }

        throw ORMException::unknownEntityNamespace($alias);
    }

    private function addDefaultCommands()
    {
        $this->commands = [
        ];
    }

    /**
     * @param DoctrineCommand $command
     * @return $this
     */
    public function addCommand(DoctrineCommand $command)
    {
        if (null === $this->commands) {
            $this->addDefaultCommands();
        }
        $this->commands[] = $command;
        return $this;
    }

    /**
     * @return array
     */
    public function getCommands()
    {
        if (null === $this->commands) {
            $this->addDefaultCommands();
        }
        return $this->commands;
    }

    /**
     * @return HelperSet
     */
    public function getHelperSet()
    {
        if (null === $this->helperSet) {
            $this->helperSet = ConsoleRunner::createHelperSet($this->getEntityManager());
        }
        return $this->helperSet;
    }

    /**
     * @param EntityManager $entityManager
     * @return $this
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        return $this;
    }

    /**
     * @return EntityManager|null
     */
    public function getEntityManager()
    {
        if (isset($this->entityManager)) {
            return $this->entityManager;
        }
        return $this->entityManager = $this->createEntityManager();
    }

    /**
     * @return EntityManager|null
     * @throws Exception
     */
    public function createEntityManager()
    {
        $connectionConfig = $this->getConfig()->get("doctrine.connection");

        if (isset($connectionConfig['is_dev_mode'])) {
            $isDevMode = (bool)$connectionConfig['is_dev_mode'];
        } else {
            $isDevMode = false;
        }

        $doctrineConfig = Setup::createConfiguration($isDevMode);

        $doctrineConfig->setMetadataDriverImpl(
            new AnnotationDriver(new AnnotationReader(), $connectionConfig['paths'])
        );

        return EntityManager::create($connectionConfig, $doctrineConfig);
    }
}
