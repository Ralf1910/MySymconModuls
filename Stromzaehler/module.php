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
		$this->RegisterPropertyInteger("CounterObjektID", 0);
		$this->RegisterPropertyInteger("CurrentObjektID", 0);
		$this->RegisterPropertyInteger("Zaehleroffset", 0);

		// Variablenprofile anlegen
		$this->CreateVarProfileStromzaehlerEnergy();
		$this->CreateVarProfileStromzaehlerPower();

		// Variablen anlegen
		$this->RegisterVariableFloat("aktuelleLeistung", "aktuelle Leistung", "Stromzaehler.Power", 10);
		$this->RegisterVariableFloat("zaehlerstand", "Zählerstand", "Stromzaehler.Energy", 20);
		$this->RegisterVariableFloat("heutigerVerbrauch", "Heutiger Verbrauch", "Stromzaehler.Energy", 30);
		$this->RegisterVariableFloat("yearEnergyConsumption", "Rollierender Jahreswert", "Stromzaehler.Energy", 40);

		// Updates einstellen
		$this->RegisterTimer("UpdateStromzaehler", 10*1000, 'Stromzaehler_UpdateStromzaehler($_IPS[\'TARGET\']);');
		$this->RegisterTimer("UpdateJahreswert", 60*60*1000, 'Stromzaehler_UpdateJahreswert($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		//Timerzeit setzen in Minuten
		$this->SetTimerInterval("UpdateStromzaehler", 10*1000);
		$this->SetTimerInterval("UpdateJahreswert", 60*60*1000);

		// Variablenprofile anlegen
		$this->CreateVarProfileStromzaehlerEnergy();
		$this->CreateVarProfileStromzaehlerPower();

	}



	public function UpdateStromzaehler() {

		SetValue($this->GetIDforIdent("aktuelleLeistung"), 	getValue($this->ReadPropertyInteger("CurrentObjektID")));
		SetValue($this->GetIDforIdent("zaehlerstand"), 		(getValue($this->ReadPropertyInteger("CounterObjektID"))/1000) + $this->ReadPropertyInteger("Zaehleroffset"));

	}

	public function UpdateJahreswert() {

			$archivID = IPS_GetVariableIDByName("Archiv", 0);
			$historischeWerte = AC_GetLoggedValues($archivID, $this->ReadPropertyInteger("CounterObjektID"), strtotime('today midnight') - 50000, strtotime('today midnight'), 1);
	 	    foreach($historischeWerte as $wertZumTagesbeginn) {
		    	SetValueFloat($this->ReadPropertyInteger("heutigerVerbrauch"), $this->ReadPropertyInteger("CounterObjektID") - $wertZumTagesbeginn['Value']);
		    }

		}




	//Variablenprofil für den Action erstellen
	private function CreateVarProfileStromzaehlerEnergy() {
		if (!IPS_VariableProfileExists("Stromzaehler.Energy")) {
			IPS_CreateVariableProfile("Stromzaehler.Energy", 2);
			IPS_SetVariableProfileText("Stromzaehler.Energy", "", " kWh");
			IPS_SetVariableProfileDigits("Stromzaehler.Energy", 2);
		 }
	}

	//Variablenprofil für die Battery erstellen
	private function CreateVarProfileStromzaehlerPower() {
			if (!IPS_VariableProfileExists("Stromzaehler.Power")) {
				IPS_CreateVariableProfile("Stromzaehler.Power", 1);
				IPS_SetVariableProfileText("Stromzaehler.Power", "", " W");
			 }
	}










 }

