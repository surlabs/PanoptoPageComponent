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
use connection\PanoptoLTIHandler;
use ILIAS\DI\Exceptions\Exception;
use platform\PanoptoConfig;
use utils\DTO\ContentObject;
use utils\DTO\Session;
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
    protected mixed $properties;

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
    public function render($parent, $properties = array()): \ILIAS\UI\Component\Legacy\Legacy
    {
        global $DIC;
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->ctrl = $DIC->ctrl();
        $this->parent_gui = $parent;

        $this->lng = $DIC->language();
//        $this->id = 'xpan_embed';
        $this->pl = ilPanoptoPageComponentPlugin::getInstance();
        $this->properties = $properties;
        $this->factory = $DIC->ui()->factory();

        return self::createContentObject();

    }


    /**
     * @throws ilException
     */
    public function createContentObject(): \ILIAS\UI\Component\Legacy\Legacy
    {

        try {
            $url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=true';
            $onclick = "if(typeof(xpan_modal_opened) === 'undefined') { xpan_modal_opened = true; $('#xpan_iframe').attr('src', '" . $url . "');}"; // this avoids a bug in firefox (iframe source must be loaded on opening modal)
            $onclick .= "$('#ilContentContainer .modal-dialog').addClass('modal-lg').css('width', '100%').css('max-width', '800px');";
            $onclick .= "$('#ilContentContainer .modal').modal('show');";

            $field_add_video = $this->factory->legacy("<h1>".$this->pl->txt('video_form_title')."</h1>"."<button id='il_prop_cont_xpan_choose_videos_link' onclick=\"" . $onclick . "\">".$this->pl->txt('choose_videos')."</button>");

            return $field_add_video;


        } catch(Exception $e){
            throw new ilException($e->getMessage());
        }

    }

    protected function initForm(): void
    {
        if (empty($this->properties)) {
            $this->addCommandButton('create', $this->lng->txt('create'));

            $item = new ilCustomInputGUI('', 'xpan_choose_videos_link');
            $url = 'https://' . PanoptoConfig::get('hostname') . '/Panopto/Pages/Sessions/EmbeddedUpload.aspx?playlistsEnabled=true';
            $onclick = "if(typeof(xpan_modal_opened) === 'undefined') { xpan_modal_opened = true; $('#xpan_iframe').attr('src', '" . $url . "');}"; // this avoids a bug in firefox (iframe source must be loaded on opening modal)
            $onclick .= "$('#xpan_modal').modal('show');";
            $item->setHtml("<a onclick=\"" . $onclick . "\">" . $this->pl->txt('choose_videos') . "<a>");
            $this->addItem($item);
        } else {
            $this->addCommandButton('update', $this->lng->txt('update'));

            $item = new ilHiddenInputGUI('id');
            $item->setValue($this->properties['id']);
            $this->addItem($item);

            $item = new ilHiddenInputGUI('is_playlist');
            $item->setValue((string)$this->properties['is_playlist']);
            $this->addItem($item);

            $item = new ilCustomInputGUI('', '');
            $item->setHtml("<iframe src='" . 'https://' . PanoptoConfig::get('hostname') . "/Panopto/Pages/Embed.aspx?"
                . ($this->properties['is_playlist'] ? "p" : "")
                . "id=" . $this->properties['id']
                . "&v=1' width='450' height='256'"
                . "' frameborder='0' allowfullscreen></iframe>");
            $this->addItem($item);

            $item = new ilNumberInputGUI($this->pl->txt('max_width'), 'max_width');
            $item->setRequired(true);
            $item->setValue($this->properties['max_width']);
            $this->addItem($item);
        }

        $this->addCommandButton('cancel', $this->lng->txt('cancel'));
    }

}
