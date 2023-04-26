<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Strings for component 'auth_selfcheckin', language 'en'.
 *
 * @package   auth_selfcheckin
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['auth_selfcheckindescription'] = '<p>QRCode-based self-registration enables a user to create their own account via a QRCode link shown by the teacher. The user then receives an email with account confirmation. As the procedure is secured from the beginning, there is no need of accountconfirmation by email, just email notification.</p>';
$string['auth_selfcheckinnoemail'] = 'Nous avons essayé de vous envoyer un courriel, mais nous n\'avons pas pu !';
$string['auth_selfcheckinsettings'] = 'Réglages';
$string['pluginname'] = 'Auto-incription via QRCode';
$string['privacy:metadata'] = 'L\'auto-inscription par QRCode-ne store par lui-même aucune donnée personnelle.';
$string['emailconfirmsent'] = '
<p>Votre procédure d\'enregistrement est achevée.</p>
<p>Vous allez recevoir un courriel de confirmation à l\'addresse : {$a}</p>
<p>Vous y trouverez une confirmation de vos identifiants d\'accès, ainsi qu\'un lien pour vous connecter, valable 24 heures.</p>
';
$string['selfcheckinemailsubject'] = 'Votre inscription sur {$a->sitename}';
$string['selfcheckinemailtext'] = '
Bienvenue {$a->firstname} {$a->lastname}

Votre inscription sur {$a->sitename} s\'est bien passée !.

Votre compte est enregistré et ne demande pas de confirmation supplémentaire.

vous pouvez accéder à vos cours à l\'adresse : {$a->wwwroot} avec les coordonnées qui suivent : 
Votre identifiant : {$a->username}
Votre mot de passe (rappel) : {$a->password}
Ne communiquez ce mot de passe à personne! Vous pouvez le changer à tout moment.

Profitez de vos contenus de formation !

Accédez maintenant à votre cours en suivant le lien : {$a->courseaccessurl} (24 heures)
';
$string['selfcheckinemailhtml'] = '
<h2>Bienvenue {$a->firstname} {$a->lastname}</h2>

<p>Votre inscription sur {$a->sitename} s\'est bien passée !.</p>

<p>Votre compte est enregistré et ne demande pas de confirmation supplémentaire.</p>

<p>vous pouvez accéder à vos cours à l\'adresse : {$a->wwwroot} avec les coordonnées qui suivent : <br/>
Votre identifiant : {$a->username}<br/>
Votre mot de passe (rappel) : {$a->password}<br/>
Ne communiquez ce mot de passe à personne ! Vous pouvez le changer à tout moment.</p>

<p>Profitez de vos contenus de formation !</p>

<p><a href="{$a->courseaccessurl}">Accédez maintenant à votre cours en suivant ce lien</a> (24 heures)
';

