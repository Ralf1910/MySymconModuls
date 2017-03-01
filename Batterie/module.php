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

		// Verbraucher, Erzeuger und Batteriedaten konfigurieren
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


		// Variablen anlegen
		$this->RegisterVariableFloat("fuellstand", "Füllstand", "~Electricity", 10);
		$this->RegisterVariableFloat("aktuelleLadeleistung", "aktuelle Ladeleistung", "~Watt.14490", 20);
		$this->RegisterVariableFloat("aktuelleEinspeisung", "aktuelle Einspeisung", "~Watt.14490", 40);
		$this->RegisterVariableFloat("eingespeisteEnergie", "Eingepeiste Energie", "~Electricity", 30);
		$this->RegisterVariableFloat("aktuellerNetzbezug", "aktueller Netzbezug", "~Watt.14490", 50);
		$this->RegisterVariableFloat("bezogeneEnergie", "Bezogene Energie", "~Electricity", 60);
		$this->RegisterVariableFloat("rollierendeZyklen", "Rollierende Zyklen pro Jahr", "", 70);
		$this->RegisterVariableFloat("rollierendeGespeicherteEnergie", "Gespeicherte Energie pro Jahr", "~Electricity", 80);
		$this->RegisterVariableFloat("gespeicherteEnergie", "Gespeicherte Energie", "~Electricity", 90);
		$this->RegisterVariableFloat("zyklen", "Zyklen", "", 100);


		// Updates einstellen
		$this->RegisterTimer("Update", 60*1000, 'Batterie_Update($_IPS[\'TARGET\']);');
		//$this->RegisterTimer("UpdateJahreswert", 60*60*1000, 'Stromzaehler_UpdateJahreswert($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		//Timerzeit setzen in Minuten
		$this->SetTimerInterval("Update", 60*1000);
		// $this->SetTimerInterval("UpdateJahreswert", 60*60*1000);
	}



	public function Update() {

		// Gesamtverbrauch zusammenaddieren
		$aktuellerVerbrauch 	= 	0;
		if ($this->ReadPropertyInteger("Verbraucher1")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher1"));
		if ($this->ReadPropertyInteger("Verbraucher2")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher2"));
		if ($this->ReadPropertyInteger("Verbraucher3")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher3"));
		if ($this->ReadPropertyInteger("Verbraucher4")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher4"));
		if ($this->ReadPropertyInteger("Verbraucher5")>0) $aktuellerVerbrauch += getValue($this->ReadPropertyInteger("Verbraucher5"));

		// Gesamterzeugung zusammenaddieren
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


		// Berechnung, der einzelnen Werte
		if ($aktuellerVerbrauch > $aktuelleErzeugung) {
			if ($fuellstand <= 0) {
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
			if ($fuellstand >= $kapazität) {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), 0);
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch));
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch,0)/1000/3600);
			} else {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung,0));
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung,0)/1000/3600);
				setValue($this->GetIDforIdent("fuellstand"), min($fuellstand + max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/1000/3600, $kapazität));
				setValue($this->GetIDforIdent("gespeicherteEnergie"), $gespeicherteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/1000/3600);
			}
		}

		SetValue($this->GetIDforIdent("zyklen"), getValue($this->GetIDforIdent("gespeicherteEnergie")) / $this->ReadPropertyInteger("Kapazitaet"));

	}


 }

