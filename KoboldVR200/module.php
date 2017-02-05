<?
//  Modul zur Steuerung des Vorwerk Kobold VR200
//
//	Version 0.9
//
// ************************************************************

class KoboldVR200 extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		$this->RegisterPropertyString("BaseURL", "https://nucleo.ksecosys.com/vendors/vorwerk/robots/");
		$this->RegisterPropertyString("SerialNumber", "");
		$this->RegisterPropertyString("SecretKey", "");
		$this->RegisterPropertyInteger("UpdateKobold", 1);
		$this->RegisterPropertyInteger("CleaningIntervalWinter", 2);
		$this->RegisterPropertyInteger("CleaningIntervalSpring", 3);
		$this->RegisterPropertyInteger("CleaningIntervalSummer", 3);
		$this->RegisterPropertyInteger("CleaningIntervalAutum", 2);

		// Variablenprofile anlegen
		$this->CreateVarProfileVR200Action();
		$this->CreateVarProfileVR200IsCharging();
		$this->CreateVarProfileVR200Charge();
		$this->CreateVarProfileVR200State();
		$this->CreateVarProfileVR200Mode();
		$this->CreateVarProfileVR200Category();
		$this->CreateVarProfileVR200isDocked();
		$this->CreateVarProfileVR200isScheduleEnabled();
		$this->CreateVarProfileVR200dockHasBeenSeen();
		$this->CreateVarProfileVR200Commands();

		// Updates einstellen
		$this->RegisterTimer("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKobold")*60*1000, 'VR200_UpdateKoboldData($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();
		if (($this->ReadPropertyString("SerialNumber") != "") && ($this->ReadPropertyString("SecretKey") != "")){
			//Timerzeit setzen in Minuten
			$this->SetTimerInterval("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKobold")*60*1000);

			// Variablenprofile anlegen
			$this->CreateVarProfileVR200Action();
			$this->CreateVarProfileVR200IsCharging();
			$this->CreateVarProfileVR200Charge();
			$this->CreateVarProfileVR200State();
			$this->CreateVarProfileVR200Mode();
			$this->CreateVarProfileVR200Category();
			$this->CreateVarProfileVR200isDocked();
			$this->CreateVarProfileVR200isScheduleEnabled();
			$this->CreateVarProfileVR200dockHasBeenSeen();
			$this->CreateVarProfileVR200Commands();

			// Variablen aktualisieren
			$this->MaintainVariable("lastCleaning", "letzte Reinigung", 1, "~UnixTimestampDate", 10, true);
			$this->MaintainVariable("version", "Version", 1, "", 10, true);
			$this->MaintainVariable("reqId", "Requested ID", 1, "", 20, true);
			$this->MaintainVariable("error", "Fehlermeldung", 3, "", 30, true);
			$this->MaintainVariable("state", "Status", 1, "VR200.State", 40, true);
			$this->MaintainVariable("action", "Action", 1, "", 50, true);
			$this->MaintainVariable("cleaningCategory", "Reinigungskategory", 1, "VR200.Category", 60, true);
			$this->MaintainVariable("cleaningMode", "Reinigungsmodus", 1, "VR200.Mode", 70, true);
			$this->MaintainVariable("cleaningModifier", "Reinigungsmodifier", 1, "", 80, true);
			$this->MaintainVariable("cleaningSpotWidth", "Spotbreite", 1, "", 90, true);
			$this->MaintainVariable("cleaningSpotHeight", "Spothöhe", 1, "", 100, true);
			$this->MaintainVariable("detailsIsCharging", "Lädt", 0, "VR200.isCharging", 110, true);
			$this->MaintainVariable("detailsIsDocked", "In der Ladestation", 0, "VR200.isDocked", 120, true);
			$this->MaintainVariable("detailsIsScheduleEnabled", "Zeitplan aktiviert", 0, "VR200.isScheduleEnabled", 130, true);
			$this->MaintainVariable("detailsDockHasBeenSeen", "Dockingstation gesichtet", 0, "VR200.dockHasBeenSeen", 140, true);
			$this->MaintainVariable("detailsCharge", "Ladezustand", 1, "VR200.Charge", 150, true);
			$this->MaintainVariable("metaModelName", "Modelname", 3, "", 160, true);
			$this->MaintainVariable("metaFirmware", "Firmware", 3, "", 170, true);
			$this->MaintainVariable("availableCommandsStart", "Kommando Start", 0, "VR200.Commands", 200, true);
			$this->MaintainVariable("availableCommandsStop", "Kommando Stop", 0, "VR200.Commands", 210, true);
			$this->MaintainVariable("availableCommandsPause", "Kommando Pause", 0, "VR200.Commands", 220, true);
			$this->MaintainVariable("availableCommandsResume", "Kommando Resume", 0, "VR200.Commands", 230, true);
			$this->MaintainVariable("availableCommandsGoToBase", "Kommando GoToBase", 0, "VR200.Commands", 240, true);

			//Instanz ist aktiv
			$this->SetStatus(102);
		} else {
			//Instanz ist inaktiv
			$this->SetStatus(104);
		}
	}



	public function UpdateSerialAndKey() {

	}

	public function UpdateKoboldData() {
		$robotState = $this->doAction("getRobotState");

		// Daten aktualisieren
		SetValue($this->GetIDForIdent("version"), $robotState['version']);
		SetValue($this->GetIDForIdent("reqId"), $robotState['reqId']);
		SetValue($this->GetIDForIdent("error"), $this->TranslateErrorMessages($robotState['error']));
		SetValue($this->GetIDForIdent("state"), $robotState['state']);
		SetValue($this->GetIDForIdent("action"), $robotState['action']);
		SetValue($this->GetIDForIdent("cleaningCategory"), $robotState['cleaning']['category']);
		SetValue($this->GetIDForIdent("cleaningMode"), $robotState['cleaning']['mode']);
		SetValue($this->GetIDForIdent("cleaningModifier"), $robotState['cleaning']['modifier']);
		SetValue($this->GetIDForIdent("cleaningSpotWidth"), $robotState['cleaning']['spotWidth']);
		SetValue($this->GetIDForIdent("cleaningSpotHeight"), $robotState['cleaning']['spotHeight']);
		SetValue($this->GetIDForIdent("detailsIsCharging"), $this->ToBoolean($robotState['details']['isCharging']));
		SetValue($this->GetIDForIdent("detailsIsDocked"), $this->ToBoolean($robotState['details']['isDocked']));
		SetValue($this->GetIDForIdent("detailsIsScheduleEnabled"), $this->ToBoolean($robotState['details']['isScheduleEnabled']));
		SetValue($this->GetIDForIdent("detailsDockHasBeenSeen"), $this->ToBoolean($robotState['details']['dockHasBeenSeen']));
		SetValue($this->GetIDForIdent("detailsCharge"), $robotState['details']['charge']);
		SetValue($this->GetIDForIdent("metaModelName"), $robotState['meta']['modelName']);
		SetValue($this->GetIDForIdent("metaFirmware"), $robotState['meta']['firmware']);
		SetValue($this->GetIDForIdent("availableCommandsStart"), $this->ToBoolean($robotState['availableCommands']['start']));
		SetValue($this->GetIDForIdent("availableCommandsStop"), $this->ToBoolean($robotState['availableCommands']['stop']));
		SetValue($this->GetIDForIdent("availableCommandsPause"), $this->ToBoolean($robotState['availableCommands']['pause']));
		SetValue($this->GetIDForIdent("availableCommandsResume"), $this->ToBoolean($robotState['availableCommands']['resume']));
		SetValue($this->GetIDForIdent("availableCommandsGoToBase"), $this->ToBoolean($robotState['availableCommands']['goToBase']));
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

