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

use platform\PanoptoConfig;
use platform\PanoptoException;
use ILIAS\UI\Factory;

/**
 * Class UploadVideoGUI
 *
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 */
class UploadVideoGUI {

    /**
     * @var ilLanguage
     */
    protected ilLanguage $lng;

    /**
     * @var ilPanoptoPageComponentPluginGUI
     */
    protected ilPanoptoPageComponentPluginGUI $parent_gui;
    /**
     * @var ilPanoptoPageComponentPlugin
     */
    protected ilPlugin $pl;
    /**
     * @var array
     */
    protected array $properties = [];

    /**
     * @var ilGlobalTemplateInterface
     */
    protected ilGlobalTemplateInterface $tpl;

    /**
     * @var ilCtrl
     */
    protected ilCtrl $ctrl;

    /**
     * @var Factory
     */
    protected Factory $factory;

    /**
     * @throws ilException
     */
    public function render($parent, $properties = array(), $onManage = false): string
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent;

        $this->lng = $DIC->language();
        $this->pl = ilPanoptoPageComponentPlugin::getInstance();
        $this->properties = $properties;
        $this->factory = $DIC->ui()->factory();

        return self::createContentObject($onManage);

    }


    /**
     * @throws ilException
     */
    public function createContentObject($onManage): string
    {
        global $DIC;
        $renderer = $DIC->ui()->renderer();
        $this->tpl->addOnLoadCode('setLenguage("'.$this->pl->txt('video').'", "'.$this->pl->txt('max_video_width').'");');
        $this->tpl->addCss($this->pl->getDirectory() . '/templates/default/manage.css');

        try {
            if(!$this->properties){
                $url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=true';
                $onclick = "if(typeof(xpan_modal_opened) === 'undefined') { xpan_modal_opened = true; $('#xpan_iframe').attr('src', '" . $url . "');}"; // this avoids a bug in firefox (iframe source must be loaded on opening modal)
                $onclick .= "$('#ilContentContainer .modal-dialog').addClass('modal-lg').css('width', '100%').css('max-width', '800px');";
                $onclick .= "$('#ilContentContainer .modal').modal('show');";

                $field_add_video = $this->factory->legacy("<h1>".$this->pl->txt('video_form_title')."</h1>"."<button class='ppco_add_button' id='il_prop_cont_xpan_choose_videos_link' onclick=\"" . $onclick . "\">".$this->pl->txt('choose_videos')."</button>");
                $inputHidden = $this->factory->input()->field()->hidden()->withLabel($this->pl->txt('hidden'));
                $form_action = $DIC->ctrl()->getFormActionByClass('ilPanoptoPageComponentPluginGUI', "create");
                $form = $this->factory->input()->container()->form()->standard($form_action, [$inputHidden]);
                return $renderer->render($field_add_video) . $renderer->render($form);


            } else {
                $inputHidden = $this->factory->input()->field()->hidden()->withLabel($this->pl->txt('hidden'));
                $form_action = $DIC->ctrl()->getFormActionByClass('ilPanoptoPageComponentPluginGUI', 'update');

                $this->tpl->addOnLoadCode("addIframe('".$this->properties['id']."', '".PanoptoConfig::get('hostname')."', ".$this->properties['is_playlist'].", ".$this->properties['max_width'].");");

                $form = $this->factory->input()->container()->form()->standard($form_action, [$inputHidden]);
                return $renderer->render($form);

            }

        } catch(PanoptoException $e){
            $messageBox = $this->factory->messageBox()->failure($e->getMessage());
            return $renderer->render($messageBox);
        }

    }


}
