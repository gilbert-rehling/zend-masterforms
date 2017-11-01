<?php

namespace Savve\EventManager\Listener;

use ProxyManager\ProxyGenerator\ValueHolder\MethodGenerator\GetWrappedValueHolderValue;

use Savve\Stdlib;
use Savve\Stdlib\Exception;
use Savve\View\Model\ViewModel;

use Zend\View\Model\JsonModel;
//use Zend\View\ViewEvent;
//use Zend\Router\Http\RouteMatch;
//use Zend\EventManager\Event;
use Zend\Mvc\MvcEvent;
//use Zend\Mvc\Application;
use Zend\View\Resolver as ViewResolver;

class DispatchListener {


	/**
	 * Converts HTTP Response to XML format if request is via AJAX
	 *
	 * \Zend\Mvc\MvcEvent::EVENT_DISPATCH postDispatch event listener
	 *
	 * @param MvcEvent $event
	 *
	 * @return void Zend\Http\PhpEnvironment\Response
	 */
	public function dispatchAjax (MvcEvent $event)
	{
		/* @var $controller \Savve\Controller\AbstractController */
		/* @var $application \Zend\Mvc\Application */
		/* @var $serviceManager \Zend\ServiceManager\ServiceManager */
		/* @var $viewHelperManager \Zend\View\HelperPluginManager */
		/* @var $renderer \Zend\View\Renderer\PhpRenderer */
		/* @var $request \Zend\Http\PhpEnvironment\Request */
		/* @var $response \Zend\Http\PhpEnvironment\Response */
		/* @var $view \Zend\View\Model\ViewModel */

	//	$controller = $event->getTarget ();
		$application = $event->getApplication ();
		$serviceManager = $application->getServiceManager ();
		$viewHelperManager = $serviceManager->get ('ViewHelperManager');
		$renderer = $viewHelperManager->getRenderer ();
		$request = $event->getRequest ();
		$response = $event->getResponse ();

		// only proceed if this an browser request
		if (!($request instanceof \Zend\Http\PhpEnvironment\Request && $response instanceof \Zend\Http\PhpEnvironment\Response)) {
			return;
		}

		// do not show layout when in AJAX
		if ($request->isXmlHttpRequest ()) {
			// get the view from the action controller
	//		$controller->flashMessenger ()->clearMessages ('error');
	//		$controller->flashMessenger ()->clearMessages ('success');
	//		$controller->flashMessenger ()->clearMessages ('info');

			//$controller->flashMessenger()->getMessages('success');
			//$controller->flashMessenger()->getMessages('info');
			//$controller->flashMessenger()->clearMessages();

			/** @var \Zend\View\Model\ViewModel $view */
			$view = $event->getResult ();
			$view->setTerminal ($request->isXmlHttpRequest ());

			// create a new response
			$response->setStatusCode (200);

			$ajaxView = new ViewModel();
			$ajaxView->setTemplate ('layout/ajax-layout');
			$view->setVariable ('isAjax', true);
			$ajaxView->setTitle ($view);
			$ajaxView->setVariable('message', $view->message);
			$ajaxView->setVariable('isAPIResponse', $view->isAPIResponse);
			$ajaxView->setContent ($renderer->render ($view));

			$response->setContent ($renderer->render ($ajaxView));
			return $response;
		}
	}

