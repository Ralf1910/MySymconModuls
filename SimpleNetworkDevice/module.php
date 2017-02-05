<?
//  Modul zur Berechnung und Anzeige der Abholtermine
//
//	Version 0.8
//
// ************************************************************

class SimpleNetworkDevice extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();

		// IP Adresse
		$this->RegisterPropertyString("IPAdresse", "");
		$this->RegisterPropertyInteger("UpdateKobold", 1);

		// Updates einstellen
		$this->RegisterTimer("Update", $this->ReadPropertyInteger("Update")*60*1000, 'SND_Update($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		$this->SetTimerInterval("Update", $this->ReadPropertyInteger("Update")*60*1000);


		// Variablen aktualisieren
		$this->MaintainVariable("Power", "Power", 0, "", 10, true);

		$this->Update();

		//Instanz ist aktiv
		$this->SetStatus(102);
	}


	// Aktualisierung der Variablen
	public function Update() {

		$power = Sys_Ping($this->ReadPropertyString("IPAdresse"), 1000 );
		SetValue($this->GetIDForIdent("Power"), $power);

	}


 }

