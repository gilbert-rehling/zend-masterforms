<?php
namespace Masterforms\Service;

use Masterforms\StdLib\Exception;
use Masterforms\Entity;
use Zend\Session\Container as SessionContainer;
use Masterforms\Doctrine\Service\AbstractService;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\ORMException;

use Zend\Session\Config\ConfigInterface;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Sql\Sql;

use Interop\Container\ContainerInterface;


class MasterformsSetupService extends AbstractService
{

    /**
     * Learner options
     *
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var SessionContainer
     */
    protected $masterformsStorage;

    /**
     * @var $container ContainerInterface
     */
    protected $container;

    /**
     *  Constructor
     *
     * MasterformsSetupService constructor.
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param ConfigInterface $config
     * @param $masterformsStorage
     * @param $container ContainerInterface
     */
    public function __construct (\Doctrine\ORM\EntityManager $entityManager, $config, $masterformsStorage, ContainerInterface $container)
    {
        $this->config = $config;
        $this->masterformsStorage = $masterformsStorage;
        $this->container          = $container;

        parent::__construct($entityManager);
    }

    /**
     * Fetches the Masterforms configuration from this service
     *
     * @return bool|ConfigInterface
     */
    public function getConfig()
    {
        if (count($this->config)) {
            return $this->config;
        }
        return false;
    }

    /**
     * @return SessionContainer
     */
    public function getStorage()
    {
        return $this->masterformsStorage;
    }

    /**
     * Get MasterformsHelp doctrine entity repository
     *
     * @return \Masterforms\Doctrine\Repository\AbstractRepository
     */
    public function helpRepository ()
    {
        $entityManager = $this->getEntityManager();
        return $entityManager->getRepository(Entity\MasterformsHelp::class);
    }

    public function setupDatabase( $table ) {

    //    $config = $this->container->get('Config');
    //    $doctrine = $config['doctrine']['connection']['orm_default']['params'];

    //    $adapter = new \Zend\Db\Adapter\Adapter([
    //        'driver'   => 'Mysqli',
    //        'database' => $doctrine['dbname'],
    //        'username' => $doctrine['user'],
    //        'password' => $doctrine['password'],
    //    ]);

    //    $dblink = mysqli_connect( $doctrine['host'], $doctrine['user'], $doctrine['password'], $doctrine['dbname'] );
    //    if ($dblink->connect_errno) {
    //        echo "Failed to connect to Database server: (" . $dblink->connect_errno . ") " . $dblink->connect_error;
    //        die;
    //    }

        try {

            // get the table CREATE_TABLE query
            $query = $this->getCreate( $table );

            if ($query) {

                //$adapter->query( $query );

            //    $result = $adapter->query($query, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
            //    $this->entityManager->getConnection()->executeQuery($query, array());
            //    $statement = $adapter->query($query); //, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE);
             //   $statement->prepare();
            //    if (!($result    = $dblink->query( $query ))) {
            //  if (!($result = $this->entityManager->getConnection()->executeQuery($query, array()))) {
            //        var_dump($table);
            //        var_dump($query);
                //    echo "<br />Table creation failed: (" . $dblink->errno . ") " . $dblink->error;
                //    echo "<br />Table creation failed: (" . $hello . ") " . $dblink->error;
               //     die(' dying!!');

                if ($result = $this->entityManager->getConnection()->executeQuery($query, array())) {

                    // successful table creation - get any related INSERT data
                    $query = $this->getInsert($table);
                //    var_dump($query);

                    if ($query) {

                    //    $statement = $adapter->query($query);
                    //    if (!($result = $dblink->query( $query ))) {
                    //    if (!($result = $this->entityManager->getConnection()->executeQuery($query, array()))) {
                    //    //    echo "<br />Data insert failed: (" . $dblink->errno . ") " . $dblink->error;
                    //        var_dump($table);
                    //        var_dump($query);
                     //       die(' still dying!');
                    //    }

                        if ($result = $this->entityManager->getConnection()->executeQuery($query, array())) {
                            return $table;
                        }
                    }
                    return $table;
                }
            }
            return false;

        }
        catch (ORMException $e) {
            echo "<br />Database action failed: (" . $e->getMessage() . ")";
            exit;
        }
        catch(\Exception $e) {
            throw $e;
        }
    }

