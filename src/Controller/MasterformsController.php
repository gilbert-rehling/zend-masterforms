<?php
namespace Masterforms\Controller;

use Masterforms\StdLib\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MasterformsController extends AbstractActionController
{
    /**
     * Dashboard Service
     * @var \Masterforms\Service\MasterformsService
     */
    protected $masterformsService;

    /**
     * Masterforms/Config
     * @var $config;
     */
    private $config;

    /**
     * Constructor is used for injecting dependencies into the controller.
     *
     * MasterformsController constructor.
     * @param $masterformsService
     */
    public function __construct($masterformsService)
    {
        $this->masterformsService  = $masterformsService;
        $this->config              = $masterformsService->getConfig();
    }

    /**
     * Runs a setup test to determine if Masterforms has been completely setup
     *
     * @return ViewModel
     */
    public function masterformsAction()
    {
        $message = false;
        $content = false;

        try {
            $result  = $this->masterformsService->isSetupTest();

        }
        catch (Exception\InvalidQueryException $e) {
            $message['error'] = $e->getMessage();
            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('masterforms/error');

        }
        catch (\Exception $e) {
            var_dump($e->getMessage()); die(" its broken!!");
        }

        if ($this->flashMessenger()->hasMessages()) {
            $message = $this->flashMessenger()->getMessages();
            $message = $message[0];
        }

        return new ViewModel([
            'activeMenuItemId' => 'masterforms',
            'pageTitle' => $this->config['welcomeTitle'],
            'content'   => $content,
            'message'   => $message
        ]);
    }

    public function errorAction()
    {
        $message = false;
        $content = false;

        if ($this->flashMessenger()->hasMessages()) {
            $message = $this->flashMessenger()->getMessages();
            $message = $message[0];
        }

        return new ViewModel([
            'activeMenuItemId' => 'masterforms/error',
            'pageTitle' => $this->config['errorsTitle'],
            'message' => $message,
            'content' => $content
        ]);
    }
}