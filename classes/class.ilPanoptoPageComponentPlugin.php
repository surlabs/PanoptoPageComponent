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

/**
 * Class UserContentMainUI
 * @authors Jesús Copado, Daniel Cazalla, Saúl Díaz, Juan Aguilar <info@surlabs.es>
 * @ilCtrl_isCalledBy ilPanoptoPageComponentPluginGUI
 */
class ilPanoptoPageComponentPlugin extends ilPageComponentPlugin {

    function isValidParentType($a_type): bool
    {
        return true;
    }

    function getPluginName(): string
    {
        return "PanoptoPageComponent";
    }

    public static function getInstance() : ilPlugin
    {
        GLOBAL $DIC;
        /** @var ilComponentFactory $component_factory */
        $component_factory = $DIC["component.factory"];
        return $component_factory->getPlugin('ppco');
    }

}
