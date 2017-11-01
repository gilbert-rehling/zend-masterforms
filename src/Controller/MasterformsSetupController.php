<?php
namespace Masterforms\Controller;

use Masterforms\StdLib\Exception;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class MasterformsSetupController extends AbstractActionController
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

    private $storage;

    private $tables = array(
        1 => 'masterforms_help',
        2 => 'masterforms_category',
        3 => 'masterforms_fields',
        4 => 'masterforms_fieldsets',
        5 => 'masterforms_forms',
        6 => 'masterforms_form_fields',
        7 => 'masterforms_form_data',
        8 => 'masterforms_admin_accounts',
        9 => 'masterforms_admin_browsers',
        10 => 'masterforms_admin_components',
        11 => 'masterforms_admin_tracking'
    );

    private $messages = array(
        'masterforms_help'              => 'Help table successfully created',
        'masterforms_category'          => 'Category table successfully created',
        'masterforms_fields'            => 'Fields table successfully created',
        'masterforms_fieldsets'         => 'Fieldsets table successfully created',
        'masterforms_forms'             => 'Forms table successfully created',
        'masterforms_form_fields'       => 'Form fields table successfully created',
        'masterforms_form_data'         => 'Form data table successfully created',
        'masterforms_admin_accounts'    => 'Admin account table successfully created',
        'masterforms_admin_browsers'    => 'Admin browsers table successfully created',
        'masterforms_admin_components'  => 'Admin components table successfully created',
        'masterforms_admin_tracking'    => 'Admin tracking table successfully created'
    );

    protected $completed = array();

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
        $this->storage             = $masterformsService->getStorage();
    }

    /**
     * Displays the setup progress
     *
     * @return ViewModel
     * @throws \Exception
     */
    public function setupAction()
    {
        $message = false;
        $content = '';
        //$this->storage->step = 0;
        //$this->storage->started = null;
        //$this->storage->completed = null;
        //die;

        try {
            $storage = $this->masterformsService->getStorage();
            if (isset($this->storage->step) && $this->storage->step >= 1 && $this->storage->started !== null) {

                // continue building the iterations
                $step = $this->storage->step + 1;
                $this->storage->step++;

            } else {
                $storage->step    = 1;
                $storage->started = time();
                $this->storage->completed = array();
                $step = 1;
            }

            // prevent this from rerunning if the page is reloaded after completion
            if ($step <= 11) {

                // call the setup function and pass the name of the table to setup
                $result = $this->masterformsService->setupDatabase($this->tables[$step]);

                if ($result && isset($this->messages[$result])) {

                    $this->storage->completed[] = $this->messages[$result];
                    $content = '';

                    foreach ($this->storage->completed as $complete) {
                        $content .= '<p>' . $complete . '.</p>';
                    }

                    if ($step < 11) {
                        return $this->redirect()->refresh();
                    }

                } else {
                    $message['error'] = "Unable to import " . $message['error'] . 'database table';
                    $this->flashMessenger()->addMessage($message);
                    return $this->redirect()->toRoute('masterforms/error');
                }
            } else {
                $content = 'Database setup has completed...';
            }

        }
        catch (Exception\InvalidQueryException $e) {
            $message['error'] = $e->getMessage();
            $this->flashMessenger()->addMessage($message);
            return $this->redirect()->toRoute('masterforms/error');

        }
        catch (\Exception $e) {
            throw($e);
        }

        if ($this->flashMessenger()->hasMessages()) {
            $message = $this->flashMessenger()->getMessages();
            $message = $message[0];
        }

        return new ViewModel([
            'activeMenuItemId' => 'masterforms/admin/setup',
            'pageTitle' => $this->config['setupTitle'],
            'subHeading' => 'Database and Initialisation',
            'content'   => $content,
            'message'   => $message
        ]);
    }
}