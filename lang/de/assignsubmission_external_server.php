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
 * @package    assignsubmission_external_server
 * @author     Stefan Weber (stefan.weber@think-modular.com)
 * @copyright  2025 think-modular
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Basics.
$string['pluginname'] = 'Externer Server';
$string['privacy:metadata'] = 'Das Externe Server Abgabe Plugin speichert keine persönlichen Daten.';

// Strings.
$string['defaultsettings'] = 'Standardeinstellungen';
$string['defaultsettings_help'] = 'Vorgabewerte, die in jeder Instanz geändert werden können.';
$string['maxbytes'] = 'Maximale Größe';
$string['maxbytes_help'] = 'Maximale Dateigröße für alle Aufgabenabgaben dieser Website (Obergrenze für alle Kurse und andere lokale Einstellungen)';
$string['servers'] = 'Server';
$string['addserver'] = 'Externen Server hinzufügen';
$string['editserver'] = 'Externen Server "{$a}" bearbeiten';
$string['noservers'] = 'Keine externen Server konfiguriert';
$string['filetypes'] = 'Erlaubte Dateitypen';
$string['filetypes_help'] = 'Die akzeptierten Dateitypen können als kommagetrennte Liste mit Dateiendungen eingeschränkt werden. Falls das Feld leer ist, sind alle Dateitypen erlaubt.';
$string['uploads'] = 'Anzahl der erlaubten Uploads';
$string['uploads_help'] = 'Legt fest wie oft ein/e Teilnehmer/in eine Datei hochladen darf. Nur die jeweils zuletzt hochgeladene Datei wird am Moodle Server gespeichert.';
$string['addexternalserver'] = 'Externen Server hinzufügen';
$string['editexternalserver'] = 'Externen Server "{$a}" bearbeiten';
$string['deleteexternalserver'] = 'Externen Server "{$a}" löschen';
$string['confirmdeleting'] = 'Sind Sie sicher, dass Sie den externen Server "{$a}" löschen möchten? Diese Aktion kann nicht rückgängig gemacht werden.';
$string['unknownserver'] = 'Ungültige Server ID';
$string['delete:disabled'] = 'Server wird verwendet und kann nicht gelöscht werden';
$string['delete:success'] = 'Server erfolgreich gelöscht';
$string['delete:error'] = 'Fehler beim Löschen des Servers';

// Server form.
$string['server:name'] = 'Name';
$string['server:name_help'] = 'Dieser Name wird für den externen Server angezeigt werden.';
$string['server:name_missing'] = 'Sie müssen einen Namen für den externen Server angeben.';
$string['server:name_duplicate'] = 'Dieser Name wird bereits verwendet';
$string['server:url'] = 'URL';
$string['server:url_help'] = 'URL zum Externen Server. Beispiel: http://yoursite.com/moodle_external_assignment.php';
$string['server:url_invalid'] = 'Die URL des externen Servers ist ungültig.';
$string['server:form_url'] = 'Upload URL';
$string['server:form_url_help'] = 'Die URL zum Uploadskript für Dateien auf den externen Server. Beispiel: http://yoursite.com/moodle_external_assignment_upload.php';
$string['server:auth_type'] = 'Authentifizierungstyp';
$string['server:auth_type_help'] = 'Wählen Sie den Authentifizierungstyp für den externen Server.';
$string['server:auth_api_key'] = 'API Key';
$string['server:auth_jwt'] = 'JWT';
$string['server:auth_oauth2'] = 'OAUTH2';
$string['server:auth_secret'] = 'Schlüssel:';
$string['server:auth_secret_help'] = 'Je nach Authentifizierungstyp, entweder ein API Key oder ein Shared Secret.';
$string['server:contact'] = 'Kontakt';
$string['server:contact_name'] = 'Konktaktperson';
$string['server:contact_name_help'] = 'Name des Serverkontaktes.';
$string['server:contact_email'] = 'E-Mail';
$string['server:contact_email_help'] = 'E-Mail Adresse des Serverkontaktes.';
$string['server:contact_email_invalid'] = 'Die E-Mail Adresse des Serverkontaktes ist ungültig.';
$string['server:contact_phone'] = 'Telefonnummer';
$string['server:contact_phone_help'] = 'Telefonnummer des Serverkontaktes.';
$string['server:contact_org'] = 'Organisation';
$string['server:contact_org_help'] = 'Name der Organisation des Serverkontaktes.';
$string['server:info'] = 'Kommentar';
$string['server:info_help'] = 'Hier können Sie einen Kommentar zum Server hinterlegen. Dieser wird nur für Administratoren angezeigt.';
$string['server:hashalgorithm'] = 'Hash-Algorithmus';
$string['server:hashalgorithm_help'] = 'Bitte wählen Sie den auf dem Externen Server verwendeten Hash-Algorithmus aus. Diese muss auf beiden Systemen verfügbar sein. Ändern Sie diese Einstellung nur, wenn Sie wissen, was Sie tun. Externe Server vor Moodle 3.1 verwendeten "sha1", ab Moodle 3.1 wechselten wir zu "sha256" als Standard.';
$string['server:sslverification'] = 'Verify SSL-Certificates/Identities';
$string['server:sslverification_help'] = 'Steuert die Überprüfung der SSL Zertifikate bei der Serverkommunikation.<ul><li><strong>Ja, auf Gleichheit</strong> - es wird überprüft, ob Host-Name in Zertifikat vorhanden ist und mit dem Hostnamen übereinstimmt. Das Peer-Zertifikat wird überprüft.</li><li><strong>Ja, ob Name vorhanden</strong> - es wird nur überprüft, ob das Namensfeld im Host-Zertifikat vorhanden ist. Das Peer-Zertifikat wird überprüft.</li><li><strong>Nein, keine Überprüfung</strong> - es wird <strong>keine</strong> Zertifikatsüberprüfung durchgeführt.</li></ul>';
$string['server:sslverification_identity'] = 'Ja, auf Gleichheit';
$string['server:sslverification_ifnameexists'] = 'Ja, ob Name vorhanden';
$string['server:sslverification_none'] = 'Nein, keine Überprüfung';
$string['server:groupinfo'] = 'Gruppeninformationen';
$string['server:groupinfo_help'] = 'Einstellung legt fest, ob der externe Server die Gruppeninformationen benötigt oder nicht. Auf \'erforderlich\' setzen, wenn externer Server Gruppeninfos benötigt um korrekt zu arbeiten. Auf \'nicht erforderlich\' setzen, wenn diese nicht benötigt werden';
$string['server:groupinfo_not_needed'] = 'nicht erforderlich';
$string['server:groupinfo_must_be_sent'] = 'erforderlich';

// Servertest.
$string['checkconnection'] = 'Verbindung prüfen';
$string['createassignment'] = 'Aufgabe erstellen';
$string['studentview'] = 'Teilnehmer/innen Ansicht';
$string['teacherview'] = 'Trainer/innen Ansicht';
$string['loadgrades'] = 'Bewertungen laden';