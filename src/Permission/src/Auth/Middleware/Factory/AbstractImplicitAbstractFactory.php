<?php


namespace rollun\permission\Auth\Middleware\Factory;


use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Factory\AbstractFactoryInterface;

abstract class AbstractImplicitAbstractFactory implements AbstractFactoryInterface
{
    /**
     * Return service postfix
     * @return string
     */
    abstract static public function getImplicitPostfix();

    /**
     * @param ContainerInterface $container
     * @param string $requestedName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $resourceName = $this->getResourceName($requestedName);
        if(empty($resourceName)) return false;
        return $container->has($resourceName);
    }

    /**
     * @param $requestedName
     * @return string
     */
    protected function getResourceName($requestedName)
    {
        if(preg_match('/^(?<resourceName>[\w\W]+)' .static::getImplicitPostfix(). '/', $requestedName, $match)) {
            return $match["resourceName"];
        }
        return "";
    }
}