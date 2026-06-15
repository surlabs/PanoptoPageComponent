![Panopto](https://github.com/user-attachments/assets/265a22ed-5a5d-409b-9d15-2d965fd372c5)

# Panopto Page Component Plugin for ILIAS 7
This plugin allows users to embed Panopto videos in ILIAS as Page Component objects

## Installation & Update

### Software Requirements
This Page Component plugin requires the Panopto Repository Object plugin (https://github.com/surlabs/Panopto) properly installed and configured in your ILIAS platform to be used

### Installation steps
1. Create subdirectories, if necessary for Customizing/global/plugins/Services/COPage/PageComponent/ or run the following script fron the ILIAS root
   
```bash
mkdir -p Customizing/global/plugins/Services/COPage/PageComponent
cd Customizing/global/plugins/Services/COPage/PageComponent
```

3. In Customizing/global/plugins/Services/COPage/PageComponent/ **ensure you delete any previous PanoptoPageComponent folder**
4. Then, execute:

```bash
git clone https://github.com/surlabs/PanoptoPageComponent.git
git checkout release_7
```

Ensure you run composer install at platform root before you install/update the plugin
```bash
composer install --no-dev
```

Run ILIAS update script at platform root
```bash
php setup/setup.php update
```

**Ensure you don't ignore plugins at the ilias .gitignore files and don't use --no-plugins option at ILIAS setup**

# Authors
* Initially created by studer + raimann ag, switzerland
* Further maintained by fluxlabs ag, switzerland
* Revamped and currently maintained by SURLABS, spain [SURLABS](https://surlabs.com)

# Bug Reports & Discussion
- Bug Reports: [Mantis](https://www.ilias.de/mantis) (Choose project "ILIAS plugins" and filter by category "Panopto")
- SIG Panopto [Forum](https://docu.ilias.de/goto_docu_frm_13755.html)

# Version History
* The version 11.x.x for **ILIAS 11** developed and maintained by SURLABS can be found in the Github branch **dev_11**
* The version 10.x.x for **ILIAS 10** developed and maintained by SURLABS can be found in the Github branch **release_10**
* The version 9.x.x for **ILIAS 9** developed and maintained by SURLABS can be found in the Github branch **release_9**
* The version 8.x.x for **ILIAS 8** developed and maintained by SURLABS can be found in the Github branch **release_8**
* The version 7.x.x for **ILIAS 7** developed and maintained by SURLABS can be found in the Github branch **release_7**
* The previous plugin versions for ILIAS <8 is archived. It can be found in https://github.com/fluxapps/Panopto
