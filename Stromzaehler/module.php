<?
//  Modul zur Steuerung des Vorwerk Kobold VR200
//
//	Version 0.9
//
// ************************************************************

class Stromzaehler extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		$this->RegisterPropertyInteger("EKM-Counter ObjektID", 1);
		$this->RegisterPropertyInteger("EKM-Current ObjektID", 2);
		$this->RegisterPropertyInteger("Persistenter Counter", 1);
		$this->RegisterPropertyInteger("Counter Offset", 1);
		$this->RegisterPropertyInteger("Korrekturwert", 1);

		// Variablenprofile anlegen
		$this->CreateVarProfileStromzaehlerEnergy();
		$this->CreateVarProfileStromzaehlerPower();

		// Interne Variablen anlegen
		$OffsetObjektID = $this->RegisterVariableInteger("Offset", "Counter Offset");

		// Updates einstellen
		$this->RegisterTimer("UpdateStromzaehler", $this->ReadPropertyInteger("UpdateStromzaehler")*60*1000, 'Stromzaehler_UpdateStromzaehler($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		//Timerzeit setzen in Minuten
		$this->SetTimerInterval("UpdateStromzaehler", $this->ReadPropertyInteger("UpdateStromzaehler")*60*1000);

		// Variablenprofile anlegen
		$this->CreateVarProfileNRGEnergy();
		$this->CreateVarProfileNRGPower();

		// Variablen aktualisieren
		$this->MaintainVariable("currentPower", "aktuelle Leistung", 2, "", 10, true);
		$this->MaintainVariable("energyConsumption", "Zählerstand", 2, "", 20, true);
		$this->MaintainVariable("todayEnergyConsumption", "Heutige kWh", 2, "", 30, true);
		$this->MaintainVariable("yearEnergyConsumption", "Rollierender Jahreswert", 2, "", 40, true);

		//Instanz ist aktiv
		$this->SetStatus(102);

	}



	public function UpdateStromzaehler() {
		if ((GetValueInteger($this->ReadPropertyInteger("EKM-Counter ObjektID")) + GetValueInteger($OffsetObjektID))>= GetValueInteger(28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/ )) {
 	SetValueInteger (28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/, GetValueInteger(49624 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter]*/) + GetValueInteger(38863 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Offset]*/));
 } else {
 	SetValueInteger(38863 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Offset]*/, GetValueInteger(28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/));
	SetValueInteger(28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/, GetValueInteger(49624 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter]*/) + GetValueInteger(38863 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Offset]*/));
 }

 $ret = SetValueFloat(50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/  , Round(GetValue(28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/)*1.00/1000 + 133437 /* 14.11.2015 */,4));
 if ($ret == false) { echo "Fehler beim setzen des Haushalt Energieverbrauchs"; }

 $ret = SetValueFloat(24149 /*[Werte & Stati\Strom\Haushalt\aktuelle Leistung]*/ , Round(GetValue (53144 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Current]*/ )));
 if ($ret == false) { echo "Fehler beim setzen der aktuellen Haushaltsleistung"; }

 $historischeWerte = AC_GetLoggedValues(27366 /*[Archiv]*/ , 50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/ , strtotime('today midnight') - 50000, strtotime('today midnight'), 1);
 foreach($historischeWerte as $wertZumTagesbeginn) {
		SetValueFloat(13989 /*[Werte & Stati\Strom\Haushalt\Heute Verbraucht]*/, GetValueFloat(50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/) - $wertZumTagesbeginn['Value']);
	}


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
	private function CreateVarProfileNRGEnergy() {
		if (!IPS_VariableProfileExists("Stromzaehler.Energy")) {
			IPS_CreateVariableProfile("Stromzaehler.Energy", 2);
			IPS_SetVariableProfileText("Stromzaehler.Energy", "", " kWh");
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

