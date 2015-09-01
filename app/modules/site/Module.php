<?php

namespace App\Modules\Site;

use Phalcon\Loader;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ModuleDefinitionInterface;

class Module implements ModuleDefinitionInterface{

	public function registerAutoloaders(\Phalcon\DiInterface $di = null){
		$loader = new \Phalcon\Loader();
		$loader->registerNamespaces(array(
			'App\Modules\Site\Controllers' => '../app/modules/site/controllers/',
			//'App\Site\Models' => '../apps/frontend/models/',
		));
		
		$loader->register();
	}

	/**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	public function registerServices(\Phalcon\DiInterface $di = null)
	{
		//Registering a dispatcher
		$di->set('dispatcher', function () {
			$dispatcher = new \Phalcon\Mvc\Dispatcher();

			//Attach a event listener to the dispatcher
			$eventManager = new \Phalcon\Events\Manager();
			//$eventManager->attach('dispatch', new \Acl('site'));

			$dispatcher->setEventsManager($eventManager);
			$dispatcher->setDefaultNamespace("App\Modules\Site\Controllers");
			return $dispatcher;
		});

		//Registering the view component
		$di->set('view', function () {
			$view = new \Phalcon\Mvc\View();
			$view->setViewsDir('../app/modules/site/views/');
			return $view;
		});
	}

}