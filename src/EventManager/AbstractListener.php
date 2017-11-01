<?php

namespace Savve\EventManager;

use Savve\EventManager\Event;
use Savve\Service\ServiceRetrieverTrait;

use Zend\ServiceManager\ServiceLocatorInterface;
//use Zend\ServiceManager\ServiceLocatorAwareTrait;
use Zend\Http\PhpEnvironment\Response;
use Zend\Mvc\Plugin\Prg\PostRedirectGet;

/**
 * Convenience methods
 *
 * @method \Savve\Service\AbstractService service() service($serviceName)
 * @method \Savve\Form\Form getForm() getForm($formName)
 * @method /\Savve\InputFilter\InputFilter inputFilter() inputFilter($inputFilterName)
 * @method /\Savve\Hydrator\AbstractHydrator|\Savve\Hydrator\AggregateHydrator hydrator() hydrator($hydratorName)
 * @method /\Savve\Entity\ArrayObject entity() entity($entityName)
 * @method \Savve\Table\TableGateway table() table($tableName)
 * @method /\Savve\Filter\AbstractFilter filter() filter($filterName)
 * @method /\Savve\Validator\AbstractValidator validator() validator($validatorName)
 * @method \Savve\View\Model\ViewModel block() block($blockName)
 * @method \Zend\Config\Config|array config() config($configName)
 * @method \Zend\ServiceManager\ServiceManager getServiceManager()
 * @method \Zend\Mvc\Application getApplication()
 * @method \Zend\Router\Http\RouteMatch routeMatch()
 * @method \Zend\Router\Http\TreeRouteStack router()
 * @method \Zend\Session\AbstractContainer session() session($namespace)
 * @method \Zend\I18n\Translator\Translator translator()
 */
abstract class AbstractListener implements ServiceLocatorInterface
{
    use ServiceRetrieverTrait;

    /**
     * Get the form post data from PostRedirectGet or Request
     *
     * @param string $type Type of request to use getting the POST params
     * @return array
     */
    public function getPost ($type = 'post')
    {
        /* @var $request \Zend\Http\PhpEnvironment\Request */
        /* @var $response \Zend\Http\PhpEnvironment\Response */
        /* @var $prg \Zend\Mvc\Plugin\Prg\PostRedirectGet */

        $mvcEvent = $this->getApplication()
            ->getMvcEvent();
        $request = $mvcEvent->getRequest();
    //    $response = $mvcEvent->getResponse();
    //    $routeMatch = $mvcEvent->getRouteMatch();
    //    $controllerName = $routeMatch->getParam('controller');
     //   $controller = $mvcEvent->getTarget();

        // $controllerPluginManager = $this->getServiceLocator()
        //    ->get('ControllerPluginManager');
        $prg = $this->plugin(PostRedirectGet::class);// $controllerPluginManager->get('PostRedirectGet');

        switch (strtolower($type)) {
            case 'prg':

                $prg = new $prg($request->getRequestUri(), true);
                $post = (!($prg instanceof \Zend\Http\PhpEnvironment\Response) && ($prg !== false)) ? $prg : null;
                break;

            default:
            case 'post':
                $post = $request->isPost() ? $request->getPost() : null;
                break;
        }
        return $post;
    }
}