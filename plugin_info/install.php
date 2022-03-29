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

require_once dirname(__FILE__) . '/../../../core/php/core.inc.php';

function ipx800_install() {
    $cron = cron::byClassAndFunction('ipx800', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('ipx800');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
	config::remove('listChildren', 'ipx800');
	config::save('subClass', 'ipx800_bouton;ipx800_analogique;ipx800_relai;ipx800_compteur', 'ipx800');
	if (config::byKey('api::ipx800::mode') == '') {
		config::save('api::ipx800::mode', 'enable');
	}
	if(strlen(jeedom::getApiKey('ipx800')) > 32){
		config::save('api', config::genKey(32), 'ipx800');
	}
}

function ipx800_update() {
	config::remove('listChildren', 'ipx800');
	config::save('subClass', 'ipx800_bouton;ipx800_analogique;ipx800_relai;ipx800_compteur', 'ipx800');
    $cron = cron::byClassAndFunction('ipx800', 'pull');
	if ( ! is_object($cron)) {
        $cron = new cron();
        $cron->setClass('ipx800');
        $cron->setFunction('pull');
        $cron->setEnable(1);
        $cron->setDeamon(0);
        $cron->setSchedule('* * * * *');
        $cron->save();
	}
    $cron = cron::byClassAndFunction('ipx800', 'cron');
	if (is_object($cron)) {
		$cron->stop();
		$cron->remove();
	}
	foreach (eqLogic::byType('ipx800_bouton') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('ipx800_analogique') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('ipx800_relai') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('ipx800_compteur') as $SubeqLogic) {
		$SubeqLogic->save();
	}
	foreach (eqLogic::byType('ipx800') as $eqLogic) {
		$eqLogic->save();
	}
	if ( config::byKey('api', 'ipx800', '') == "" )
	{
		log::add('ipx800', 'alert', __('Une clef API "ipx800" a été configurée. Pensez à reconfigurer le push de chaque carte IPX800', __FILE__));
	}
	if (config::byKey('api::ipx800::mode') == '') {
		config::save('api::ipx800::mode', 'enable');
	}
	if(strlen(jeedom::getApiKey('ipx800')) > 32){
		config::save('api', config::genKey(32), 'ipx800');
	}
}

function ipx800_remove() {
    $cron = cron::byClassAndFunction('ipx800', 'pull');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
    $cron = cron::byClassAndFunction('ipx800', 'cron');
    if (is_object($cron)) {
		$cron->stop();
        $cron->remove();
    }
	config::remove('listChildren', 'ipx800');
	config::remove('subClass', 'ipx800');
}
?>
