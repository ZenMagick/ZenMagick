<?php
/*
 * ZenMagick - Another PHP framework.
 * Copyright (C) 2006-2012 zenmagick.org
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 */
?>
<?php
namespace zenmagick\base\dependencyInjection\compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * This processes all DefinitionDecorator instances that merge.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 * @author DerManoMann <mano@zenmagick.org>
 */
class ResolveMergeDefinitionsPass implements CompilerPassInterface {
    private $container;
    private $compiler;
    private $formatter;

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container) {
        $this->container = $container;
        $this->compiler = $container->getCompiler();
        $this->formatter = $this->compiler->getLoggingFormatter();
        foreach (array_keys($container->getDefinitions()) as $id) {
            // yes, we are specifically fetching the definition from the
            // container to ensure we are not operating on stale data
            $definition = $container->getDefinition($id);
            if (!$definition instanceof DefinitionDecorator || $definition->isAbstract()) {
                continue;
            }
            // check for merge: prefix and fix if found parent
            $parent = $definition->getParent();
            if (0 === strpos($parent, 'merge:')) {
                $parent = substr($parent, 6);
                $definition->setParent($parent);
                if (!$this->container->hasDefinition($parent)) {
                    // create parent, assume id = class (best guess and ZM practice)
                    $this->container->setDefinition($parent, new Definition($parent));
                }
                // resolve as parent, not id
                $this->resolveDefinition($parent, $definition);
                $this->container->removeDefinition($id);
            }
        }
    }

    /**
     * Resolves the definition
     *
     * @param string              $id         The definition identifier
     * @param DefinitionDecorator $definition
     * @return Definition
     */
    private function resolveDefinition($id, DefinitionDecorator $definition)
    {
        if (!$this->container->hasDefinition($parent = $definition->getParent())) {
            throw new \RuntimeException(sprintf('The parent definition "%s" defined for definition "%s" does not exist.', $parent, $id));
        }

        $parentDef = $this->container->getDefinition($parent);
        if ($parentDef instanceof DefinitionDecorator) {
            $parentDef = $this->resolveDefinition($parent, $parentDef);
        }

        $this->compiler->addLogMessage($this->formatter->formatResolveInheritance($this, $id, $parent));
        $def = new Definition();

        // merge in parent definition
        // purposely ignored attributes: scope, abstract
        $def->setClass($parentDef->getClass());
        $def->setArguments($parentDef->getArguments());
        $def->setMethodCalls($parentDef->getMethodCalls());
        $def->setProperties($parentDef->getProperties());
        $def->setFactoryClass($parentDef->getFactoryClass());
        $def->setFactoryMethod($parentDef->getFactoryMethod());
        $def->setFactoryService($parentDef->getFactoryService());
        $def->setConfigurator($parentDef->getConfigurator());
        $def->setFile($parentDef->getFile());
        $def->setPublic($parentDef->isPublic());
        $def->setTags($parentDef->getTags());

        // overwrite with values specified in the decorator
        $changes = $definition->getChanges();
        if (isset($changes['class'])) {
            $def->setClass($definition->getClass());
        }
        if (isset($changes['factory_class'])) {
            $def->setFactoryClass($definition->getFactoryClass());
        }
        if (isset($changes['factory_method'])) {
            $def->setFactoryMethod($definition->getFactoryMethod());
        }
        if (isset($changes['factory_service'])) {
            $def->setFactoryService($definition->getFactoryService());
        }
        if (isset($changes['configurator'])) {
            $def->setConfigurator($definition->getConfigurator());
        }
        if (isset($changes['file'])) {
            $def->setFile($definition->getFile());
        }
        if (isset($changes['public'])) {
            $def->setPublic($definition->isPublic());
        }

        // merge arguments
        foreach ($definition->getArguments() as $k => $v) {
            if (is_numeric($k)) {
                $def->addArgument($v);
                continue;
            }

            if (0 !== strpos($k, 'index_')) {
                throw new \RuntimeException(sprintf('Invalid argument key "%s" found.', $k));
            }

            $index = (integer) substr($k, strlen('index_'));
            $def->replaceArgument($index, $v);
        }

        // merge properties
        foreach ($definition->getProperties() as $k => $v) {
            $def->setProperty($k, $v);
        }

        // append method calls
        if (count($calls = $definition->getMethodCalls()) > 0) {
            $def->setMethodCalls(array_merge($def->getMethodCalls(), $calls));
        }

        // merge tags
        $def->setTags(array_merge($def->getTags(), $definition->getTags()));

        // these attributes are always taken from the child
        $def->setAbstract($definition->isAbstract());
        $def->setScope($definition->getScope());

        // set new definition on container
        $this->container->setDefinition($id, $def);

        return $def;
    }

}
