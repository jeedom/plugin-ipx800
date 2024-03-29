<?php

/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class ipx800_bouton extends eqLogic {
	/*     * *************************Attributs****************************** */

	/*     * ***********************Methode static*************************** */
	public function postInsert() {
		$state = $this->getCmd(null, 'state');
		if (!is_object($state)) {
			$state = new ipx800_boutonCmd();
			$state->setName('Etat');
			$state->setEqLogic_id($this->getId());
			$state->setType('info');
			$state->setSubType('binary');
			$state->setLogicalId('state');
			$state->setDisplay('generic_type', 'LIGHT_STATE');
			$state->setTemplate('dashboard', 'light');
			$state->setTemplate('mobile', 'light');
			$state->save();
		}
		$btn_on = $this->getCmd(null, 'btn_on');
		if (!is_object($btn_on)) {
			$btn_on = new ipx800_boutonCmd();
			$btn_on->setName('On');
			$btn_on->setEqLogic_id($this->getId());
			$btn_on->setType('action');
			$btn_on->setSubType('other');
			$btn_on->setLogicalId('btn_on');
			$btn_on->setIsVisible(0);
			$btn_on->setDisplay('generic_type', 'LIGHT_ON');
			$btn_on->save();
		}
		$btn_off = $this->getCmd(null, 'btn_off');
		if (!is_object($btn_off)) {
			$btn_off = new ipx800_boutonCmd();
			$btn_off->setName('Off');
			$btn_off->setEqLogic_id($this->getId());
			$btn_off->setType('action');
			$btn_off->setSubType('other');
			$btn_off->setLogicalId('btn_off');
			$btn_off->setIsVisible(0);
			$btn_off->setDisplay('generic_type', 'LIGHT_OFF');
			$btn_off->save();
		}
	}

	public function preUpdate() {
		$nbimpulsion = $this->getCmd(null, 'nbimpulsion');
		if (is_object($nbimpulsion)) {
			$nbimpulsion->remove();
		}
		$state = $this->getCmd(null, 'etat');
		if (is_object($state)) {
			$state->setLogicalId('state');
			$state->save();
		}
		$state = $this->getCmd(null, 'state');
		if ($state->getDisplay('generic_type') == "") {
			$state->setDisplay('generic_type', 'LIGHT_STATE');
			$state->save();
		}
		if ($state->getTemplate('dashboard') == "") {
			$state->setTemplate('dashboard', 'light');
			$state->save();
		}
		if ($state->getTemplate('mobile') == "") {
			$state->setTemplate('mobile', 'light');
			$state->save();
		}
		$btn_on = $this->getCmd(null, 'btn_on');
		if (!is_object($btn_on)) {
			$btn_on = new ipx800_boutonCmd();
			$btn_on->setName('On');
			$btn_on->setEqLogic_id($this->getId());
			$btn_on->setType('action');
			$btn_on->setSubType('other');
			$btn_on->setLogicalId('btn_on');
			$btn_on->setIsVisible(0);
			$btn_on->setDisplay('generic_type', 'LIGHT_ON');
			$btn_on->save();
		} else {
			if ($btn_on->getDisplay('generic_type') == "") {
				$btn_on->setDisplay('generic_type', 'LIGHT_ON');
				$btn_on->save();
			}
		}
		$btn_off = $this->getCmd(null, 'btn_off');
		if (!is_object($btn_off)) {
			$btn_off = new ipx800_boutonCmd();
			$btn_off->setName('Off');
			$btn_off->setEqLogic_id($this->getId());
			$btn_off->setType('action');
			$btn_off->setSubType('other');
			$btn_off->setLogicalId('btn_off');
			$btn_off->setIsVisible(0);
			$btn_off->save();
		} else {
			if ($btn_off->getDisplay('generic_type') == "") {
				$btn_off->setDisplay('generic_type', 'LIGHT_OFF');
				$btn_off->save();
			}
		}
	}

	public function preInsert() {
		$gceid = substr($this->getLogicalId(), strpos($this->getLogicalId(), "_") + 2);
		$this->setEqType_name('ipx800_bouton');
		$this->setIsEnable(0);
		$this->setIsVisible(0);
	}

	public static function eventroute($id, $state) {
		$cmd = ipx800_boutonCmd::byId($id);
		if (!is_object($cmd)) {
			throw new Exception('Commande ID virtuel inconnu : ' . $id);
		}
		log::add('ipx800', 'debug', "Receive push notification for " . $cmd->getName() . " (" . $id . ") : value = " . $state);
		if ($cmd->execCmd() != $cmd->formatValue($state)) {
			$cmd->setCollectDate('');
			$cmd->event($state);
		}
	}

	public function configPush($url_serveur, $pathjeedom, $ipjeedom, $portjeeom) {
		$cmd = $this->getCmd(null, 'state');
		$gceid = substr($this->getLogicalId(), strpos($this->getLogicalId(), "_") + 2);
		$url_serveur .= 'protect/settings/push1.htm?channel=' . $gceid;
		$url = $url_serveur . '&server=' . $ipjeedom . '&port=' . $portjeeom . '&pass=&enph=1';
		log::add('ipx800', 'debug', "get " . preg_replace("/:[^:]*@/", ":XXXX@", $url));
		$result = @file_get_contents($url);
		if ($result === false)
			throw new Exception(__('L\'ipx ne repond pas.', __FILE__));
		$url = $url_serveur . '&cmd1=' . urlencode($pathjeedom . 'core/api/jeeApi.php?api=' . jeedom::getApiKey('ipx800') . '&plugin=ipx800&type=ipx800&who=ipx800_bouton&id=' . $cmd->getId() . '&state=1');
		log::add('ipx800', 'debug', "get " . preg_replace("/:[^:]*@/", ":XXXX@", $url));
		$result = @file_get_contents($url);
		if ($result === false)
			throw new Exception(__('L\'ipx ne repond pas.', __FILE__));
		$url = $url_serveur . '&cmd2=' . urlencode($pathjeedom . 'core/api/jeeApi.php?api=' . jeedom::getApiKey('ipx800') . '&plugin=ipx800&type=ipx800&who=ipx800_bouton&id=' . $cmd->getId() . '&state=0');
		log::add('ipx800', 'debug', "get " . preg_replace("/:[^:]*@/", ":XXXX@", $url));
		$result = @file_get_contents($url);
		if ($result === false)
			throw new Exception(__('L\'ipx ne repond pas.', __FILE__));
	}

	public function getLinkToConfiguration() {
		return 'index.php?v=d&p=ipx800&m=ipx800&id=' . $this->getId();
	}
	/*     * **********************Getteur Setteur*************************** */
}