    public function getCreate ( $table)
    {
        $sql = array(
            'masterforms_help' => "CREATE TABLE IF NOT EXISTS masterforms_help (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `help_title` varchar(100) NOT NULL,
              `help_category` varchar(100) NOT NULL,
              `help_description` text NOT NULL,
              `help_data` text NOT NULL,
              `help_status` tinyint(1) NOT NULL DEFAULT '0',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Help - Primary Help directory data repository' AUTO_INCREMENT=100000",

            'masterforms_category' => "CREATE TABLE IF NOT EXISTS masterforms_category (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `parent` int(11) DEFAULT '0',
              `order` int(11) NOT NULL,
              `category_name` varchar(50) NOT NULL,
              `remote_category` int(6) NOT NULL,
              `datecreated` datetime NOT NULL,
              `status` tinyint(4) DEFAULT NULL,
              PRIMARY KEY (`id`),
              KEY `masterforms_category_FI_1` (`parent`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=100000",

            'masterforms_fields' => "CREATE TABLE IF NOT EXISTS masterforms_fields (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `field_type` varchar(20) NOT NULL,
              `field_inline` tinyint(1) NOT NULL DEFAULT '0',
              `field_label` varchar(100) NOT NULL,
              `field_label_break` tinyint(1) NOT NULL DEFAULT '0',
              `field_name` varchar(100) NOT NULL,
              `field_attributes` varchar(250) NOT NULL,
              `field_required` tinyint(1) NOT NULL DEFAULT '0',
              `field_placeholder` varchar(250) NOT NULL,
              `field_length` int(3) NOT NULL,
              `field_size` int(3) NOT NULL,
              `field_options` varchar(512) NOT NULL,
              `field_information` text NOT NULL,
              `field_status` tinyint(1) NOT NULL DEFAULT '0',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Fields Storage - Stores all configured Fields for reuse' AUTO_INCREMENT=100000",

            'masterforms_fieldsets' => "CREATE TABLE IF NOT EXISTS masterforms_fieldsets (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `fieldset_formid` int(11) NOT NULL,
              `fieldset_legend` varchar(50) NOT NULL,
              `fieldset_type` varchar(5) NOT NULL,
              `fieldset_style` varchar(500) NOT NULL,
              `fieldset_order` int(2) NOT NULL,
              `fieldset_parent` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Fieldsets - Stores all Fieldset data for generated forms' AUTO_INCREMENT=100000",

            'masterforms_form_fields' => "CREATE TABLE IF NOT EXISTS masterforms_form_fields (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `field_form_id` int(11) NOT NULL,
              `field_fieldset_id` int(11) NOT NULL,
              `field_id` int(11) NOT NULL,
              `field_order` tinyint(2) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Form/Fieldset/Field Storage - Tracks the direct relationships between Forms, Fieldsets and Fields ' AUTO_INCREMENT=100000",

            'masterforms_forms' => "CREATE TABLE IF NOT EXISTS masterforms_forms (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `form_title` varchar(100) NOT NULL,
              `form_category_id` int(11) NOT NULL,
              `form_description` text NOT NULL,
              `form_show_description` tinyint(1) NOT NULL DEFAULT '0',
              `form_instance` varchar(50) NOT NULL,
              `form_fields` text NOT NULL,
              `form_fieldsets_outer` text NOT NULL,
              `form_fieldsets_inner` text NOT NULL,
              `form_parameters` text NOT NULL,
              `form_status` tinyint(1) NOT NULL DEFAULT '0',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Form Details - Primary storage for Form information and parameters' AUTO_INCREMENT=100000",

            'masterforms_form_data' => "CREATE TABLE IF NOT EXISTS masterforms_form_data (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `data_form_id` int(11) NOT NULL,
              `data_form_title` varchar(70) NOT NULL,
              `data_form_instance` varchar(50) NOT NULL,
              `data_form_data` longtext NOT NULL,
              `data_form_category_id` int(11) NOT NULL,
              `data_item_id` int(11) NOT NULL COMMENT 'links to associated item(s)',
              `user_id` int(11) NOT NULL,
              `customer_code` varchar(50) NOT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              `updated_by` int(11) NOT NULL,
              `status` tinyint(1) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='MasterForms Form Data Storage - Stores all submitted Form data for for locally submitted Forms' AUTO_INCREMENT=100000",

            'masterforms_admin_tracking' => "CREATE TABLE IF NOT EXISTS `masterforms_admin_tracking` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `key` varchar(65) NOT NULL,
              `type` varchar(20) NOT NULL,
              `version` varchar(20) NOT NULL,
              `ref` varchar(255) NOT NULL,
              `ip` varchar(15) NOT NULL,
              `domain` varchar(200) NOT NULL,
              `count` int(11) NOT NULL,
              `first` datetime NOT NULL,
              `last` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tracking remote component usage' AUTO_INCREMENT=100000",

            'masterforms_admin_components' => "CREATE TABLE IF NOT EXISTS `masterforms_admin_components` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `key` varchar(65) NOT NULL,
              `type` varchar(20) NOT NULL,
              `version` varchar(20) NOT NULL,
              `subscription` varchar(65) NOT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tracks updates of the MasterForms component' AUTO_INCREMENT=100000",

            'masterforms_admin_accounts' => "CREATE TABLE IF NOT EXISTS `masterforms_admin_accounts` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `user_id` int(11) NOT NULL,
              `key` varchar(65) NOT NULL,
              `type` varchar(20) NOT NULL,
              `subscription` varchar(65) NOT NULL,
              `created_at` datetime NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Stores accounts associated with components' AUTO_INCREMENT=100000",

            'masterforms_admin_browsers' => "CREATE TABLE IF NOT EXISTS `masterforms_admin_browsers` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `key` varchar(65) NOT NULL,
              `ref` varchar(255) NOT NULL,
              `ip` varchar(15) NOT NULL,
              `browser` varchar(255) NOT NULL,
              `count` int(11) NOT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Tracks browsers access to components' AUTO_INCREMENT=100000"

        );

        return $sql[ $table ];
    }

    public function getInsert( $table ) {

        $sql = array(
            'masterforms_help' => "INSERT INTO `masterforms_help` (`id`, `help_title`, `help_category`, `help_description`, `help_data`, `help_status`, `created_at`, `updated_at`) VALUES
(100000, 'Welcome to MasterForms', 'General', 'Thank-you for choosing MasterForms as your Web-Form-Builder! We hope you enjoy using this component as much as we enjoyed building it.', '<h3><span style=\"font-size: 12pt; font-family: Times New Roman;\">Masterforms Introduction</span></h3>\r\n<p>MasterForms was designed to be easy to use. Using MasterForms you can create web-forms without and programming knowledge. The builder-interfaces have detailed inline help and instructions to assist you in understanding the various form elements that you can use. In fact, our form builders are so informative, you will probably learn a thing or two about HTML whilst you use them.</p>\r\n<p>MasterForms also comes preloaded with Forms that are ready-to-use, and there are also several example Forms to help you understand some of the more complex abilities within this component.</p>\r\n<p>There are two versions of MasterForms. This is the &quot;Starter&quot; version, and is our Free community form builder. It&#39;s a good way to get started building Web-Forms and has plenty of capabilities without any real limitations. To see a detailed reference showing the difference between the Starter and Pro versions, please visit our website for more information or <a href=\"http://www.masterformsbuilder.com/master-forms\" target=\"_blank\" title=\"Click to open MasterForms comparison page at www.masterformsbuilder.com\">Click Here</a> to go directly to the comparison page.</p>\r\n<p>To get started using this component, simply select either the Forms or Fields links from the component sub-menu. These are the two primary sections you will use for building and editing your forms and for creating new fields or editing existing fields.</p>\r\n<p>The Getting-Started help category has detailed information on how to make the most of the default forms and also how to get stated building your own. Click on the Help links to to read the getting started information.</p>\r\n<h5>Upgrading:</h5>\r\n<p>Should you wish to, you can upgrade to the <strong>MasterForms Pro</strong> version at any time by purchasing a subscription from <a href=\"http://www.masterformsbuilder.com\" target=\"_blank\">www.masterformsbuilder.com</a>. If you upgrade to \"Pro\", all Forms you have created and all submitted From-Data will be preserved and will be available in the new version. Pro has many extended features that give you a powerful form building system, including the ability to generate Forms based on existing database tables.</p>\r\n<p>Upgrading to the latest &#39;Starter Version&#39; can also be done at anytime, and similarly this process will not interrupt any of you previous work.</p>\r\n<p>Upgrading&nbsp; MasterForms Starter to a new version can be done automatically via the Joomla Extension manager. Should a new version become available, you will receive a visual notification message in the main-console page for this component. You can then follow the link directly to the extension manager to update your current &#39;Starter&#39; version. The Pro versions can also be updated via the Joomla extension manager just like any other professional Joomla component, and it also has automatic updates for the Help section.</p>\r\n<h5>Comments:</h5>\r\n<p>If you have any comments, please visit our forum at <a href=\"http://www.masterformsbuilder.com/forum\" target=\"_blank\">www.masterformsbuilder.com/forum</a> (account required). We welcome all feedback whether its positive or negative. We aim to please all and are continually looking for ways to improve this component with new features.</p>\r\n<h5>Bugs:</h5>\r\n<p>In the unexpected event that you encounter a Bug, please use the Bug-Reporting tool we have incorporated directed into this component. Its easy and fast to use, and will enable us to roll out a bug fix version as soon as humanly possible, and, you will be notified via email as soon as its available.</p>\r\n<p>Once again, than you for choosing MasterForms as your Web-Form and Data-Storage component, and remember, we are here to help!</p>', 1, '2017-03-10 10:03:50', '2017-03-10 10:03:52'),
(100001, 'Using the Default Forms', 'Getting Started', 'This tutorial will help you get started using MasterForms immediately.  You will learn how to implement the default forms using the MasterForms Joomla module.', '<h3>Getting Started with the Default Forms</h3>\r\n<p>MasterForms is provided &#39;off the shelf&#39 with several default forms that are ready to use straight away. These include alternative Contact-Us and Login forms, a Newsletter subscription form and a Testimonials form.&nbsp;</p>\r\n<p>These forms can be placed anywhere within your website using the Masterforms Module. This tutorial expects you to have some previous experience creating a web form.</p>\r\n<p>In this tutorial we will be using the Contact Us form.&nbsp;</p>\r\n<p>The first required step is to Enable the form. (All default forms are disabled when you first install this component). There are several methods you can use to Enable/Disable forms and also form fields. The most direct method is to load the Forms directory list in the Forms section. Click on the Red-Cross on the far right-hand side. After the page reloads the form will be enabled and this can be confirmed by a Green Tick.</p>\r\n<p>After you have enabled the form it will be available in the Form-Selector in the masterForms Module. Select the Contact Us form from the list. Any form which you create in the future may be displayed using the module without any need for further modifications, however, you may need to implement some custom CSS to user certain positions within your website&#39;s template.</p>\r\n<p>The Contact Us form has several options that can be Set within the module parameters section. Only one is a required option. That is the Email Address for the receiver value. If this option is not set to a valid email address the form will not load at all. There is also an option to &quot;Send a Copy to Sender&quot; which enables the action of sending a copy of the submitted Contact form data to the senders email address. This action is only available in the contact-form mode.</p>\r\n<p>All email address entered into MasterForms will be validated by our custom email validation function. By default, data submissions from Contact Form will not be visible in the Data List in the front-end view, however the submitted data will be saved to the masterforms_data table in the database, and will always be visible in the backend. You can also set a Form-Option allowing you to send the submitted data to an Email-Address. This can be applied to any form that you create.</p>\r\n<p>There is an option in the MasterForms Options targeting Data-Access parameters which enables the listing of Contact-form-data in the website&#39;s front-end, and doing so will enable the data-item-view for submitted Contact form data.</p>', 1, '2017-03-10 08:03:57', '2017-03-10 04:03:17'),
(100002, 'Creating Forms - Overview', 'Getting Started', 'This tutorial will provide an overview of the processes that you will use to create a new form. We will run through the complete process of creating a form and also outline some of the benefits of our form builder during the process.', '<h3>Building Your First Master Form</h3>\r\n<p>A Form is a collection of HTML elements that are combined and placed within a form tags wrapper.</p>\r\n<p>Modern forms use HTML5 compatable input fields. MasterForms can use all of the new HTML5 elements when creating forms.</p>\r\n<p>MasterForms applies a logical process where the first step to creating a form is generating some field entities.</p>\r\n<p><strong>Note: </strong>When you create a field in MasterForms you can re-use the same field in as many forms as you require. It is therefore good practice to use a practical naming convention when creating your field entities. For example: if your where to create an Email Address field you can use the same field later on in other forms, If you intend to create several forms, it may serve you well to plan them (map them out) beforehand so that you have a guide that will assist you in determining which fileds can easliy be reused.</p>\r\n<p>In this tutorial we will create a survey form which you can personalise to suit your own needs if you wish to do so. We will use a step by step process from here on.</p>\r\n<p><strong>Step 1 (getting ready): </strong>Well be creating five new fields for the new Survey form. Before we get started creating new fields, lets have a look and see which of the existing fields we can reuse. Click on the Fields menu or submenu so list the existing fields. When the Fields Manager directory loads you can see all of the existing fields.</p>\r\n<p><strong>Note: </strong>take note of the two fields columns on the left (Label and Name). The Label field is the text which is displayed within the form adjacent to the corresponding input field. When you create a new field the text entered for the Label is reconfigured and automatically used as the fields hidden name element. As you can see from the sample fields in the list, in some cases you may want to use an alternative name value, as we have done for the Contact-Form fields. The name value can be changed at any time, but its important that it contains no blank spaces.</p>\r\n<p>Using the Select a Form here dropdown, choose the Testimonial form. These fields are a good example of generic Label / name usage. For our Survey form we will reuse four of these fields (Your name, Your Email, Your Country and Your Website). To complete our form we will create a few new fields. (Joomla Version, Rating, Date, Coding Experience, Comment)</p>\r\n<p><strong>Step 2 (creating fields): </strong>In the Fields Manager click the New button. You will notice that the fileds list scrolls down, and a Helper Window appears on the right hand side. The helper window is designed to assists you with the process of creating fields and applying the many variations of field attributes.</p>\r\n<p>We&#39;ll create the Joomla Version selector first. Choose select as the Field Type value. The Helper Window will now display information about select elements.</p>\r\n<p>The Field Attributes automatically selected option which is generally required for select elements. In the indented text box indented just below type in \"Please select your Joomla version\".</p>\r\n<p>Type \"Joomla Version\" as the Field Label. The Field Name will be automatically populated.</p>\r\n<p>In the Field Options textarea enter the follwing: 1.5,1.6,2.0,2.5,2.6,3.0,3.3,3.5 (you can list all of the major Joomla versions if you wish to)</p>\r\n<p>Select Published for the Field Status value.</p>\r\n<p>Click the Add New Field button to finish creating the new field. The new field will now be visible at the top of the Field List at the bottom of the page.</p>\r\n<p>Click New to start creating the Rating field: select range as the Field Type. As mentioned in the Helper Window instructions, select min-max as the Field Attributes value. Enter the digit 1 directly after min: and before the comma, and 10 directly after max:. This configuration will create a range selector that can be set anywhere between 1 and 10 inclusive.&nbsp;The Helper-Window instructions for the rating field type explain how to set a decimal value for the increment/decrement which allows you to have a much finer grained range selection. Select Published from the Field Status and click Add New Field to save the new range field.</p>\r\n<p>Click New to start creating the Date field: select date as the Field Type.&nbsp; This will create a date field with limited browser support. Alternatively, you can cheese the calendar Field Type. This is not a HTML5 field, its a Joomla exclusive field type. The calendar will generate a date-picker which let you pick a date from a popup calendar. The HTML5 date field will render a date picker in a limited selection of browsers. For this exercise, switch to calendar. Enter \"Date\" into the Field Label element. Select Published as the Field Status and then click Add New Field</p>\r\n<p>Click New to start creating the Coding Experience field: choose select as the Field Type. For this field we will use a variation on the options that we input in order to demonstrate the possible variations. Enter Coding Experience as the Field Label. In the indented Field Attributes enter \"Please select you coding experience\" as the default option text for the select. Then copy paste this into the Field Options: beginner_level:Absolute Beginner,novice_level:Just a Novice,student_level:Student Level,competent-programmer:Competent level,expert:I am an Expert,master:Master Programmer</p>\r\n<p><strong>Hint: </strong>regardless of the type of field you are creating and especially for fields with options, its best practice to never used spaces in option values or for field names. Always use either a dash (-) or underscore (_) to join words together. (beginner_level:Beginner Level) In the example I have used several styles.</p>\r\n<p>Select Published as the Field Status and then click Add New Field to finish adding the Coding Experience field.</p>\r\n<p>Click New to start creating the Comment field: select textarea as the Field Type. For text ares the Field Attributes are automatically populated with workable defaults. You only need to adjust the cols: and rows: value as you deem fit.</p>\r\n<p><strong>Hint: </strong>any attributes that you set, labels, options etc can all be edited at any time later on. All fields in masterForms can be adjusted to suit your needs at anytime.</p>\r\n<p>In the Field Placeholder enter this text: \"Please enter any comments you have regarding our software in here\". Many field types support the placeholder attribute. It allows you to define information relevant to the field. The Field Options textarea is a good example of placeholder usage. Select Published from the Field Status and click Add New Field to complete this step of our form building process.</p>\r\n<p>&nbsp;<strong>Step 2 (creating a form):&nbsp;</strong>Click on Forms in the sub-menu or main menu MasterForms -&gt; Forms. The Form Manager directory will load and display all the (preloaded) forms.</p>\r\n<p><strong>Hint: </strong>the Forms directory can be filtered by Category, Label or Description.</p>\r\n<p>Click on New to start creating a new form. The Create a New Form interface does not have a Save or Save and Exit button like may other Joomla components. All elements are saved after the form field is exited (after the blur action is detected) after you Confirm the action. When building a new Form, always populate the Title element first. After the Title is saved, the form will be displayed in the directory. If you exit the builder you can relod the form to continue creating it later. Enter \"Survey\" in the Title field and then click anywhere outside the field, or just hit the Tab key on your keyboard. A confirm box will appear prompting you \"Save Form Title and begin process?\" Click OK to save the title. You have now started building the form and can return to the teak at any time by selecting the Survey form from the directory list. If you click on Cancel you can re-enter the Title field, blur (exit) the field again and the confirmation will run again.</p>\r\n<p><strong>Hint: </strong>if you try to proceed without first setting theTitle value you will recieve an alert message indication that the Title must be set first.</p>\r\n<p>Select a Category for the form. If you wish to create a new Category you will have to click on the Categories submenu or main link. There is a small tutorial about Category management that can assist you with that process if you require assistance. After creating a new category, select Forms then click on the Survey form heading to continue building the form. All the form element have the same Confirm to save behavior as the Title element. Select a category then blur (exit) the category selector.</p>\r\n<p><strong>Important: </strong>there is a primary difference between the Create a New Form and Editing a Form page functions. When creating a new form you can select a previously populated Fieldset and import it into your new form project. This process add the complete fieldset and the fields bouind to it directly into the new form. A full description of this functionailty can be found in the Forms tutorial.</p>\r\n<p>The Description element is optional, however, it will serve you well to add a short description of the form which can contain instructions or requirement for users to read before completing the form. Form descriptions can be displayed along with (above) the Form in the website. After you save the description you can enable the Show Description by selecting On if you want the description to be visible.</p>\r\n<p><strong>Hint: </strong>when you Hover the form-builder fields you will notice that the element become highlighted. The next two form-builder elements (Add a Section and Add a Fieldset) are used to create the HTML elements (Fieldsets) that will contain the form fields.The Add a Section Outer Fieldset is required as it will bind the form fields and allows you the label the form with a Legend HTML element.</p>\r\n<p>Its good practice to always use at least one Fieldset to wrap all of the forms fields. Enter Survey Form into the Add a Section element and then blur (exit) the field. The fieldset will now be visible&nbsp;within the form preview pane. The entered value and context will also be displayed.</p>\r\n<p>Now that you have created a Fieldset, it will be visible in the first selector of the Add a Field section (Please Select an Outer-Fieldset). Select the Fieldset that you just created. Click on the Please Select a Field selector. You notice that the new fields you created will be listed at the top. Scroll down and select the Your name field. Click on Add Field To Form to add the field. Ths field will then appear in the preview-pane. Use the same process to add the Your Email, Your Country, Your Website fields.</p>\r\n<p>For this exercise, we will now create 2 special fields that are not Form Input fields. They are included in MasterForms as helpers to assist in creatinh rich media capabvle forms.</p>\r\n<p>Click on the Fields&nbsp; submenu, and then the New button. Scroll down to the bottom of the Field Type selector and select information. An overlay will appear which allows you to enter Text, Images, Links and ahost of other HTML elements. This field-type is intented to be used anywhere in your form where you need to show information (instructions) or images etc. Enter this text \"The following form fields help us get a better understanding of our customers\" (or choose your own words) The top of the modal has further information that explains the usage of Information field-types. When your ready click on Add Content. Enter \"Survey Information\" as the field label. Select Published as the field-staus then save the field.</p>\r\n<p>Another special field is the Php field-type. This field-type has been included to allow you to apply values from Joomla code objects into your form. Click New then select php from the Field Type selector. Select object from the Field Attributes selector. The text object: will appear in the indented field below. Enter request after the : (object:request) Enter \"Current Component:\" as the field-label, select Published as the field-status then save the new field.</p>\r\n<p>Return to the Survey form (Click Forms then select the Survey form label in the form-directory) Next, add the the Information field which you just created. Use the same procedure as the previous fields. (Select the Outer Fieldset, then select the Field then click Add Field To Form). You can see how the Information field-types are displayed in the preview-pane.</p>\r\n<p>Now add the 4 fields that you created at the beginning of this exercise. (Joomla Version, Rating, Date, Coding Experience, Comment)</p>\r\n<p>To complete this part of the exercise, add the Php field as the last field element.</p>\r\n<p><strong>Step 3 (form parameters) </strong>Form&nbsp;parameters are used to set behaviors and rules for each individual form. At the top of the Editing A Form page there is a link new to the main page title &gt;&gt; Modify Form Parameters &lt;</p>\r\n<p>There are currently four primary sections for setting form parameters. For this exercise we are only convered with the last section (Submission Parameters). The field builder can create Submit buttons, &nbsp;however, for the majority of form you create you wont need to do so. Sometime a form required several different submit buttons that can have different names / value combinations applied to them, however this is an relativeley advenaced usage case. For this form, enter \"Submit Survey\" into the Submit Button Text field. Enter \"Click to submit your survey results\" into the Submit Button Title. Select Jooma Tool Tip from the Submit Button Title Type.<span style=\"color: #ffffff; font-size: 13px; text-align: right; background-color: rgba(0, 0, 0, 0.8);\"><br /></span></p>\r\n<p><strong>Hint: </strong>all forms created with masterForms can have a custom action attribute set. This allows you to POST the submitted form data to another Joomla component and even to an external website script / url. This is set within the last field in the Submission Parameters section.</p>\r\n<p>Click on Set Parameters to complete the parameter setting process.</p>\r\n<p><strong>Linking the Form:&nbsp;</strong>MasterForms makes it easy to create menu items that link to forms. Joomla allows you to create hidden menu items that activate SEF Urls (routes) to pages without disclosing a publicy visible menu item. As with all Joomla components, you can always access a page / form using the native non SEF (search engine friendly) URIs.</p>\r\n<p>To link this new form to a menu item, click the Menus link in your admin template menu. Click the Menu Items submenu. Click the New button to begin generating a new menu item. Enter \"Survey Form\" into the Menu Title field. Click on the blue Select button adjacent the Menu Item Type field. &nbsp;Click on the MasterForms link and a list will be displayed. Click on the fourth item down Form Layout. The Details TAB will now have a new selector Form Selector, click on the selector and choose the Survey form from the list. Click on the Save &amp; Close button the complete this form building tutorial.</p>\r\n<p><strong>Data Retrieval:&nbsp;</strong>The new Survey form is ready to use. You wil be able to access it directly via the SEF link which you created for your site front-end view.</p>\r\n<p>All forms which are not using an external action will save their submitted data in the MasterForms data table for future access. You can create menu items that will display the saved data in a directory llsit on a form-by-form basis. Each saved item can then be viewed individually or downloaded as a PDF.</p>\r\n<p><strong>Alternative Form View:&nbsp;</strong>MasterForms comes packaged with a Joomla Module which provides a method to display both Forms and Data directories in any position within the template you are using on youe site. The Testimonials section on this website is loased from our Module. The data directory and form are both loaded via separate instances of our module, with the relative position controlled by the module ordering setting.</p>', 1, '2017-03-10 01:03:40', '2017-03-17 01:03:32')",

            'masterforms_category' => "INSERT INTO masterforms_category (`id`, `parent`, `order`, `category_name`, `remote_category`, `datecreated`, `status`) VALUES
(100000, 0, 0, 'Default Category', 0, '2017-03-09 03:03:06', 1),
(100001, 0, 1, 'Contact', 0, '2014-05-13 01:05:17', 1),
(100002, 0, 2, 'Testimonials', 0, '2014-05-13 01:05:46', 1),
(100003, 0, 3, 'Demo Forms', 0, '2017-03-17 01:03:47', 1)",

            '`masterforms_fields' => "INSERT INTO `masterforms_fields` (`id`, `field_type`, `field_inline`, `field_label`, `field_label_break`, `field_name`, `field_attributes`, `field_required`, `field_placeholder`, `field_length`, `field_size`, `field_options`, `field_information`, `field_status`, `created_at`, `updated_at`) VALUES
(100000, 'text', 0, 'ContactName', 1, 'contact_name', 'autofocus', 1, 'Please enter your full name', 0, 0, '', '', 1, '2014-05-11 12:36:46', '2017-03-15 09:57:36'),
(100001, 'email', 0, 'Your Email', 1, 'contact_email', '', 1, 'Please enter your email address', 0, 0, '', '', 1, '2014-05-11 12:38:03', '2014-05-11 02:35:00'),
(100002, 'tel', 0, 'Contact Number', 1, 'contact_number', '', 0, 'Please enter a contact number (mobile prefered)', 0, 0, '', '', 1, '2014-05-11 12:39:48', '2014-05-11 02:35:11'),
(100003, 'countrys', 0, 'Your Country', 1, 'country', 'option:Select a Country', 0, 'Please select your country', 0, 0, '', '', 1, '2014-05-11 12:40:50', '2014-05-11 02:35:36'),
(100004, 'textarea', 0, 'Your Message', 1, 'contact_message', 'cols:30,rows:6', 1, 'Please write your message or query here', 0, 0, '', '', 1, '2014-05-11 12:42:10', '2014-05-11 02:35:24'),
(100005, 'checkbox', 0, 'Send copy of message to me', 0, 'contact_copy', '', 0, 'Check this box to have a copy of submission sent to your email address', 0, 0, '', '', 1, '2014-05-11 12:44:50', '0000-00-00 00:00:00'),
(100006, 'select', 0, 'Testimonial Subject', 0, 'testimonial_subject', 'option:Please select a Testimonial subject', 1, 'Select the subject or related topic for this testimonial', 0, 0, 'MasterForms-Starter,MasterForms-Pro', '', 1, '2014-05-11 09:31:15', '0000-00-00 00:00:00'),
(100007, 'text', 0, 'Your Name', 0, 'your_name', 'autofocus,action:comment', 1, 'Please enter your full name - please enter any aliases into the associated comment field', 0, 0, '', '', 1, '2014-05-11 09:34:38', '2017-05-12 09:51:25'),
(100008, 'email', 0, 'Your Email', 0, 'your_email', '', 1, 'Please enter your email address', 0, 0, '', '', 1, '2014-05-11 09:35:29', '0000-00-00 00:00:00'),
(100009, 'textarea', 0, 'Testimonial', 0, 'testimonial_text', 'cols:30,rows:6', 0, 'Please write your testimonial here', 0, 0, '', '', 1, '2014-05-11 09:49:29', '0000-00-00 00:00:00'),
(100010, 'url', 0, 'Your Website', 0, 'your_website', 'defaultValue:http://', 0, 'Please indicate the website MasterForms is installed', 0, 0, '', '', 1, '2014-05-11 09:55:32', '2014-05-23 08:16:50'),
(100011, 'select', 0, 'Product Rating', 0, 'product_rating', 'option:Please select a rating', 0, 'Select your rating for the product: 1 = poor to 10 = excellent', 0, 0, '1,2,3,4,5,6,7,8,9,10', '', 1, '2014-05-11 10:00:33', '2014-05-23 08:18:45'),
(100012, 'countrys', 0, 'Your Country', 0, 'your_country', 'option:Select your country', 0, 'Please select your country', 0, 0, '', '', 1, '2014-05-11 10:16:02', '0000-00-00 00:00:00'),
(100013, 'text', 0, 'Subscribe Username', 0, 'user[name]', '', 0, 'Please enter your name', 0, 0, '', '', 1, '2014-05-16 09:44:17', '0000-00-00 00:00:00'),
(100014, 'text', 0, 'Subscribe Email', 0, 'user[email]', '', 0, 'Please enter your email', 0, 0, '', '', 1, '2014-05-16 09:44:57', '0000-00-00 00:00:00'),
(100015, 'hidden', 0, 'Option', 0, 'option', 'value:com_acymailing', 0, '', 0, 0, '', '', 1, '2014-05-16 09:48:48', '0000-00-00 00:00:00'),
(100016, 'hidden', 0, 'Ctrl', 0, 'ctrl', 'value:sub', 0, '', 0, 0, '', '', 1, '2014-05-16 09:49:25', '0000-00-00 00:00:00'),
(100017, 'hidden', 0, 'Hiddenlists', 0, 'hiddenlists', 'value:1', 0, '', 0, 0, '', '', 1, '2014-05-16 09:50:14', '0000-00-00 00:00:00'),
(100018, 'hidden', 0, 'Optin Task', 0, 'task', 'value:optin', 0, '', 0, 0, '', '', 1, '2014-05-16 09:52:20', '2014-05-18 03:35:09'),
(100019, 'hidden', 0, 'Acy Form Name', 0, 'acyformname', 'value:formAcymailing1', 0, '', 0, 0, '', '', 1, '2014-05-16 09:53:46', '0000-00-00 00:00:00'),
(100020, 'hidden', 0, 'Visible Lists', 0, 'visiblelists', '', 0, '', 0, 0, '', '', 1, '2014-05-16 09:54:45', '0000-00-00 00:00:00'),
(100021, 'radio', 0, 'Format', 0, 'user[html]', '', 0, '', 0, 0, '0:Text,1:HTML', '', 1, '2014-05-16 09:56:31', '0000-00-00 00:00:00'),
(100022, 'text', 0, 'User Name', 1, 'username', '', 1, 'your username', 0, 0, '', '', 1, '2014-05-18 02:24:53', '2014-05-18 03:08:47'),
(100023, 'password', 0, 'Password', 1, 'password', '', 1, 'your password', 0, 0, '', '', 1, '2014-05-18 02:26:49', '2014-05-18 03:08:56'),
(100024, 'checkbox', 0, 'Remember Me', 0, 'remember', 'value:yes,label:first', 1, 'Remember my login details', 0, 0, '', '', 1, '2014-05-18 02:28:30', '2014-05-18 02:51:36'),
(100025, 'anchor', 0, 'Create an account', 0, 'createanaccount', 'url:index.php?option=com_users&view=register', 1, 'Click here to register', 0, 0, '', '', 1, '2014-05-18 02:33:12', '0000-00-00 00:00:00'),
(100026, 'anchor', 0, 'Forgot your username?', 0, 'forgotyourusername', 'url:index.php?option=com_users&view=remind', 0, 'Click here to request a reminder email', 0, 0, '', '', 1, '2014-05-18 02:34:51', '0000-00-00 00:00:00'),
(100027, 'anchor', 0, 'Forgot your password?', 0, 'forgot_your_password', 'url:index.php?option=com_users&view=reset', 0, 'Click here to reset your password', 0, 0, '', '', 1, '2014-05-18 02:35:36', '0000-00-00 00:00:00'),
(100028, 'hidden', 0, 'Login Task', 0, 'task', 'value:user.login', 0, '', 0, 0, '', '', 1, '2014-05-18 03:00:55', '0000-00-00 00:00:00'),
(100029, 'php', 0, 'Hi', 0, 'name', 'object:user', 0, '', 0, 0, '', '', 1, '2014-05-18 03:32:31', '2014-05-18 03:46:59'),
(100030, 'hidden', 0, 'Logout Task', 0, 'task', 'value:user.logout', 0, '', 0, 0, '', '', 1, '2014-05-18 03:34:48', '0000-00-00 00:00:00'),
(100031, 'select', 0, 'Joomla Version', 0, 'joomla_version', 'option:Please select your Joomla version', 0, '', 0, 0, '1.5,1.6,2.0,2.5,2.6,3.0,3.3,3.5', '', 1, '2017-03-10 03:05:45', '0000-00-00 00:00:00'),
(100032, 'information', 0, 'Information Field Demo', 0, 'information_field_demo', '', 0, '', 0, 0, '', 'This is an ''Information Field''. They are used to add HTML content (text, images etc) into your forms. You can add this type of field anywhere within your forms.', 1, '2017-03-16 11:03:08', '0000-00-00 00:00:00'),
(100033, 'php', 0, 'PHP Current Component:', 0, 'option', 'object:request', 0, '', 0, 0, '', '', 1, '2017-03-16 01:34:42', '0000-00-00 00:00:00'),
(100034, 'text', 1, 'Set 1', 0, 'set_1', 'set:first', 0, 'I am part of a set', 0, 0, '', '', 1, '2017-05-11 12:32:12', '0000-00-00 00:00:00'),
(100035, 'text', 1, 'Set 2', 0, 'set_2', 'set:set', 0, 'I am part of a set', 0, 0, '', '', 1, '2017-05-11 12:34:28', '0000-00-00 00:00:00'),
(100036, 'text', 1, 'Set 3', 0, 'set_3', 'set:', 0, 'I am part of a set', 0, 0, '', '', 1, '2017-05-11 12:35:46', '0000-00-00 00:00:00'),
(100037, 'text', 1, 'Set 4', 0, 'set_4', 'set:last,set:repeat', 0, 'I am part of a set', 0, 0, '', '', 1, '2017-05-11 12:36:50', '2017-05-11 02:34:31'),
(100038, 'checkbox', 0, 'Checkboxes With Comments', 0, 'checkboxes_with_comments', 'value1:comment,value3:comment,width:auto', 0, 'Two of these checkboxes required extra data (why did you choose this value?)', 0, 0, 'value1:Box 1,value2:Box 2,value3:Box 3,value4:Box 4', '', 1, '2017-05-11 02:32:12', '0000-00-00 00:00:00'),
(100039, 'radio', 0, 'Radio Buttons With Comments', 0, 'radio_buttons_with_comments', 'value1:comment,value3:comment,width:auto', 0, 'Some of these Buttons required extra data (why have you selected this radio?)', 0, 0, 'value1:Radio 1,value2:Radio 2,value3:Radio 3,value4:Radio 4', '', 1, '2017-05-11 02:33:49', '0000-00-00 00:00:00')",

            'masterforms_fieldsets' => "INSERT INTO `masterforms_fieldsets` (`id`, `fieldset_formid`, `fieldset_legend`, `fieldset_type`, `fieldset_style`, `fieldset_order`, `fieldset_parent`) VALUES
(100000, 100000, 'Contact Us', 'outer', 'margin: 0 0 10px 0;', 1, 0),
(100001, 100001, 'Testimonial Form', 'outer', 'margin: 0 0 10px 0; padding: 0 12em 0 0;', 1, 0),
(100002, 100002, 'Newsletter', 'outer', 'margin: 0 0 10px 0;', 1, 0),
(100003, 100003, 'Login', 'outer', 'margin: 0 0 10px 0;', 1, 0),
(100004, 100004, 'Logout', 'outer', 'margin: 0 0 10px 0;', 1, 0),
(100005, 100005, 'Outer Fieldset', 'outer', 'margin: 0 0 10px 0;', 1, 0),
(100006, 100005, 'First Nested Fieldset', 'inner', 'margin: 0 10px 10px 0; float: left', 1, 6),
(100007, 100005, 'Second Nested Fieldset', 'inner', 'margin: 0 0 10px 0; float: left', 2, 6),
(100008, 100005, 'Full Width Inner Nested Fieldset', 'inner', 'margin: 0 10px 10px 0; float: left; display: inline-block; width: 97%;', 3, 6)",

            'masterforms_form_fields' => "INSERT INTO `masterforms_form_fields` (`id`, `field_form_id`, `field_fieldset_id`, `field_id`, `field_order`) VALUES
(100000, 100000, 100000, 100000, 100000),
(100001, 100000, 100000, 100001, 2),
(100002, 100000, 100000, 100002, 3),
(100003, 100000, 100000, 100003, 4),
(100004, 100000, 100000, 100004, 5),
(100005, 100000, 100000, 100005, 6),
(100006, 100001, 100001, 100007, 1),
(100007, 100001, 100001, 100008, 2),
(100008, 100001, 100001, 100006, 3),
(100009, 100001, 100001, 100009, 5),
(100010, 100001, 100001, 100010, 7),
(100011, 100001, 100001, 100011, 4),
(100012, 100001, 100001, 100012, 6),
(100013, 100002, 100002, 100013, 1),
(100014, 100002, 100002, 100014, 2),
(100015, 100002, 100002, 100021, 3),
(100016, 100002, 100002, 100015, 4),
(100017, 100002, 100002, 100016, 5),
(100018, 100002, 100002, 100017, 6),
(100019, 100002, 100002, 100019, 7),
(100020, 100002, 100002, 100020, 8),
(100021, 100002, 100002, 100018, 9),
(100022, 100003, 100003, 100022, 1),
(100023, 100003, 100003, 100023, 2),
(100024, 100003, 100003, 100024, 3),
(100025, 100003, 100003, 100025, 4),
(100026, 100003, 100003, 100026, 5),
(100027, 100003, 100003, 100027, 6),
(100028, 100003, 100003, 100028, 7),
(100029, 100004, 100004, 100029, 1),
(100030, 100004, 100004, 100030, 2),
(100031, 100005, 100006, 100007, 1),
(100032, 100005, 100006, 100008, 2),
(100033, 100005, 100006, 100012, 3),
(100034, 100005, 100006, 100010, 4),
(100035, 100005, 100007, 100031, 5),
(100036, 100005, 100007, 100011, 6),
(100037, 100005, 100007, 100032, 7),
(100038, 100005, 100007, 100033, 8),
(100039, 100005, 100008, 100034, 1),
(100040, 100005, 100008, 100035, 2),
(100041, 100005, 100008, 100036, 3),
(100042, 100005, 100008, 100037, 4),
(100043, 100005, 100008, 100038, 5),
(100044, 100005, 100008, 100039, 6)",

            'masterforms_forms' => "INSERT INTO `masterforms_forms` (`id`, `form_title`, `form_category_id`, `form_description`, `form_show_description`, `form_instance`, `form_fields`, `form_fieldsets_outer`, `form_fieldsets_inner`, `form_parameters`, `form_status`, `created_at`, `updated_at`) VALUES
(100000, 'Contact Us', 100001, 'Contact form for website - layout suitable for rendering in form module', 0, '', '1,2,3,4,5,6', '1', '', '{\"contactForm\":\"1\",\"contactEmail\":\"gilbert@imbi.com.au\",\"contactDataAccess\":\"2\",\"formTitleOn\":\"0\",\"categoryInTitleOn\":\"0\",\"categoryNameOn\":\"\",\"formTitle\":\"\",\"alignLabelTextRight\":\"\",\"hideLabels\":\"\",\"autoComplete\":\"\",\"submitButtonText\":\"Send Message\",\"submitButtonTitle\":\"\",\"submitButtonTitleType\":\"\",\"formAction\":\"\",\"formid\":\"1\"}', 1, '2014-05-11 12:47:19', '0000-00-00 00:00:00'),
(100001, 'Testimonials', 100002, 'Testimonials form. These testimonials will be publicly visible.', 0, '', '7,8,9,10,11,12,13', '2', '', '{\"contactForm\":\"\",\"contactEmail\":\"\",\"contactDataAccess\":\"0\",\"formTitleOn\":\"\",\"categoryInTitleOn\":\"\",\"categoryNameOn\":\"0\",\"formTitle\":\"\",\"alignLabelTextRight\":\"1\",\"hideLabels\":\"\",\"formAction\":\"\",\"formid\":\"2\"}', 1, '2014-05-11 10:17:22', '2017-03-15 10:43:03'),
(100002, 'Subscription', 100000, 'ACY Newsletter Subscriptions', 0, '', '14,15,16,17,18,19,20,21,22', '3', '', '{\"contactForm\":\"\",\"contactEmail\":\"\",\"contactDataAccess\":\"\",\"formTitleOn\":\"0\",\"categoryInTitleOn\":\"\",\"categoryNameOn\":\"\",\"formTitle\":\"\",\"alignLabelTextRight\":\"\",\"hideLabels\":\"1\",\"submitButtonText\":\"Subscribe\",\"formAction\":\"index.php?option=com_acymailing\",\"formid\":\"3\"}', 1, '2014-05-16 10:03:03', '0000-00-00 00:00:00'),
(100003, 'Login', 100000, 'Login Form replacement - render in module for Public Access only', 0, '', '23,24,25,26,27,28,29', '4', '', '{\"contactForm\":\"\",\"contactEmail\":\"\",\"contactDataAccess\":\"\",\"formTitleOn\":\"0\",\"categoryInTitleOn\":\"\",\"categoryNameOn\":\"\",\"formTitle\":\"\",\"alignLabelTextRight\":\"\",\"hideLabels\":\"\",\"submitButtonText\":\"Login\",\"formAction\":\"index.php?option=com_users\",\"formid\":\"4\"}', 1, '2014-05-18 02:40:20', '0000-00-00 00:00:00'),
(100004, 'Logout', 100000, 'Logout replacement - render using Module - Registered user access only', 0, '', '30,31', '5', '', '{\"contactForm\":\"\",\"contactEmail\":\"\",\"contactDataAccess\":\"\",\"formTitleOn\":\"0\",\"categoryInTitleOn\":\"\",\"categoryNameOn\":\"\",\"formTitle\":\"\",\"alignLabelTextRight\":\"\",\"hideLabels\":\"\",\"submitButtonText\":\"Logout\",\"formAction\":\"index.php?option=com_users\",\"formid\":\"5\"}', 1, '2014-05-18 03:42:15', '0000-00-00 00:00:00'),
(100005, 'Nested Elements Form Demo', 100003, 'This form demonstrates the usage of nested Fieldsets', 1, '', '32,33,34,35,36,37,38,39,40,41,42,43,44,45', '6', '7,8,9', '', 1, '2017-03-16 09:26:42', '2017-03-17 01:38:05')"
        );

        if (isset($sql[ $table ])) {
            return $sql[ $table ];
        }

        return false;

    }
}