<?
// Klassendefinition
class KoboldVR200 extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		$this->RegisterPropertyString("BaseURL", "https://nucleo.ksecosys.com/vendors/vorwerk/robots/");
		$this->RegisterPropertyString("SerialNumber", "");
		$this->RegisterPropertyString("SecretKey", "");
		$this->RegisterPropertyInteger("UpdateKoboldWorking", 1);
		$this->RegisterPropertyInteger("UpdateKoboldCharging", 5);
		$this->RegisterPropertyInteger("CleaningIntervalWinter", 2);
		$this->RegisterPropertyInteger("CleaningIntervalSpring", 3);
		$this->RegisterPropertyInteger("CleaningIntervalSummer", 3);
		$this->RegisterPropertyInteger("CleaningIntervalAutum", 2);

		//Variablenprofil anlegen ($name, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon)
		$this->CreateVarProfileVR200IsCharging();
		$this->CreateVarProfileVR200Charge();
		$this->CreateVarProfileVR200Status();

//		$this->CreateVarProfile("WGW.Rainfall", 2, " Liter/m²" ,0 , 10, 0 , 2, "Rainfall");
//		$this->CreateVarProfile("WGW.Sunray", 2, " W/m²", 0, 2000, 0, 2, "Sun");
//		$this->CreateVarProfile("WGW.Visibility", 2, " km", 0, 0, 0, 2, "");
//		$this->CreateVarProfileWGWWindSpeedkmh();
//		$this->CreateVarProfileWGWUVIndex();
		//Timer erstellen
		$this->RegisterTimer("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKoboldCharging")*60*1000, 'VR200_UpdateKoboldData($_IPS[\'TARGET\']);');
	}
	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();
		if (($this->ReadPropertyString("SerialNumber") != "") && ($this->ReadPropertyString("SecretKey") != "")){
			//Timerzeit setzen in Minuten
			$this->SetTimerInterval("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKoboldCharging")*60*1000);
			// Variablenprofile anlegen
			$this->CreateVarProfileVR200IsCharging();
			$this->CreateVarProfileVR200Charge();
			$this->CreateVarProfileVR200Status();

			$keep = true; // $this->ReadPropertyBoolean("FetchNow");
			$this->MaintainVariable("lastCleaning", "letzte Reinigung", 1, "~UnixTimestampDate", 10, $keep);
			$this->MaintainVariable("version", "Version", 1, "", 10, $keep);
			$this->MaintainVariable("reqId", "Requested ID", 1, "", 20, $keep);
			$this->MaintainVariable("error", "Fehlermeldung", 3, "", 30, $keep);
			$this->MaintainVariable("state", "Status", 1, "VR200.Status", 40, $keep);
			$this->MaintainVariable("action", "Action", 1, "", 50, $keep);
			$this->MaintainVariable("cleaningCategory", "Reinigungskategory", 1, "", 60, $keep);
			$this->MaintainVariable("cleaningMode", "Reinigungsmodus", 1, "VR200.Mode", 70, $keep);
			$this->MaintainVariable("cleaningModifier", "Reinigungsmodifier", 1, "", 80, $keep);
			$this->MaintainVariable("cleaningSpotWidth", "Spotbreite", 1, "", 90, $keep);
			$this->MaintainVariable("cleaningSpotHeight", "Spothöhe", 1, "", 100, $keep);
			$this->MaintainVariable("detailsIsCharging", "Lädt", 0, "VR200.isCharging", 110, $keep);
			$this->MaintainVariable("detailsIsDocked", "In der Ladestation", 0, "", 120, $keep);
			$this->MaintainVariable("detailsIsScheduleEnabled", "Zeitplan aktiviert", 0, "", 130, $keep);
			$this->MaintainVariable("detailsDockHasBeenSeen", "Dockingstation gesichtet", 0, "", 140, $keep);
			$this->MaintainVariable("detailsCharge", "Ladezustand", 1, "VR200.Charge", 150, $keep);
			$this->MaintainVariable("metaModelName", "Modelname", 3, "", 160, $keep);
			$this->MaintainVariable("metaFirmware", "Firmware", 3, "", 170, $keep);

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

		SetValue($this->GetIDForIdent("version"), $robotState['version']);
		SetValue($this->GetIDForIdent("reqId"), $robotState['reqId']);
		SetValue($this->GetIDForIdent("error"), $robotState['error']);
		SetValue($this->GetIDForIdent("state"), $robotState['state']);
		SetValue($this->GetIDForIdent("action"), $robotState['action']);
		SetValue($this->GetIDForIdent("cleaningCategory"), $robotState['cleaning']['category']);
		SetValue($this->GetIDForIdent("cleaningMode"), $robotState['cleaning']['mode']);
		SetValue($this->GetIDForIdent("cleaningModifier"), $robotState['cleaning']['modifier']);
		SetValue($this->GetIDForIdent("cleaningSpotWidth"), $robotState['cleaning']['spotWidth']);
		SetValue($this->GetIDForIdent("cleaningSpotHeight"), $robotState['cleaning']['spotHeight']);
		SetValue($this->GetIDForIdent("detailsIsCharging"), $robotState['details']['isCharging']);
		SetValue($this->GetIDForIdent("detailsIsDocked"), $robotState['details']['isDocked']);
		SetValue($this->GetIDForIdent("detailsIsScheduleEnabled"), $robotState['details']['isScheduleEnabled']);
		SetValue($this->GetIDForIdent("detailsDockHasBeenSeen"), $robotState['details']['dockHasBeenSeen']);
		SetValue($this->GetIDForIdent("detailsCharge"), $robotState['details']['charge']);
		SetValue($this->GetIDForIdent("metaModelName"), $robotState['meta']['modelName']);
		SetValue($this->GetIDForIdent("metaFirmware"), $robotState['meta']['firmware']);

	//	if ($this->ReadPropertyInteger("state") == 1)
	//		$this->SetTimerInterval("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKoboldCharging")*60*1000);
	//	else
	//		$this->SetTimerInterval("UpdateKoboldData", $this->ReadPropertyInteger("UpdateKoboldWorking")*60*1000);
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

	//Variablenprofil für die Battery erstellen
	private function CreateVarProfileVR200IsCharging() {
			if (!IPS_VariableProfileExists("VR200.isCharging")) {
				IPS_CreateVariableProfile("VR200.isCharging", 0);
				IPS_SetVariableProfileText("VR200.isCharging", "", "");
				IPS_SetVariableProfileAssociation("VR200.isCharging", 0, "entlädt", "", 0xFFFF00);
				IPS_SetVariableProfileAssociation("VR200.isCharging", 1, "lädt", "", 0x66CC33);
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
	private function CreateVarProfileVR200Status() {
			if (!IPS_VariableProfileExists("VR200.Status")) {
				IPS_CreateVariableProfile("VR200.Status", 1);
				IPS_SetVariableProfileText("VR200.Status", "", "");
				IPS_SetVariableProfileAssociation("VR200.Status", 1, "angehalten", "", 0xFFFF00);
				IPS_SetVariableProfileAssociation("VR200.Status", 2, "reinigt", "", 0xFFFF00);
			 }
	}

	private function CreateVarProfileVR200Mode() {
				if (!IPS_VariableProfileExists("VR200.Mode")) {
					IPS_CreateVariableProfile("VR200.Mode", 1);
					IPS_SetVariableProfileText("VR200.Mode", "", "");
					IPS_SetVariableProfileAssociation("VR200.Mode", 1, "normal", "", 0xFFFF00);
					IPS_SetVariableProfileAssociation("VR200.Mode", 2, "eco", "", 0xFFFF00);
				 }
	}




	public function getState() {
		return $this->doAction("getRobotState");
	}
	public function startCleaning() {
		$params = array("category" => 2, "mode" => 2, "modifier" => 2);
		SetValue($this->GetIDForIdent("lastCleaning"), time());
		return $this->doAction("startCleaning", $params);
	}

	public function startEcoCleaning() {
		$params = array("category" => 2, "mode" => 1, "modifier" => 2);
		SetValue($this->GetIDForIdent("lastCleaning"), time());
		return $this->doAction("startCleaning", $params);
	}
	public function pauseCleaning() {
		return $this->doAction("pauseCleaning");
	}

		public function resumeCleaning() {
			return $this->doAction("resumeCleaning");
		}
		public function stopCleaning() {
			return $this->doAction("stopCleaning");
		}
		public function sendToBase() {
			return $this->doAction("goToBase");
		}
		public function enableSchedule() {
			return $this->doAction("enableSchedule");
		}
		public function disableSchedule() {
			return $this->doAction("disableSchedule");
		}
		public function getSchedule() {
			return $this->doAction("getSchedule");
		}
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

 // Fehlermeldungen
 //
 // ui_error_dust_bin_full
 // ui_error_dust_bin_emptied
 // ui_alert_invalid
 //