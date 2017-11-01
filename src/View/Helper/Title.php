<?php

namespace Masterforms\View\Helper;

class Title extends AbstractViewHelper
{

    /**
     * Page title
     *
     * @var string
     */
    protected $title = null;

    /**
     * @var array
     */
    protected $options=[];
    /**
     * Invoke the plugin
     *
     * @param string $title
     * @return string
     */
    public function __invoke ($title = null, $showInHeadTitle = true , $options=[] )
    {
        if (is_object($title) && method_exists($title, '__toString')) {
            $title = (string) $title;
        }
        $renderer = $this->getView();

        if (null !== $title) {
            $this->title = $title;
            $renderer->viewModel()->title = $title;
            $renderer->layout()->title = $title;

            // display the title in the <head>
            $this->showInHeadTitle((bool) $showInHeadTitle);

            if($options && is_array($options) && !empty($options)){
                $this->options=$options;
                $renderer->viewModel()->options=$options;
                $renderer->layout()->options=$options;
            }
        }


        return $this;
    }


    /**
     * Whether to add the title in the HTML <head><title></title></head> tag
     *
     * @param boolean $flag
     * @return SubTitle
     */
    public function showInHeadTitle ($flag = true)
    {
        // set the <head><title>
        if ($flag == true) {
            $renderer = $this->getView();
            $headTitle = $renderer->headTitle(null);
            $headTitle($this->title);
        }
        return $this;
    }



    /**
     * Magic method when object is treated as a string or echoed
     *
     * @return string
     */
    public function toString ()
    {
        $renderer = $this->getView();
        $translator = $this->getTranslator();
        $title = $renderer->layout()->title ?  : '';
        return $translator->translate($title);
    }
}