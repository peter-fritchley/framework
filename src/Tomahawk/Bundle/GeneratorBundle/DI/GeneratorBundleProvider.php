<?php

/*
 * This file is part of the TomahawkPHP package.
 *
 * (c) Tom Ellis
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tomahawk\Bundle\GeneratorBundle\DI;

use Tomahawk\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Tomahawk\DI\ContainerInterface;
use Tomahawk\DI\ServiceProviderInterface;
use Tomahawk\Bundle\GeneratorBundle\Generator\ModelGenerator;
use Tomahawk\Bundle\GeneratorBundle\Generator\ControllerGenerator;
use Symfony\Component\Filesystem\Filesystem;

class GeneratorBundleProvider implements ServiceProviderInterface
{
    protected $dirs;

    public function __construct($dirs)
    {
        $this->dirs = $dirs;
    }

    public function register(ContainerInterface $container)
    {
        $skeltonDirs = $this->dirs;

        $container->set('bundle_generator', function(ContainerInterface $c) use ($skeltonDirs) {
            $generator = new BundleGenerator($c['filesystem']);
            $generator->setSkeletonDirs($skeltonDirs);
            return $generator;
        });

        $container->set('model_generator', function(ContainerInterface $c) use ($skeltonDirs) {
            $generator = new ModelGenerator($c['filesystem']);
            $generator->setSkeletonDirs($skeltonDirs);
            return $generator;
        });

        $container->set('controller_generator', function(ContainerInterface $c) use ($skeltonDirs) {
            $generator = new ControllerGenerator($c['filesystem']);
            $generator->setSkeletonDirs($skeltonDirs);
            return $generator;
        });
    }
}
