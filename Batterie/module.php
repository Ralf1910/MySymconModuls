<?
//  Modul zur Steuerung des Vorwerk Kobold VR200
//
//	Version 0.9
//
// ************************************************************

class Batterie extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		$this->RegisterPropertyInteger("Verbraucher1", 0);
		$this->RegisterPropertyInteger("Verbraucher2", 0);
		$this->RegisterPropertyInteger("Verbraucher3", 0);
		$this->RegisterPropertyInteger("Verbraucher4", 0);
		$this->RegisterPropertyInteger("Verbraucher5", 0);
		$this->RegisterPropertyInteger("Erzeuger1", 0);
		$this->RegisterPropertyInteger("Erzeuger2", 0);
		$this->RegisterPropertyInteger("Erzeuger3", 0);
		$this->RegisterPropertyInteger("Erzeuger4", 0);
		$this->RegisterPropertyInteger("Erzeuger5", 0);
		$this->RegisterPropertyInteger("Kapazitaet", 0);
		$this->RegisterPropertyInteger("MaxLadeleistung", 0);

		// Variablenprofile anlegen
		//$this->CreateVarProfileStromzaehlerEnergy();
		//$this->CreateVarProfileStromzaehlerPower();

		// Variablen anlegen
		$this->RegisterVariableFloat("fuellstand", "Füllstand", "", 10);
		$this->RegisterVariableFloat("gespeicherteEnergie", "Gespeicherte Energie", "", 20);
		$this->RegisterVariableFloat("zyklen", "Zyklen", "", 30);
		$this->RegisterVariableFloat("rollierendeZyklen", "Rollierende Zyklen pro Jahr", "", 40);
		$this->RegisterVariableFloat("rollierendeGespeicherteEnergie", "Gespeicherte Energie pro Jahr", "", 50);
		$this->RegisterVariableFloat("eingespeisteEngerie", "Eingepeiste Energie", "", 50);
		$this->RegisterVariableFloat("bezogeneEngerie", "Bezogene Energie", "", 50);
		$this->RegisterVariableFloat("aktuelleLadeleistung", "aktuelle Ladeleistung", "", 50);
		$this->RegisterVariableFloat("aktuelleEinspeisung", "aktuelle Einspeisung", "", 50);
		$this->RegisterVariableFloat("aktuellerNetzbezug", "aktueller Netzbezug", "", 50);

		// Updates einstellen
		$this->RegisterTimer("update", 10*1000, 'Batterie_Update($_IPS[\'TARGET\']);');
		//$this->RegisterTimer("UpdateJahreswert", 60*60*1000, 'Stromzaehler_UpdateJahreswert($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		//Timerzeit setzen in Minuten
		$this->SetTimerInterval("Update", 10*1000);
		// $this->SetTimerInterval("UpdateJahreswert", 60*60*1000);

		// Variablenprofile anlegen
		//$this->CreateVarProfileStromzaehlerEnergy();
		//$this->CreateVarProfileStromzaehlerPower();

	}



	public function Update() {

		$aktuellerVerbrauch 	= 	0;
		if ($this->ReadPropertyInteger("Verbraucher1")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher1"));
		if ($this->ReadPropertyInteger("Verbraucher2")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher2"));
		if ($this->ReadPropertyInteger("Verbraucher3")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher3"));
		if ($this->ReadPropertyInteger("Verbraucher4")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher4"));
		if ($this->ReadPropertyInteger("Verbraucher5")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher5"));

		$aktuelleErzeugung		=	0;
		if ($this->ReadPropertyInteger("Erzeuger1")>0) $aktuelleErzeugung += getValue($this->ReadPropertyInteger("Erzeuger1"));
		if ($this->ReadPropertyInteger("Erzeuger2")>0) $aktuelleErzeugung += getValue($this->ReadPropertyInteger("Erzeuger2"));
		if ($this->ReadPropertyInteger("Erzeuger3")>0) $aktuelleErzeugung += getValue($this->ReadPropertyInteger("Erzeuger3"));
		if ($this->ReadPropertyInteger("Erzeuger4")>0) $aktuelleErzeugung += getValue($this->ReadPropertyInteger("Erzeuger4"));
		if ($this->ReadPropertyInteger("Erzeuger5")>0) $aktuelleErzeugung += getValue($this->ReadPropertyInteger("Erzeuger5"));

		$bezogeneEnergie		= 	getValue($this->GetIDforIdent("bezogeneEnergie"));

		$eingespeisteEnergie	=	getValue($this->GetIDforIdent("eingespeisteEnergie"));

		$gespeicherteEnergie	=	getValue($this->GetIDforIdent("gespeicherteEnergie"));

		$maxLadeleistung		= 	$this->ReadPropertyInteger("MaxLadeleistung");

		$kapazitaet				=	$this->ReadPropertyInteger("Kapazitaet");

		$fuellstand				=	getValue($this->GetIDforIdent("fuellstand"));


		if ($aktuellerVerbrauch > $aktuelleErzeugung) {
			if ($fuellstand == 0) {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), max($aktuellerVerbrauch - $aktuelleErzeugung,0));
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), 0);
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), 0);
				setValue($this->GetIDforIdent("bezogeneEnergie"), $bezogeneEnergie + max($aktuellerVerbrauch - $aktuelleErzeugung,0)/1000/3600);
			} else {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), max($aktuellerVerbrauch - $aktuelleErzeugung - $maxLadeleistung,0));
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), max($aktuelleErzeugung - $aktuellerVerbrauch, -1*$maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), 0);
				setValue($this->GetIDforIdent("bezogeneEnergie"), $bezogeneEnergie + max($aktuellerVerbrauch - $aktuelleErzeugung - $maxLadeleistung,0)/1000/3600);
				setValue($this->GetIDforIdent("fuellstand"), max($fuellstand - max($aktuelleErzeugung - $aktuellerVerbrauch, -1*$maxLadeleistung)/1000/3600, 0));
			}
		} else {
			if ($fuellstand == $kapazität) {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch);
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch,0)/1000/3600);
			} else {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung));
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung,0)/1000/3600);
				setValue($this->GetIDforIdent("fuellstand"), min($fuellstand + max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/1000/3600, $kapazität));
				setValue($this->GetIDforIdent("gespeicherteEnergie"), max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/1000/3600);
			}
		}





		SetValue($this->GetIDforIdent("zyklen"), getValue($this->GetIDforIdent("gespeicherteEnergie")) / $this->ReadPropertyInteger("kapazität"));


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

