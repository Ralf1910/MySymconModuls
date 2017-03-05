<?
//  Modul für Utitilties
//
//	Version 0.9
//
// ************************************************************

class Utilities extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();
		$this->RegisterPropertyInteger("Archiv", 27366);


	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();


	}



	public function RollierenderJahreswert(Integer $VariableID) {

		//Den Datensatz von vor 365,25 Tagen abfragen (zur Berücksichtigung von Schaltjahren)
		$historischeWerte = AC_GetLoggedValues($this->ReadPropertyInteger("Archiv"), $VariableID , time()-1000*24*60*60, time()-365.25*24*60*60, 1);
		$wertVor365d = 0;
		foreach($historischeWerte as $wertVorEinemJahr) {
			$wertVor365d = $wertVorEinemJahr['Value'];
		}

		return (GetValue($VariableID) - $wertVor365d);
	}

}

