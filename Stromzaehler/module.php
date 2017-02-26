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
		$this->RegisterTimer("UpdateStromzaehler", 60*1000, 'Stromzaehler_UpdateStromzaehler($_IPS[\'TARGET\']);');
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
	private function CreateVarProfileStromzaehlerEnergy() {
		if (!IPS_VariableProfileExists("Stromzaehler.Energy")) {
			IPS_CreateVariableProfile("Stromzaehler.Energy", 2);
			IPS_SetVariableProfileText("Stromzaehler.Energy", "", " kWh");
		 }
	}

	//Variablenprofil für die Battery erstellen
	private function CreateVarProfileStromzaehlerPower() {
			if (!IPS_VariableProfileExists("Stromzaehler.Power")) {
				IPS_CreateVariableProfile("Stromzaehler.Power", 1);
				IPS_SetVariableProfileText("Stromzaehler.Power", "", " W");
			 }
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

