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
 * Language strings for the external server submission plugin.
 *
 * @package    assignsubmission_externalserver
 * @author     Stefan Weber <stefan.weber@think-modular.com>
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addserver'] = 'Externen Server hinzufügen';
$string['all'] = 'alle Teilnehmer/innen';
$string['cannotdelete'] = 'Dieser Server kann nicht gelöscht werden, er wird in {$a} Aufgabe(n) verwendet.';
$string['checkconnection'] = 'Verbindung prüfen';
$string['confirmdeleting'] = 'Sind Sie sicher, dass Sie den externen Server "{$a}" löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.';
$string['confirmgrading'] = 'Bewertungen und Feedback für <strong>{$a->for}</strong> vom externen Server <strong>{$a->server}</strong> laden. <p><strong>Bestehende Bewertungen und Feedback werden überschrieben!</strong></p>';
$string['connectionstatus'] = 'Verbindung zum externen Server';
$string['couldnotgetgrades'] = 'Bewertungen konnten nicht vom externen Server abgerufen werden. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['defaultsettings'] = 'Standardeinstellungen';
$string['defaultsettings_help'] = 'Standardwerte, die in jeder Instanz geändert werden können.';
$string['delete:disabled'] = 'Server wird verwendet und kann nicht gelöscht werden';
$string['delete:error'] = 'Fehler beim Löschen des Servers';
$string['delete:success'] = 'Server erfolgreich gelöscht';
$string['deleteexternalserver'] = 'Externen Server "{$a}" löschen';
$string['downloadgradesfor'] = 'Bewertungen laden für';
$string['editserver'] = 'Externen Server "{$a}" bearbeiten';
$string['enabled'] = 'Aktiviert';
$string['enabled_help'] = 'Teilnehmer/innen können eine Abgabe an einen vorkonfigurierten externen Server senden, der eine Antwort zurücksendet.';
$string['error:couldnotgetjwttoken'] = 'JWT-Token konnte nicht vom externen Server abgerufen werden. HTTP-Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['error:couldnotgetoauth2token'] = 'OAuth2-Token konnte nicht vom externen Server abgerufen werden. HTTP-Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['error:requestfailed'] = 'Anfrage an externen Server fehlgeschlagen. HTTP-Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['expandresponse'] = 'Antwort vom externen Server anzeigen';
$string['externalserver'] = 'Server';
$string['externalserver_help'] = 'Wählen Sie den externen Server für diese Aufgabe aus. Externe Server können in der Systemadministration konfiguriert werden.';
$string['externalservertitle'] = 'Externer Server "{$a}"';
$string['file_uploaded'] = 'Datei erfolgreich auf externen Server hochgeladen.';
$string['filetypes'] = 'Erlaubte Dateitypen';
$string['filetypes_help'] = 'Akzeptierte Dateitypen können durch Eingabe einer Liste von Dateierweiterungen eingeschränkt werden. Wenn das Feld leer gelassen wird, sind alle Dateitypen erlaubt.';
$string['getgrades'] = 'Bewertungen und Feedback vom externen Server abrufen';
$string['gradesupdated'] = 'Bewertungen erfolgreich aktualisiert. {$a} Bewertung(en) aktualisiert.';
$string['gradeverb'] = 'Bewertung vom externen Server abrufen';
$string['loadgrades'] = 'Bewertungen laden';
$string['maxbytes'] = 'Maximale Größe';
$string['maxbytes_help'] = 'Maximale Aufgabengröße für alle Aufgaben auf der Website (vorbehaltlich Kurslimits und anderer lokaler Einstellungen)';
$string['needselectfile'] = 'Bitte geben Sie eine gültige Datei zum Hochladen an.';
$string['noneselected'] = 'Kein externer Server ausgewählt. Bitte wählen Sie in den Einstellungen einen externen Server für diese Aufgabe aus.';
$string['noneselectedstudent'] = 'Kein externer Server ausgewählt. Bitte kontaktieren Sie den/die Trainer/in, um einen externen Server für diese Aufgabe zu konfigurieren.';
$string['nonnumericgrade'] = 'Die Aufgabe muss auf eine numerische Bewertung eingestellt sein. Bitte überprüfen Sie die Bewertungseinstellungen.';
$string['noservers'] = 'Keine externen Server konfiguriert';
$string['nothingtograde'] = 'Keine Abgaben gefunden - nichts zu bewerten.';
$string['nouploads'] = 'Keine Uploads';
$string['nouploadsleft'] = 'Sie haben keine Uploads mehr für diese Aufgabe übrig.';
$string['pluginname'] = 'Abgabe an externen Server';
$string['privacy:metadata'] = 'Das Plugin "Abgabe an externen Server" speichert keine personenbezogenen Daten.';
$string['quickgradinginfo'] = '<ul><li>Sie können die Bewertungen und das Feedback für alle Teilnehmer/innen mit einem bestimmten Status auf einmal aktualisieren.</li><li>Sie können einzelne Teilnehmer auf der Seite {$a} aktualisieren.</li><li>Bestehende Bewertungen und Feedback werden überschrieben.</li></ul>';
$string['selectserver'] = ' --- externen Server auswählen ---';
$string['server:auth_api_key'] = 'API-Schlüssel';
$string['server:auth_jwt'] = 'OAuth2 (JWT)';
$string['server:auth_oauth2'] = 'OAuth2';
$string['server:auth_secret'] = 'Geheimer Schlüssel';
$string['server:auth_secret_help'] = 'Dieser dient sowohl als API-Schlüssel für die API-Schlüssel-Authentifizierung als auch zum Hashen von Nutzdaten, um die Integrität der an den Server gesendeten Daten zu überprüfen.';
$string['server:auth_type'] = 'Authentifizierungstyp';
$string['server:auth_type_help'] = 'Wählen Sie den Authentifizierungstyp für den externen Server aus.';
$string['server:contact'] = 'Kontakt';
$string['server:contact_email'] = 'E-Mail';
$string['server:contact_email_help'] = 'Geben Sie die E-Mail-Adresse des Kontakts ein.';
$string['server:contact_email_invalid'] = 'Die Kontakt-E-Mail-Adresse ist ungültig.';
$string['server:contact_name'] = 'Kontaktname';
$string['server:contact_name_help'] = 'Geben Sie den Namen des Kontakts ein.';
$string['server:contact_org'] = 'Organisation';
$string['server:contact_org_help'] = 'Geben Sie den Namen der Organisation ein.';
$string['server:contact_phone'] = 'Telefonnummer';
$string['server:contact_phone_help'] = 'Geben Sie die Telefonnummer des Kontakts ein.';
$string['server:form_url'] = 'Upload-URL';
$string['server:form_url_help'] = 'Geben Sie die URL zum Datei-Upload-Skript ein. Zum Beispiel: http://ihreseite.de/moodle_external_assignment_upload.php';
$string['server:groupinfo'] = 'Gruppeninformationen';
$string['server:groupinfo_help'] = 'Diese Einstellung legt fest, ob der externe Server Gruppeninformationen benötigt, um ordnungsgemäß zu funktionieren. Setzen Sie auf \'erforderlich\', wenn der externe Server Gruppeninformationen benötigt. Setzen Sie auf \'nicht erforderlich\', wenn Gruppeninformationen nicht verwendet werden.';
$string['server:groupinfo_must_be_sent'] = 'erforderlich';
$string['server:groupinfo_not_needed'] = 'nicht erforderlich';
$string['server:hashalgorithm'] = 'Hash-Algorithmus';
$string['server:hashalgorithm_help'] = 'Wählen Sie den richtigen Hash-Algorithmus für den externen Server, er muss auf beiden Systemen verfügbar sein. Ändern Sie dies nicht, wenn Sie nicht wissen, was Sie tun. Externe Server vor Moodle 3.1 verwendeten standardmäßig "sha1". Wir haben jetzt standardmäßig auf "sha256" umgestellt.';
$string['server:info'] = 'Kommentar';
$string['server:info_help'] = 'Wenn Sie einen Kommentar haben, geben Sie ihn bitte hier ein.';
$string['server:info_missing'] = 'Erforderliches Feld fehlt.';
$string['server:jwt_issuer'] = 'JWT-Aussteller';
$string['server:name'] = 'Name';
$string['server:name_duplicate'] = 'Dieser Name wird bereits verwendet';
$string['server:name_help'] = 'Dieser Name wird für diesen externen Server angezeigt.';
$string['server:oauth2_client_id'] = 'OAuth2-Client-ID';
$string['server:oauth2_client_secret'] = 'OAuth2-Client-Secret';
$string['server:oauth2_token_endpoint'] = 'OAuth2-Token-Endpunkt';
$string['server:sslverification'] = 'SSL-Zertifikate/Identitäten überprüfen';
$string['server:sslverification_help'] = 'Steuert die SSL-Zertifikatüberprüfung für externe Serververbindungen.<ul><li><strong>Ja, Identität überprüfen</strong> - der Hostname des Hosts wird gegen den Hostnamen des Host-Zertifikats überprüft. Das Peer-Zertifikat wird überprüft.</li><li><strong>Ja, wenn Name vorhanden</strong> - das Host-Zertifikat wird auf einen verfügbaren Namenseintrag überprüft. Das Peer-Zertifikat wird überprüft.</li><li><strong>Keine Überprüfung</strong> - keines der Zertifikate wird überprüft.</li></ul>';
$string['server:sslverification_identity'] = 'Ja, Identität überprüfen';
$string['server:sslverification_ifnameexists'] = 'Ja, wenn Name vorhanden';
$string['server:sslverification_none'] = 'Keine Überprüfung';
$string['server:url'] = 'URL';
$string['server:url_help'] = 'Geben Sie die URL zum externen Server ein. Zum Beispiel: http://ihreseite.de/moodle_external_assignment.php';
$string['server:url_invalid'] = 'Die URL des externen Servers ist ungültig.';
$string['servers'] = 'Server';
$string['sslerror'] = 'Die Verbindung wurde beendet, bevor eine Ausgabe erfolgte. Möglicherweise gibt es ein Problem mit dem SSL-Zertifikat. Bitte installieren Sie die Zertifikate der vertrauenswürdigen Zertifizierungsstellen auf Ihrem Server!';
$string['start'] = 'Start';
$string['studentview'] = 'Teilnehmer/innenansicht';
$string['submissionswarning'] = 'Es gibt bereits Abgaben mit maximal {$a} Upload(s) für diese Aufgabe. Einige Einstellungen können nicht mehr geändert werden.';
$string['submitted'] = 'alle abgegeben';
$string['teacherview'] = 'Trainer/innenansicht';
$string['testing'] = 'Teste externen Server "{$a->name}" auf {$a->site}';
$string['ungraded'] = 'alle unbewertet';
$string['unknownerror'] = 'HTTP-Fehlercode {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['unknownserver'] = 'Ungültige Server-ID';
$string['unlimited'] = 'Unbegrenzt';
$string['unlimiteduploads'] = 'unbegrenzt';
$string['upload'] = 'Hochladen';
$string['uploadattempts'] = 'Uploads';
$string['uploads'] = 'Anzahl der Uploads';
$string['uploads_help'] = 'Legt fest, wie oft ein/e Teilnehmer/in eine Datei hochladen kann. Nur die zuletzt hochgeladene Datei wird auf dem Moodle-Server gespeichert';
