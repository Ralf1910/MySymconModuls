<?
//  Modul zur Berechnung und Anzeige der Abholtermine
//
//	Version 0.1
//
// ************************************************************

class BSR extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();

		// Updates einstellen
		$this->RegisterTimer("UpdateAbholtermine", 60*60*1000, 'BSR_UpdateAbholtermine($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		$this->SetTimerInterval("UpdateAbholtermine", 60*60*1000);


		// Variablen aktualisieren
		$this->MaintainVariable("nextBSRDate", "nächster Abholtermin BSR", 1, "~UnixTimestampDate", 10, true);
		$this->MaintainVariable("BSRAbholungInTagen", "BSR Abholung in Tagen", 3, "", 20, true);
		$this->MaintainVariable("nextGruenerPunktDate", "nächster Abholtermin Grüner Punkt", 1, "~UnixTimestampDate", 30, true);
		$this->MaintainVariable("GruenerPunktAbholungInTagen", "Grüner Punkt Abholung in Tagen", 3, "", 40, true);

		//Instanz ist aktiv
		$this->SetStatus(102);
	}


	public function UpdateAbholtermine() {
		$AbholungHausmuell 	= array("11.01.2017", "25.01.2017", "08.02.2017", "22.02.2017", "08.03.2017", "22.03.2017", "05.04.2017", "20.04.2017", "04.05.2017", "17.05.2017", "31.05.2017", "14.06.2017", "28.06.2017",
									"12.07.2017", "26.07.2017", "09.08.2017", "23.08.2017", "06.09.2017", "20.09.2017", "05.10.2017", "18.10.2017", "02.11.2017", "15.11.2017", "29.11.2017", "13.12.2017", "28.12.2017");
		$AbholungWertstoffe	= array("12.01.2017", "26.01.2017", "09.02.2017", "23.02.2017", "09.03.2017", "23.03.2017", "06.04.2017", "21.04.2017", "05.05.2017", "18.05.2017", "01.06.2017", "15.06.2017", "29.06.2017",
									"13.07.2017", "27.07.2017", "10.08.2017", "24.08.2017", "07.09.2017", "21.09.2017", "06.10.2017", "19.10.2017", "03.11.2017", "16.11.2017", "30.11.2017", "14.12.2017", "29.12.2017");

		$heute 				= date("d.m.Y", time());
		$morgen 			= date("d.m.Y", time() + 3600*24);
 		$uebermorgen 		= date("d.m.Y", time() + 3600*24*2);

		foreach ($AbholungHausmuell as &$HausmuellTermin) {
			$dateTimestampNow	= time();
			$dateTimestampHausmuellTermin	= strtotime($HausmuellTermin);

			if ($dateTimestampHausmuellTermin > $dateTimestampNow)
				SetValue($this->GetIDForIdent("nextBSRDate"), $HausmuellTermin);
				return true;
			}
		}

		return false;
	}



	// Variablenprofile erstellen
	private function CreateVarProfile($name, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
		if (!IPS_VariableProfileExists($name)) {
			IPS_CreateVariableProfile($name, $ProfileType);
			IPS_SetVariableProfileText($name, "", $Suffix);
			IPS_SetVariableProfileValues($name, $MinValue, $MaxValue, $StepSize);
			IPS_SetVariableProfileDigits($name, $Digits);
			IPS_SetVariableProfileIcon($name, $Icon);
		 }
	}

	//Variablenprofil für den Action erstellen
	private function CreateVarProfileVR200Action() {
		if (!IPS_VariableProfileExists("VR200.Action")) {
			IPS_CreateVariableProfile("VR200.Action", 1);
			IPS_SetVariableProfileText("VR200.Action", "", "");
			IPS_SetVariableProfileAssociation("VR200.Action", 0, "Nicht aktiv", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.Action", 1, "reinigt", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.Action", 2, "2", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.Action", 4, "Fahre zurück zur Basis", "", 0xFFFF00);
		 }
	}

	//Variablenprofil für die Battery erstellen
	private function CreateVarProfileVR200IsCharging() {
		if (!IPS_VariableProfileExists("VR200.isCharging")) {
			IPS_CreateVariableProfile("VR200.isCharging", 0);
			IPS_SetVariableProfileText("VR200.isCharging", "", "");
			IPS_SetVariableProfileAssociation("VR200.isCharging", 0, "entlädt", "", 0xFF0000);
			IPS_SetVariableProfileAssociation("VR200.isCharging", 1, "lädt", "", 0x00FF00);
		 }
	}

	//Variablenprofil für die Battery erstellen
	private function CreateVarProfileVR200Charge() {
		if (!IPS_VariableProfileExists("VR200.Charge")) {
			IPS_CreateVariableProfile("VR200.Charge", 1);
			IPS_SetVariableProfileValues("VR200.Charge", 0, 100, 1);
			IPS_SetVariableProfileText("VR200.Charge", "", " %");
		 }
	}

	//Variablenprofil für den Status erstellen
	private function CreateVarProfileVR200State() {
		if (!IPS_VariableProfileExists("VR200.State")) {
			IPS_CreateVariableProfile("VR200.State", 1);
			IPS_SetVariableProfileText("VR200.State", "", "");
			IPS_SetVariableProfileAssociation("VR200.State", 1, "angehalten", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.State", 2, "unterwegs", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.State", 3, "pausiert", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.State", 4, "Weg blockiert", "", 0xFFFF00);
		 }
	}

    // Variablenprofil für den Reinigungmodus
	private function CreateVarProfileVR200Mode() {
		if (!IPS_VariableProfileExists("VR200.Mode")) {
			IPS_CreateVariableProfile("VR200.Mode", 1);
			IPS_SetVariableProfileText("VR200.Mode", "", "");
			IPS_SetVariableProfileAssociation("VR200.Mode", 1, "normal", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.Mode", 2, "eco", "", 0xFFFF00);
		 }
	}

	// Variablenprofil für den Categroy
	private function CreateVarProfileVR200Category() {
		if (!IPS_VariableProfileExists("VR200.Category")) {
			IPS_CreateVariableProfile("VR200.Category", 1);
			IPS_SetVariableProfileText("VR200.Category", "", "");
			IPS_SetVariableProfileAssociation("VR200.Category", 1, "1 - ???", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.Category", 2, "2 - ???", "", 0xFFFF00);
		 }
	}

	 // Variablenprofil für den Dockingmodus erstellen
	private function CreateVarProfileVR200isDocked() {
		if (!IPS_VariableProfileExists("VR200.isDocked")) {
			IPS_CreateVariableProfile("VR200.isDocked", 0);
			IPS_SetVariableProfileText("VR200.isDocked", "", "");
			IPS_SetVariableProfileAssociation("VR200.isDocked", 0, "unterwegs", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("VR200.isDocked", 1, "in der Dockingstation", "", 0xFFFF00);
		 }
	}

	// Variablenprofil für den Zeitplan
	private function CreateVarProfileVR200isScheduleEnabled() {
		if (!IPS_VariableProfileExists("VR200.isScheduleEnabled")) {
			IPS_CreateVariableProfile("VR200.isScheduleEnabled", 0);
			IPS_SetVariableProfileText("VR200.isScheduleEnabled", "", "");
			IPS_SetVariableProfileAssociation("VR200.isScheduleEnabled", 0, "Zeitplan deaktiviert", "", 0xFF0000);
			IPS_SetVariableProfileAssociation("VR200.isScheduleEnabled", 1, "Zeitplan aktiviert", "", 0x00FF00);
		 }
	}

	// Variablenprofil für die Sichtung der Dockingstation
	private function CreateVarProfileVR200dockHasBeenSeen() {
		if (!IPS_VariableProfileExists("VR200.dockHasBeenSeen")) {
			IPS_CreateVariableProfile("VR200.dockHasBeenSeen", 0);
			IPS_SetVariableProfileText("VR200.dockHasBeenSeen", "", "");
			IPS_SetVariableProfileAssociation("VR200.dockHasBeenSeen", 0, "Dockingstation außer Sichtweite", "", 0xFF0000);
			IPS_SetVariableProfileAssociation("VR200.dockHasBeenSeen", 1, "Dockingstation in Sichtweite", "", 0x00FF00);
		 }
	}

	//Variablenprofil für die Befehle
		private function CreateVarProfileVR200Commands() {
			if (!IPS_VariableProfileExists("VR200.Commands")) {
				IPS_CreateVariableProfile("VR200.Commands", 0);
				IPS_SetVariableProfileText("VR200.Commands", "", "");
				IPS_SetVariableProfileAssociation("VR200.Commands", 0, "Befehl nicht verfügbar", "", 0xFF0000);
				IPS_SetVariableProfileAssociation("VR200.Commands", 1, "Befehl verfügbar", "", 0x00FF00);
			 }
	}

	// Fehlermeldungen des VR200 in Klartext übersetzen
	private function TranslateErrorMessages($error) {
		if (strcasecmp($error, "ui_error_navigation_falling") == 0) 	return "Weg bitte freiräumen";
		if (strcasecmp($error, "ui_alert_invalid") == 0) 				return "Alles OK";
		if (strcasecmp($error, "ui_error_dust_bin_full") == 0) 			return "Staubbehälter voll";
		if (strcasecmp($error, "ui_error_dust_bin_emptied") == 0) 		return "Staubbehälter wurde geleert";
		if (strcasecmp($error, "ui_error_picked_up") == 0) 				return "Kobold VR200 bitte absetzen";
		if (strcasecmp($error, "ui_error_brush_stuck") == 0) 			return "Bürste blockiert";
		return $error;
	}

	// Integer Rückgabewerte in Boolean umwandeln
	private function ToBoolean($value) {
		if ($value == 1)
			return true;
		else
			return false;
	}

	// Roboter Status holen
	public function getState() {
		return $this->doAction("getRobotState");
	}

	// Reinigung im Normal Modus starten (muss in der Regel zwischendurch einmal geladen werden
	public function startCleaning() {
		$params = array("category" => 2, "mode" => 2, "modifier" => 2);
		SetValue($this->GetIDForIdent("lastCleaning"), time());
		return $this->doAction("startCleaning", $params);
	}

	// Reinigung im Eco Modus starten
	public function startEcoCleaning() {
		$params = array("category" => 2, "mode" => 1, "modifier" => 2);
		SetValue($this->GetIDForIdent("lastCleaning"), time());
		return $this->doAction("startCleaning", $params);
	}

	// Reinigung pausieren
	public function pauseCleaning() {
		return $this->doAction("pauseCleaning");
	}

	// Reinigung fortsetzen
	public function resumeCleaning() {
		return $this->doAction("resumeCleaning");
	}

	// Reinigung stoppen
	public function stopCleaning() {
		return $this->doAction("stopCleaning");
	}

	// Zurück zur Ladestation
	public function sendToBase() {
		return $this->doAction("sendToBase");
	}

	// Zeitplan aktivieren
	public function enableSchedule() {
		return $this->doAction("enableSchedule");
	}

	// Zeitplan deaktivieren
	public function disableSchedule() {
		return $this->doAction("disableSchedule");
	}

	// Zeitplan ermitteln
	public function getSchedule() {
		return $this->doAction("getSchedule");
	}

	// Action ausführen
	protected function doAction($command, $params = false) {
		$result = array("message" => "no serial or secret");
		if($this->ReadPropertyString("SerialNumber") !== false && $this->ReadPropertyString("SecretKey") !== false) {
			$payload = array("reqId" => "1", "cmd" => $command);
			if($params !== false) {
				$payload["params"] = $params;
			}
			$payload = json_encode($payload);
			$date = gmdate("D, d M Y H:i:s")." GMT";
			$data = implode("\n", array(strtolower($this->ReadPropertyString("SerialNumber")), $date, $payload));
			$hmac = hash_hmac("sha256", $data, $this->ReadPropertyString("SecretKey"));
			$headers = array(
	    	"Date: ".$date,
	    	"Authorization: NEATOAPP ".$hmac
			);
			$result = $this->requestKobold($this->ReadPropertyString("BaseURL").$this->ReadPropertyString("SerialNumber")."/messages", $payload, "POST", $headers);
		}
		return $result;
	}
	/*
		* VR200 Api.
		* Helper class to make requests against Kobold API
		*
		* PHP port based on https://github.com/kangguru/botvac
		*
		* Author: Tom Rosenback tom.rosenback@gmail.com  2016
		*/
	private static function requestKobold($url, $payload = array(), $method = "POST", $headers = array()) {
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
			if($method == "POST") {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
			}
			$requestHeaders = array(
				'Accept: application/vnd.neato.nucleo.v1'
			);
			if(count($headers) > 0) {
				$requestHeaders = array_merge($requestHeaders, $headers);
			}
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $requestHeaders);
			$result = curl_exec($ch);
			curl_close($ch);
			return json_decode($result, true);
	}


 }

