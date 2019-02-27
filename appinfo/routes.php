<?php
/**
 * Nextcloud - scanner
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Greg Sutcliffe <nextcloud@emeraldreverie.org>
 * @copyright Greg Sutcliffe 2016
 */

/**
 * Create your routes in here. The name is the lowercase name of the controller
 * without the controller part, the stuff after the hash is the method.
 * e.g. page#index -> OCA\Scanner\Controller\PageController->index()
 *
 * The controller class has to be registered in the application.php file since
 * it's instantiated in there
 */
return [
    'routes' => [
	   ['name' => 'scanner#selfcheck', 'url' => '/selfcheck', 'verb' => 'GET'],
	   ['name' => 'scanner#scan', 'url' => '/scan', 'verb' => 'POST'],
	   ['name' => 'scanner#backends', 'url' => '/backends', 'verb' => 'GET'],
	   ['name' => 'scanner#backend', 'url' => '/backends/{id}', 'verb' => 'GET'],
	   ['name' => 'scanner#preview', 'url' => '/preview', 'verb' => 'GET'],
    ]
];
