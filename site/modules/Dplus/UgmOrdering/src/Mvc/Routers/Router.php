<?php namespace UgmOrdering\Mvc\Routers;
// PHP Core
use Exception;
// Whoops
use Whoops\Run as Whoops;
use Mvc\Whoops\Handlers\Page as WhoopsHandler;
// ProcessWire
use ProcessWire\Wire404Exception;
use ProcessWire\ProcessWire;
// Be Kind Rewind
use BeKindRewind\Pw\Exceptions;
// Pauldro MVC
use Mvc\Routers\Router as BaseRouter;
use UgmOrdering\Mvc\Whoops\Handlers\EmailPage;

/**
 * Router
 * @property array  $routes      Array of Routes
 * @property string $path        Path to Begin Routing from
 * @property array  $routeInfo   Route Information from Dispatcher
 * @property array  $routeprefix Path to Begin Routing from
 *
 */
class Router extends BaseRouter {
	/**
	 * Try Calling the Handler Function, catch errors if needed
	 * @param  array $routeInfo
	 * @return strings
	 */
	protected function handleRoute() {
		$response = '';

		try {
			$response = $this->handle($this->routeInfo);
		} catch (Wire404Exception $e) {
			$this->error = true;
			throw $e;
		} catch (Exception $e) {
			$this->error = true;
			$response = $this->whoopsResponse($e);
		}
		return $response;
	}

	/**
	 * Return Whoops Response Message
	 * @param  Exception $e Exception
	 * @return string       HTML Whoops Response
	 */
	protected function whoopsResponse(Exception $e) {
		$whoops = new Whoops();
		$whoops->allowQuit(false);

		$emailHandler = $this->getWhoopsEmailHandler();
		$whoops->pushHandler($emailHandler);

		if ($this->wire('config')->debug === false) {
			$handler = $this->getWhoopsPageHandler();
			$whoops->pushHandler($handler);
		}

		$whoops->writeToOutput(false);
		return $whoops->handleException($e);
	}

	protected function getWhoopsPageHandler() {
		$handler = WhoopsHandler::handler();
		$handler->addDataTable('Dplus', [
			'User ID'    => $this->wire('user')->loginid,
			'Session ID' => session_id(),
			'Path'       => $this->wire('input')->url(),
		]);
		return $handler;
	}

	protected function getWhoopsEmailHandler() {
		$handler = new EmailPage();
		$handler->addDataTable('Dplus', [
			'User ID'    => $this->wire('user')->loginid,
			'Session ID' => session_id(),
			'Path'       => $this->wire('input')->url(),
		]);
		return $handler;
	}


}