	/**
	 * Dispatch error handler
	 * \Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR event listener
	 *
	 * @param MvcEvent $event
	 *
	 * @return \Savve\EventManager\Listener\DispatchListener | \Zend\View\Model\ViewModel
	 */
	public function dispatchError (MvcEvent $event)
	{
		/* @var $application \Zend\Mvc\Application */
		/* @var $routeMatch \Zend\Router\Http\RouteMatch */
		/* @var $serviceManager \Zend\ServiceManager\ServiceManager */
		/* @var $response \Zend\Http\PhpEnvironment\Response */
		/* @var $viewManager \Zend\Mvc\View\Http\ViewManager */
		/* @var $renderer \Zend\View\Renderer\PhpRenderer */
		/* @var $resolver \Zend\View\Resolver\AggregateResolver */

		/* @var $eventManager \Zend\EventManager\EventManager */
		/* @var $request \Zend\Http\PhpEnvironment\Request */
		/* @var $router \Zend\Router\Http\TreeRouteStack */
		/* @var $exceptionStrategy \Zend\Mvc\View\Http\ExceptionStrategy */
		/* @var $result \Zend\View\Model\ViewModel */

		$application 	= $event->getApplication ();
		$routeMatch 	= $event->getRouteMatch ();
		$serviceManager = $application->getServiceManager ();
		$response 		= $application->getResponse ();
		$viewManager 	= $serviceManager->get ('ViewManager');
		$renderer		= $serviceManager->get ('ViewRenderer');
		$resolver       = new ViewResolver\AggregateResolver();

	//	$eventManager = $application->getEventManager ();
	//	$request = $application->getRequest ();
	//	$router = $event->getRouter ();
	//	$resolver = $viewManager->getResolver ();
	//	$exceptionStrategy = $viewManager->getExceptionStrategy ();
	//	$notFoundStrategy = $viewManager->getRouteNotFoundStrategy ();

		// this is only for HTTP response, do not continue otherwise
		if (!$response instanceof \Zend\Http\Response) {
			return false;
		}
		$code = $response->getStatusCode () ?: 500; // default

		// get the current error code
	//	$error = $event->getError ();

		// the content of the page
		$result = $event->getResult ();

		// handle error exception
		if ($event->getParam ('exception')) {
			/* @var $exception \Exception */
			$exception = $event->getParam ('exception');

			// exception error message
			$message = $exception->getMessage ();
			$result->setVariable ('message', $message);

			// exception error code
			if ($exception->getCode ()) {
				$code = $exception->getCode ();
			}
		}

		// set error code back to the response object
		$response->setStatusCode ($code);


		if(isset($routeMatch) && $routeMatch->getParam('REST')){
			// This parameter is set up in the onRoute Listener
			/* @see \Authorization\EventManager\Listener\RouteListener:routeListener()*/
			if($result instanceof JsonModel){
				return false;
			}
			$response->setReasonPhrase($event->getParam('title'));
			$response->setContent($event->getParam('detail'));
			$response->getHeaders()->addHeaderLine( 'Access-Control-Allow-Origin', '*' );

			$content = [
				"title" => $event->getParam ('title'),
				"detail" => $event->getParam ('detail'),
				"code" => $event->getParam ('code'),
				"comments" => $event->getParam ('comments')
			];

			if(APPLICATION_ENV == 'development' && (!$event->getParam('simulation_mode') || ($event->getParam('simulation_mode') == 'Yes'))){

				/** @var \Savve\Stdlib\Exception\UnauthorisedException $exception */
				$exception = $event->getParam('exception');
				$content['exceptions'] = $exception ? $exception->getTraceAsString():sprintf("Unknown %s (%s)",__FILE__,__LINE__);
			}
			else{

				//In production mode a Json model is returned.
				$model = new JsonModel($content);
				$model->setTerminal(true);
				$event->setResult($model);
				$event->setViewModel($model);

				return $model;
			}
		}
		else {
			// Set the vew only if it is not a rest or else the code fails
			// view error
			/* @var $layout \Zend\View\Model\ViewModel */
			$layout = $event->getViewModel ();
			// @todo need to retrieve the following from the view_manager config in the Config service

			// set the error layout template
			$layoutTemplate = 'layout/' . $code;
			if (!$resolver->resolve ($layoutTemplate, $renderer)) {
				$layoutTemplate = 'layout/error';
			}
			if (!$resolver->resolve ($layoutTemplate, $renderer)) {
				$layoutTemplate  = $serviceManager->get('HttpDefaultRenderingStrategy')->getLayoutTemplate();
			}

			// set the error view template
			$template = 'error/' . $code;
			if (!$resolver->resolve ($template, $renderer)) {
				$template = $serviceManager->get('HttpExceptionStrategy')->getExceptionTemplate ();
			}
			$result->setTemplate ($template);

			// set the layout template
			$layout->setTemplate ($layoutTemplate);

		}
		return $result;
	}

	/**
	 * Dispatch error handler, redirects depending on the values set on the exception class
	 * \Zend\Mvc\MvcEvent::EVENT_DISPATCH_ERROR event listener
	 *
	 * @param MvcEvent $event
	 *
	 * @return \Savve\EventManager\Listener\DispatchListener | \Zend\View\Model\ViewModel
	 */
	public function redirectDispatchError (MvcEvent $event)
	{
		/* @var $application \Zend\Mvc\Application */
		/* @var $serviceManager \Zend\ServiceManager\ServiceManager */
		/* @var $eventManager \Zend\EventManager\EventManager */
		/* @var $request \Zend\Http\PhpEnvironment\Request */
		/* @var $response \Zend\Http\PhpEnvironment\Response */
		/* @var $router \Zend\Router\Http\TreeRouteStack */
		/* @var $routeMatch \Zend\Router\Http\RouteMatch */
		/* @var $viewManager \Zend\Mvc\View\Http\ViewManager */
		/* @var $resolver \Zend\View\Resolver\AggregateResolver */
		/* @var $renderer \Zend\View\Renderer\PhpRenderer */
		/* @var $exceptionStrategy \Zend\Mvc\View\Http\ExceptionStrategy */
		/* @var $result \Zend\View\Model\ViewModel */

		$application = $event->getApplication ();
		$serviceManager = $application->getServiceManager ();
	//	$eventManager = $application->getEventManager ();
	//	$request = $application->getRequest ();
		$response = $application->getResponse ();
	//	$router = $event->getRouter ();
	//	$routeMatch = $event->getRouteMatch ();
		$viewManager = $serviceManager->get ('ViewManager');
	//	$resolver = $viewManager->getResolver ();
	//	$renderer = $serviceManager->get ('ViewRenderer');
	//	$exceptionStrategy = $viewManager->getExceptionStrategy ();
	//	$notFoundStrategy = $viewManager->getRouteNotFoundStrategy ();

		// controller plugins
		/* @var $controllerPluginManager \Zend\Mvc\Controller\PluginManager */
		/* @var $flashMessenger \Zend\Mvc\Controller\Plugin\FlashMessenger */
		/* @var $redirect \Zend\Mvc\Controller\Plugin\Redirect */

		$controllerPluginManager = $serviceManager->get ('ControllerPluginManager');
	//	$flashMessenger = $controllerPluginManager->get ('FlashMessenger');
	//	$redirect = $controllerPluginManager->get ('Redirect');

		// this is only for HTTP response, do not continue otherwise
		if (!$response instanceof \Zend\Http\Response) {
			return;
		}
	//	$code = $response->getStatusCode () ?: 500; // default

		// get the current error code
	//	$error = $event->getError ();

		// the content of the page
		$result = $event->getResult ();

		// handle error exception
		if ($event->getParam ('exception')) {
			/* @var $exception \Exception */
			$exception = $event->getParam ('exception');

			// exception error message
			$message = $exception->getMessage ();
			$result->setVariable ('message', $message);

			// exception error code
			if ($exception->getCode ()) {
				$code = $exception->getCode ();
			}
		}

		// $message = 'hello there';
		// $flashMessenger->addErrorMessage($message);
		// return $redirect->refresh();
	}
}