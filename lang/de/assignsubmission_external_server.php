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
$string['pluginname'] = 'Abgabe an externen Server';
$string['privacy:metadata'] = 'Das Abgabe an externen Server Plugin speichert keine persönlichen Daten.';

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
$string['file_uploaded'] = 'Datei erfolgreich an den externen Server hochgeladen.';
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
$string['server:auth_oauth2'] = 'OAuth2';
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
$string['server:oauth2_endpoint'] = 'OAuth2 Endpoint';
$string['server:oauth2_client_id'] = 'OAuth2 Client ID';
$string['server:jwt_issuer'] = 'JWT Issuer';
$string['server:jwt_audience'] = 'JWT Audience';

// Servertest.
$string['checkconnection'] = 'Verbindung prüfen';
$string['testing'] = 'Testen des externen Servers "{$a->name}" auf der Seite "{$a->site}"';
$string['createassignment'] = 'Aufgabe erstellen';
$string['studentview'] = 'Teilnehmer/innen Ansicht';
$string['teacherview'] = 'Trainer/innen Ansicht';
$string['loadgrades'] = 'Bewertungen laden';
$string['sslerror'] = 'Die Verbindung wurde getrennt, bevor der Server eine Rückmeldung lieferte.
Möglicherweise gibt es ein Problem mit dem SSL Zertifikat.
Bitte installieren Sie das Trusted CA Zertifikat auf Ihrem Server!';
$string['unknownerror'] = 'HTTP Fehlercode {$a}. Bitte überprüfen Sie die Servereinstellungen und die Verbindung zum externen Server.';

// Assignment form.
$string['enabled'] = 'Aktiviert';
$string['enabled_help'] = 'Teilnehmer/innen können eine Abgabe an einen vorkonfigurierten externen Server senden, der eine Antwort zurücksendet.';
$string['externalserver'] = 'Server';
$string['externalserver_help'] = 'Wählen Sie den externen Server, an den die Abgabe gesendet werden soll. Externe Server müssen zuvor in den Einstellungen des Plugins konfiguriert werden.';
$string['selectserver'] = ' --- Externen Server auswählen ---';
$string['submissionswarning'] = 'Es gibt bereits {$a} Abgabe(n) für diese Aufgabe. Deshalb sind einige der Einstellungen deaktiviert.';

// Other new.
$string['unlimiteduploads'] = 'unbegrenzt';
$string['connectionstatus'] = 'Verbindung zum externem Server';
$string['quickupload'] = 'Quick Upload';
$string['quickupload_help'] = 'Neue Datei direkt an den externen Server schicken, zählt als Uploadversuch.';
$string['upload'] = 'Upload';
$string['needselectfile'] = 'Es wurde keine Datei ausgewählt.';
$string['lastupload'] = 'Letzer Upload';
$string['getgrades'] = 'Noten und Feedback abrufen';
$string['externalservertitle'] = 'Externer Server "{$a}"';
$string['uploadattempts'] = 'Uploads';
$string['viewsubmissionat'] = 'Abgabe ansehen';
$string['quickgradinginfo'] = '<ul><li>Sie können die Noten und Feedbacks für alle Teilnehmer/innen eines bestimmten Status auf einmal aktualisieren.</li>
    <li>Einzelne Teilnehmer/innen können Sie auf der Seite für die {$a} aktualisieren.</li>
    <li> Vorhandene Noten und Feedbacks werden dabei überschrieben.</li></ul>';
$string['start'] = 'Start';
$string['downloadgradesfor'] = 'Bewertungen herunterladen für';
$string['all'] = 'alle Teilnehmer/innen';
$string['ungraded'] = 'alle nicht bewerteten';
$string['submitted'] = 'alle abgegebenen';
$string['confirmgrading'] = 'Bewertungen und Feedback für <strong>{$a->for}</strong> vom externen Server <strong>{$a->server}</strong> laden.<p><strong>Vorhandene Bewertungen und Feedback werden überschrieben!</strong></p>';
$string['nonnumericgrade'] = 'Die Bewertung der Abgabe muss numerisch sein. Bitte kontrollieren Sie die Bewertungseinstellungen.';
$string['nothingtograde'] = 'Keine entsprechenden Abgaben gefunden - nichts wurde bewertet.';
$string['couldnotgetgrades'] = 'Die Bewertungen konnten nicht vom externen Server abgerufen werden. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['gradesupdated'] = 'Bewertungen erfolgreich aktualisiert. {$a} Bewertung(en) aktualisiert.';
$string['nouploads'] = 'Keine Uploads';
$string['unlimited'] = 'Unbegrenzt';
$string['nouploadsleft'] = 'Sie haben keine Uploads mehr für diese Aufgabe.';
$string['cannotdelete'] = 'Dieser Server kann nicht gelöscht werden, da er in {$a} Aufgabe(n) verwendet wird.';
$string['noneselected'] = 'Kein externer Server ausgewählt. Bitte wählen Sie in den Einstellungen einen externen Server, der für diese Aufgabe verwendet werden soll.';
$string['noneselectedstudent'] = 'Es wurde vom Trainer/der Trainerin kein externer Server ausgewählt. Bitte kontaktieren Sie den/die Trainer/in, um einen externen Server für diese Aufgabe zu konfigurieren.';

// Errors.
$string['error:couldnotgetjwttoken'] = 'Konnte keinen JWT Token vom externen Server abrufen. HTTP Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['error:couldnotgetoauth2token'] = 'Konnte keinen OAuth2 Token vom externen Server abrufen. HTTP Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
$string['error:requestfailed'] = 'Die Anfrage an den externen Server ist fehlgeschlagen. HTTP-Statuscode: {$a}. Bitte überprüfen Sie die Servereinstellungen und versuchen Sie es erneut.';
