<?php

namespace OCA\ocmultishare\AppInfo;

use OCP\AppFramework\App;

use OCA\ocmultishare\AppInfo;
use OCA\ocmultishare\Controller;
use OCA\ocmultishare\Controller\OcmultishareApiController;

use OCA\ocmultishare\Db\SharingDAO;

class Application extends App {
        public function __construct(array $urlParams=array()){
                parent::__construct('ocmultishare', $urlParams);
                $container = $this->getContainer();

		/* 
		 * Register our controller with the container.
		 * Make sure that the app-name, request and database handler
		 * are passed to it upon creation.
		 */

                $container->registerService('OcmultishareApiController', function($c) {
                        return new OcmultishareApiController(
                                $c->query('AppName'),
                                $c->query('Request'),
                                $c->query('ServerContainer')->getDb()
                        );
                });


        }
}

$application = new Application();

$application->registerRoutes($this, array(
	'routes' => array(
		/*
		 * Register our only controller: Accept 
		 * POST requests at the URL specified, and direct
		 * those requests to the create function of the class.
		 *
		 * We don't require anything else than this.
		 */

		array('name' => 'ocmultishare_api#create', 'url' => '/duplicate', 'verb' => 'POST'),
	)
));

