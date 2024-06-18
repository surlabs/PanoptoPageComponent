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

use connection\PanoptoClient;
use connection\PanoptoLog;
use connection\PanoptoLTIHandler;
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

        $DIC->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . '/templates/js/launcher.js');
        $launch_url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/BasicLTI/BasicLTILanding.aspx';
        $DIC->ui()->mainTemplate()->addOnLoadCode('panoptoLauncher.addForm('.PanoptoLTIHandler::launchToolPageComponent().', "'.$launch_url.'", "'.PanoptoConfig::get('hostname').'")' );
        
        $renderer = $DIC->ui()->renderer();
        $this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/ppco.js');

        $form = $this->uploadVideoGUI->render($this);
        $this->tpl->addJavaScript("./Services/UIComponent/Modal/js/Modal.js");

        $this->tpl->setContent($renderer->render($messageBox) . $this->getModal() . $form);
    }

    /**
     * @throws ilCtrlException
     * @throws PanoptoException|ilException
     * @throws Exception
     */
    public function edit(): void
    {
        $this->client->synchronizeCreatorPermissions();

        $this->tpl->addJavaScript($this->pl->getDirectory() . '/templates/js/ppco.js');

        $form = $this->uploadVideoGUI->render($this, $this->getProperties());

        $this->tpl->setContent($this->getModal() . $form);
    }


    /**
     * @throws ilCtrlException
     */
    public function create(): void
    {

        if (empty($_POST['session_id']) || empty($_POST['max_width']) || empty($_POST['is_playlist'])) {

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
        $this->returnToParent();
    }

    /**
     * @throws PanoptoException|ilLogException
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
            PanoptoLog::getInstance()->logError($e->getCode(), 'Could not grant viewer access: ' . $e->getMessage());
        }


        $randomId = uniqid();

        $return = "<div class='ppco_iframe_container' id='ppco_iframe_container_".$randomId."' style='width:" . $a_properties['max_width'] . "%; height: 'max-content';></div>";
        $size_props = "";
        if (!isset($a_properties['max_width'])) { // legacy
            $size_props = "width:" . $a_properties['width'] . "px; height:" . $a_properties['height'] . "px;";
            $return = "<div class='ppco_iframe_container' style='" . $size_props . "'></div>";
        } else {
            $size_props = "width:" . $a_properties['max_width'] . "%;";
        }

        $DIC->ui()->mainTemplate()->addJavaScript($this->pl->getDirectory() . '/templates/js/launcher.js');
        $launch_url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/BasicLTI/BasicLTILanding.aspx';
        $DIC->ui()->mainTemplate()->addOnLoadCode('panoptoLauncher.addForm('.PanoptoLTIHandler::launchToolPageComponent().', "'.$launch_url.'", "'.PanoptoConfig::get('hostname').'")' );

        if($this->ctrl->getCmd() == ''){
            $DIC->ui()->mainTemplate()->addOnLoadCode('panoptoLauncher.addVideo("'.$a_properties['id'].'", "'.PanoptoConfig::get('hostname').'", '.$a_properties['is_playlist'].', "'.$randomId.'")' );
        } else {
            $return = "<div class='ppco_iframe_container' style='" . $size_props . "'>
                <iframe src='https://" . PanoptoConfig::get('hostname') . "/Panopto/Pages/Embed.aspx?" . ($a_properties['is_playlist'] ? "p" : "") . "id=".$a_properties['id']."&v=1' style='width:100%; aspect-ratio: 16/9'></iframe>
           </div>";

        }


        return $return;
    }

    public function cancel(): void
    {
        $this->returnToParent();

    }

    function update(): bool
    {
        $this->updateElement(array(
            'id' => $_POST['session_id'][0],
            'max_width' => $_POST['max_width'][0],
            'is_playlist' => $_POST['is_playlist'][0],
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
