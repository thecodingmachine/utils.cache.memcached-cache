<?php
/*
 * Copyright (c) 2013-2014 David Negrier
 * 
 * See the file LICENSE.txt for copying permission.
 */

namespace Mouf\Utils\Cache;

use Mouf\Installer\PackageInstallerInterface;
use Mouf\MoufManager;
use Mouf\Html\Renderer\RendererUtils;
use Mouf\Actions\InstallUtils;

/**
 * An installer class for the Bootstrap template.
 */
class MemcachedCacheInstaller implements PackageInstallerInterface {

	/**
	 * (non-PHPdoc)
	 * @see \Mouf\Installer\PackageInstallerInterface::install()
	 */
	public static function install(MoufManager $moufManager) {
		// Let's create the instances.
		$memcacheCacheService = InstallUtils::getOrCreateInstance('memcacheCacheService', 'Mouf\\Utils\\Cache\\MemcachedCache', $moufManager);
		
		// Let's bind instances together.
		if (!$memcacheCacheService->getConstructorArgumentProperty('servers')->isValueSet()) {
			$memcacheCacheService->getConstructorArgumentProperty('servers')->setValue(array(0 => '127.0.0.1:11211', ));
		}
		if (!$memcacheCacheService->getConstructorArgumentProperty('defaultTimeToLive')->isValueSet()) {
			$memcacheCacheService->getConstructorArgumentProperty('defaultTimeToLive')->setValue('3600');
		}

		
		// Let's rewrite the MoufComponents.php file to save the component
		$moufManager->rewriteMouf();
	}
}
