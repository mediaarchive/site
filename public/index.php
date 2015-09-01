<?php
require '../vendor/autoload.php';
error_reporting (E_ALL);
class Application extends \Phalcon\MVC\Application {
    /**
	 * Register the services here to make them general or register in the ModuleDefinition to make them module-specific
	 */
	protected function _registerServices(){
        //Register an autoloader
        $loader = new \Phalcon\Loader();		
    
        $loader->registerNamespaces(array(
            'App\Libs' => '../app/libs/',
            'App\Models' => '../app/models/'
        ));
        
        
        $loader->register();
        
        //Create a DI
        $di = new Phalcon\DI\FactoryDefault();
    
        require '../app/config/config.php';
        require '../app/config/server.php';
        
        $di->set('config', $config);
        $di->set('config_server', $config_server);
        $di->set('env', $config->app->environment);
        
        define('ENVIRONMENT', $config->app->environment);
        
        $di->set('router', function(){
            require '../app/config/routes.php';
            $router->removeExtraSlashes(true);
            $router->setDefaults(array(
                'module' => 'Site',
                'controller' => 'index',
                'action' => 'index'
            ));
            return $router;
        });
        
        $di->setShared('auth', function() {
            $Auth = new \App\Libs\Auth();
			return $Auth;
		});
    
        $di->setShared('db', function() use ($config_server, $di) {
    
            $connection = new \Phalcon\Db\Adapter\Pdo\Mysql(array(
                "host" => $config_server->db->host,
                "username" => $config_server->db->user,
                "password" => $config_server->db->pass,
                "dbname" => $config_server->db->name,
                "charset"   => $config_server->db->charset
            ));
    
            return $connection;
        });
        
        $di->set('url', function() use ($config, $di){
            $url = new \Phalcon\Mvc\Url();
            $url->setBaseUri('http://'.$_SERVER['HTTP_HOST'].'/');
            return $url;
        });
        
        $di->set('crypt', function() {
            $crypt = new Phalcon\Crypt();
            $crypt->setKey($config->crypt->key); // Используйте свой собственный ключ!
            return $crypt;
        });
        
        $this->setDI($di);
    }
    
    public function main(){
        $this->_registerServices();

        //Register the installed modules
        $this->registerModules(array(
            'Site' => array(
                'className' => 'App\Modules\Site\Module',
                'path' => '../app/modules/site/Module.php'
            )
        ));
        
        echo $this->handle()->getContent();
    }
}

try {
    $application = new Application();
    $application->main();

} catch(\Phalcon\Exception $e) {
    echo "PhalconException: ", $e->getMessage();
    echo '<pre>';
    echo $e->getTraceAsString();
    echo '</pre>';
}

