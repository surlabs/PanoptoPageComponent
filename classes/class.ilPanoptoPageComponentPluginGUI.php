<?php
declare(strict_types=1);

/**
 * This file is part of the Panopto Repository Object plugin for ILIAS.
 * This plugin allows users to embed Panopto videos in ILIAS as repository objects.
 *
 * The Panopto Repository Object plugin for ILIAS is open-source and licensed under GPL-3.0.
 * For license details, visit https://www.gnu.org/licenses/gpl-3.0.en.html.
 *
 * To report bugs or participate in discussions, visit the Mantis system and filter by
 * the category "Panopto" at https://mantis.ilias.de.
 *
 * More information and source code are available at:
 * https://github.com/surlabs/Panopto
 *
 * If you need support, please contact the maintainer of this software at:
 * info@surlabs.es
 *
 */
//require_once __DIR__ . '/../vendor/autoload.php';

use classes\ui\user\UserContentMainUI;
use connection\PanoptoClient;
use connection\PanoptoLTIHandler;
use League\OAuth1\Client as OAuth1;
use platform\PanoptoConfig;
use platform\PanoptoException;


/**
 * Class UserContentMainUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilPanoptoPageComponentPluginGUI: ilPCPluggedGUI
 * @ilCtrl_Calls      ilPanoptoPageComponentPluginGUI: ilObjRootFolderGUI
 */
class ilPanoptoPageComponentPluginGUI extends ilPageComponentPluginGUI {


    /**
     * @var ilCtrl
     */
    protected ilCtrl $ctrl;
    /**
     * @var ilGlobalTemplateInterface
     */
    protected ilGlobalTemplateInterface $tpl;
    /**
     * @var ilPanoptoPageComponentPlugin
     */
    protected ilPlugin $pl;
    /**
     * @var PanoptoClient
     */
    protected PanoptoClient $client;

    /**
     * @var UploadVideoGUI
     */
    protected UploadVideoGUI $uploadVideoGUI;


    /**
     * ilPanoptoPageComponentPluginGUI constructor.
     * @throws ilCtrlException
     */
    public function __construct() {
        parent::__construct();
        global $DIC;
        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->pl = ilPanoptoPageComponentPlugin::getInstance();
        $this->client = PanoptoClient::getInstance();
        $this->uploadVideoGUI = new UploadVideoGUI();

    }

    public function executeCommand(): void
    {
        try {
            $cmd = $this->ctrl->getCmd();
            $this->$cmd();

        } catch (ilCtrlException $e) {
            new PanoptoException($e->getMessage(), $e->getCode(), $e);
            //TODO: Revisar si esto funciona
        }
    }

    /**
     * @throws PanoptoException
     * @throws ilCtrlException
     * @throws Exception
     */
    public function insert(): void
    {
        global $DIC;
        $this->client->synchronizeCreatorPermissions();
        $f = $DIC->ui()->factory();
        $messageBox = $f->messageBox()->success($this->pl->txt("msg_choose_videos"));
        $renderer = $DIC->ui()->renderer();
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/ppco.js');
        $form = $this->uploadVideoGUI->render($this);
        $this->tpl->addJavaScript("./Services/UIComponent/Modal/js/Modal.js");

        $this->tpl->setContent($renderer->render($messageBox) . $this->getModal() . $renderer->render($form));
    }

    /**
     * @throws ilCtrlException
     */
    public function edit(): void
    {
//        $form = new ppcoVideoFormGUI($this, $this->getProperties());
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * @throws ilCtrlException
     */
    public function create(): void
    {
        if (empty($_POST['session_id']) || empty($_POST['max_width']) || empty($_POST['is_playlist'])) {
            global $DIC;
            $factory = $DIC->ui()->factory();
            $renderer = $DIC->ui()->renderer();
            $factory->messageBox()->failure($this->pl->txt('msg_no_video'));
            //TODO: Revisar si este messageBox funciona
            $this->ctrl->redirect($this, 'insert');
        }

        $session_ids = array_reverse($_POST['session_id']);
        $max_widths = array_reverse($_POST['max_width']);
        $is_playlists = array_reverse($_POST['is_playlist']);

        for ($i = 0; $i < count($session_ids); $i++) {
            $this->createElement(array(
                'id' => $session_ids[$i],
                'max_width' => $max_widths[$i],
                'is_playlist' => $is_playlists[$i]
            ));
        }
        //TODO: Revisar si este redirect está funcionando.
        $this->returnToParent();
    }

    /**
     * @throws PanoptoException
     */
    public function getElementHTML(string $a_mode, array $a_properties, string $plugin_version): string
    {
        global $DIC;

        try {
            if ($a_properties['is_playlist']) {
                $this->client->grantViewerAccessToPlaylistFolder($a_properties['id']);
            } else {
                $this->client->grantViewerAccessToSession($a_properties['id']);
            }
        } catch (Exception $e) {
            // exception could mean that the session was deleted. The embed player will display an appropriate message
//            xpanLog::getInstance()->logError($e->getCode(), 'Could not grant viewer access: ' . $e->getMessage());
        }

        $return = "<div class='ppco_iframe_container_".$a_properties['id']."' style='width:" . $a_properties['max_width'] . "%; height: 'max-content';></div>";

        if (!isset($a_properties['max_width'])) { // legacy
            $size_props = "width:" . $a_properties['width'] . "px; height:" . $a_properties['height'] . "px;";
            $return = "<div class='ppco_iframe_container' style='" . $size_props . "'></div>";
        }

        $DIC->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . '/templates/js/launcher.js');
        $launch_url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/BasicLTI/BasicLTILanding.aspx';
        $DIC->ui()->mainTemplate()->addOnLoadCode('addForm('.PanoptoLTIHandler::launchToolPageComponent().', "'.$launch_url.'", "'.PanoptoConfig::get('hostname').'")' );
        $DIC->ui()->mainTemplate()->addOnLoadCode('addVideo("'.$a_properties['id'].'", "'.PanoptoConfig::get('hostname').'", '.$a_properties['is_playlist'].')' );


        return $return;
    }

    public function cancel(): void
    {
        $this->returnToParent();
        //TODO: Revisar si este redirect está funcionando.

    }

    function update(): bool
    {
        $form = new ppcoVideoFormGUI($this, $this->getProperties());
        $form->setValuesByPost();

        if (!$form->checkInput()) {
            $this->tpl->setContent($form->getHTML());
            // return;
        }

        $this->updateElement(array(
            'id' => $_POST['id'],
            'max_width' => $_POST['max_width'],
            'is_playlist' => $_POST['is_playlist'],
        ));

        $this->returnToParent();
        return true;
    }

    /**
     * @return String
     * @throws PanoptoException
     */
    public function getModal(): string
    {
        global $DIC;
        $factory = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();
        $url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=true';
        $message = $factory->legacy('<iframe id="xpan_iframe" style="background-size: contain;width: 100%;height: 500px;border: unset;" src="'.$url.'"></iframe>');
        $modal = $factory->modal()->roundtrip('', $message)->withActionButtons([$factory->button()->primary($this->pl->txt('choose_videos'), "#")->withAriaLabel('insert')]);
        $this->tpl->addOnLoadCode('$("#lti_form").submit();');

        return $renderer->render($modal);
    }
}