class ipx800_boutonCmd extends cmd {
	/*     * *************************Attributs****************************** */


	/*     * ***********************Methode static*************************** */


	/*     * *********************Methode d'instance************************* */
	public function execute($_options = null) {
		log::add('ipx800', 'debug', 'execute ' . $_options);
		$eqLogic = $this->getEqLogic();
		if (!is_object($eqLogic) || $eqLogic->getIsEnable() != 1) {
			throw new Exception(__('Equipement desactivé impossible d\éxecuter la commande : ' . $this->getHumanName(), __FILE__));
		}
		$IPXeqLogic = eqLogic::byId(substr($eqLogic->getLogicalId(), 0, strpos($eqLogic->getLogicalId(), "_")));
		$gceid = substr($eqLogic->getLogicalId(), strpos($eqLogic->getLogicalId(), "_") + 2);
		$url = $IPXeqLogic->getUrl();
		if ($this->getLogicalId() == 'btn_on')
			$url .= 'leds.cgi?set=' . $gceid;
		else if ($this->getLogicalId() == 'btn_off')
			$url .= 'leds.cgi?clear=' . $gceid;
		else
			return false;

		$result = @file_get_contents($url);
		log::add('ipx800', 'debug', "get " . preg_replace("/:[^:]*@/", ":XXXX@", $url));
		$count = 0;
		while ($result === false && $count < 3) {
			$result = @file_get_contents($url);
			$count++;
		}
		if ($result === false) {
			throw new Exception(__('L\'ipx ne repond pas.', __FILE__) . " " . $IPXeqLogic->getName());
		}
		return false;
	}

