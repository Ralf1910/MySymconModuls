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
		$this->RegisterPropertyFloat("Zaehleroffset", 0);

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

		// Objekt IDs
		//if(IPS_VariableExists($this->ReadPropertyInteger("CounterObjektID")))	SetValue($this->GetIDForIdent("LastCounterObjektID"), GetValue($this->ReadPropertyInteger("CounterObjektID")));
		//if(IPS_VariableExists($this->ReadPropertyInteger("CurrentObjektID")))	SetValue($this->GetIDForIdent("LastCurrentObjektID"), GetValue($this->ReadPropertyInteger("CurrentObjektID")));

		//Always hide Lastvariable
		//IPS_SetHidden($this->GetIDForIdent("LastCounterObjektID"), true);

		// Variablenprofile anlegen
		$this->CreateVarProfileStromzaehlerEnergy();
		$this->CreateVarProfileStromzaehlerPower();

	}



	public function UpdateStromzaehler() {

		SetValue($this->GetIDforIdent("aktuelleLeistung"), 	getValue($this->ReadPropertyInteger("CurrentObjektID")));
		SetValue($this->GetIDforIdent("zaehlerstand"), 		getValue($this->ReadPropertyInteger("CounterObjektID"))/1000 + $this->ReadPropertyFloat("zaehleroffset"));



	// $ret = SetValueFloat(50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/  , Round(GetValue(28348 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Counter Persistent]*/)*1.00/1000 + 133437 /* 14.11.2015 */,4));
 	// if ($ret == false) { echo "Fehler beim setzen des Haushalt Energieverbrauchs"; }

 	// $ret = SetValueFloat(24149 /*[Werte & Stati\Strom\Haushalt\aktuelle Leistung]*/ , Round(GetValue (53144 /*[Geräte\KG\Waschkeller\EKM-868 128:1 (Haushalt)\Current]*/ )));
	// if ($ret == false) { echo "Fehler beim setzen der aktuellen Haushaltsleistung"; }

 	//	$historischeWerte = AC_GetLoggedValues(27366 /*[Archiv]*/ , 50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/ , strtotime('today midnight') - 50000, strtotime('today midnight'), 1);
 	//	foreach($historischeWerte as $wertZumTagesbeginn) {
	//			SetValueFloat(13989 /*[Werte & Stati\Strom\Haushalt\Heute Verbraucht]*/, GetValueFloat(50657 /*[Werte & Stati\Strom\Haushalt\Verbrauchszähler]*/) - $wertZumTagesbeginn['Value']);
	//	}


		// Daten aktualisieren

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