	public function formatValue($_value, $_quote = false) {
		if (trim($_value) == '') {
			return '';
		}
		if ($this->getType() == 'info') {
			switch ($this->getSubType()) {
				case 'binary':
					$_value = strtolower($_value);
					if ($_value == 'dn') {
						$_value = 1;
					}
					if ($_value == 'up') {
						$_value = 0;
					}
					if ((is_numeric(intval($_value)) && intval($_value) > 1) || $_value || $_value == 1) {
						$_value = 1;
					}
					return $_value;
			}
		}
		return $_value;
	}
	/*     * **********************Getteur Setteur*************************** */
	public function imperihomeGenerate($ISSStructure) {
		$eqLogic = $this->getEqLogic(); // Récupération de l'équipement de la commande
		$object = $eqLogic->getObject(); // Récupération de l'objet de l'équipement

		// Construction de la structure de base
		$info_device = array(
			'id' => $this->getId(), // ID de la commande, ne pas mettre autre chose!
			'name' => $eqLogic->getName() . " - " . $this->getName(), // Nom de l'équipement que sera affiché par Imperihome: mettre quelque chose de parlant...
			'room' => (is_object($object)) ? $object->getId() : 99999, // Numéro de la pièce: ne pas mettre autre chose que ce code
			'type' => '',
			'params' => array(), // Le tableau des paramètres liés à ce type (qui sera complété aprés.
		);

		if ($this->getLogicalId() == 'state') { // Sauf si on est entrain de traiter la commande "Mode", à ce moment là on indique un autre type
			$btn_on = $eqLogic->getCmd(null, 'btn_on');
			if ($btn_on->getIsVisible()) {
				$info_device['type'] = 'DevSwitch'; // Le type Imperihome qui correspond le mieux à la commande
			} else {
				$info_device['type'] = 'DevDoor'; // Le type Imperihome qui correspond le mieux à la commande
			}
		} else {
			return $info_device;
		}

		#$info_device['params'] = $ISSStructure[$info_device['type']]['params']; // Ici on vient copier la structure type: laisser ce code

		if ($btn_on->getIsVisible()) {
			array_push($info_device['params'], array("value" =>  '#' . $eqLogic->getCmd(null, 'state')->getId() . '#', "key" => "status", "type" => "infoBinary", "Description" => "Current status : 1 = On / 0 = Off"));
			$info_device['actions']["setStatus"]["item"]["0"] = $eqLogic->getCmd(null, 'btn_off')->getId();
			$info_device['actions']["setStatus"]["item"]["1"] = $eqLogic->getCmd(null, 'btn_on')->getId();
		} else {
			array_push($info_device['params'], array("value" =>  '#' . $eqLogic->getCmd(null, 'state')->getId() . '#', "key" => "tripped", "type" => "infoBinary", "Description" => "Is the sensor tripped ? (0 = No / 1 = Tripped)"));
			array_push($info_device['params'], array("value" =>  '0', "key" => "armable", "type" => "infoBinary", "Description" => "Ability to arm the device : 1 = Yes / 0 = No"));
			array_push($info_device['params'], array("value" =>  '0', "key" => "ackable", "type" => "infoBinary", "Description" => "Ability to acknowledge alerts : 1 = Yes / 0 = No"));
		}
		// Ici on traite les autres commandes (hors "Mode")
		return $info_device;
	}
}
