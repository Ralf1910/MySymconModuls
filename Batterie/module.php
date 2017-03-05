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
		$this->RegisterPropertyInteger("Archiv",IPS_GetInstanceIDByName("Archiv", 0 ));
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
		$this->RegisterPropertyInteger("Kapazitaet", 7000);
		$this->RegisterPropertyInteger("MaxLadeleistung", 2000);




		// Variablen anlegen
		$this->RegisterVariableFloat("fuellstand", "Batterie - Füllstand", "~Electricity", 10);
		$this->RegisterVariableInteger("fuellstandProzent", "Batterie - Füllstand Prozent", "Integer.Prozent", 20);
		$this->RegisterVariableFloat("zyklen", "Batterie - Zyklen", "", 30);

		$this->RegisterVariableInteger("aktuelleLadeleistung", "Power - Ladeleistung", "Power.Watt", 110);
		$this->RegisterVariableInteger("aktuelleEinspeisung", "Power - Einspeisung", "Power.Watt", 120);
		$this->RegisterVariableInteger("aktuelleEigennutzung", "Power - Eigennutzung", "Power.Watt", 130);
		$this->RegisterVariableInteger("aktuellerNetzbezug", "Power - Netzbezug", "Power.Watt", 140);

		$this->RegisterVariableFloat("eingespeisteEnergie", "Energie - eingespeist", "~Electricity", 210);
		$this->RegisterVariableFloat("selbstverbrauchteEnergie", "Energie - selbstverbraucht", "~Electricity", 220);
		$this->RegisterVariableFloat("bezogeneEnergie", "Energie - bezogen", "~Electricity", 230);
		$this->RegisterVariableFloat("gespeicherteEnergie", "Energie - gespeichert", "~Electricity", 240);

		$this->RegisterVariableFloat("EVGV", "Eigenverbrauch / Gesamtverbrauch", "Float.Prozent", 310);
		$this->RegisterVariableFloat("EVGP", "Eigenverbrauch / Gesamtproduktion", "Float.Prozent", 320);

		$this->RegisterVariableInteger("rollierendeZyklen", "Pro Jahr - Zyklen", "", 410);
		$this->RegisterVariableFloat("rollierendeEingespeisteEnergie", "Pro Jahr - Eingespeiste Energie", "~Electricity", 420);
		$this->RegisterVariableFloat("rollierendeSelbstvertrauchteEnergie", "Pro Jahr - Selbstverbrauchte Energie", "~Electricity", 430);
		$this->RegisterVariableFloat("rollierendeBezogeneEnergie", "Pro Jahr - Bezogene Energie", "~Electricity", 440);
		$this->RegisterVariableFloat("rollierendeGespeicherteEnergie", "Pro Jahr - Gespeicherte Energie", "~Electricity", 450);
		$this->RegisterVariableFloat("rollierendeEVGV", "Eigenverbrauch / Gesamtverbrauch", "Float.Prozent", 460);
		$this->RegisterVariableFloat("rollierendeEVGP", "Eigenverbrauch / Gesamtproduktion", "Float.Prozent", 470);



		// Updates einstellen
		$this->RegisterTimer("Update", 60*1000, 'BAT_Update($_IPS[\'TARGET\']);');

	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		//Timerzeit setzen in Minuten
		$this->SetTimerInterval("Update", 60*1000);
	}


	private function RollierenderJahreswert(Integer $VariableID) {

		//Den Datensatz von vor 365,25 Tagen abfragen (zur Berücksichtigung von Schaltjahren)
		$historischeWerte = AC_GetLoggedValues($this->ReadPropertyInteger("Archiv"), $VariableID , time()-1000*24*60*60, time()-365.25*24*60*60, 1);
		$wertVor365d = 0;
		foreach($historischeWerte as $wertVorEinemJahr) {
			$wertVor365d = $wertVorEinemJahr['Value'];
		}

		return (GetValue($VariableID) - $wertVor365d);
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

		$bezogeneEnergie			= 	getValue($this->GetIDforIdent("bezogeneEnergie"));

		$eingespeisteEnergie		=	getValue($this->GetIDforIdent("eingespeisteEnergie"));

		$gespeicherteEnergie		=	getValue($this->GetIDforIdent("gespeicherteEnergie"));

		$selbstvertrauchteEnergie	= 	getValue($this->GetIDforIdent("selbstvertrauchteEnergie"));

		$maxLadeleistung			= 	$this->ReadPropertyInteger("MaxLadeleistung");

		$kapazitaet					=	$this->ReadPropertyInteger("Kapazitaet")/1000;

		$fuellstand					=	getValue($this->GetIDforIdent("fuellstand"));





		// Berechnung, der einzelnen Werte
		if ($aktuellerVerbrauch > $aktuelleErzeugung) {
			if ($fuellstand <= 0) {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), max($aktuellerVerbrauch - $aktuelleErzeugung,0));
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), 0);
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), 0);
				setValue($this->GetIDforIdent("bezogeneEnergie"), $bezogeneEnergie + max($aktuellerVerbrauch - $aktuelleErzeugung,0)/60000);
				setValue($this->GetIDforIdent("fuellstand"), 0);
			} else {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), max($aktuellerVerbrauch - $aktuelleErzeugung - $maxLadeleistung,0));
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), max($aktuelleErzeugung - $aktuellerVerbrauch, -1*$maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), 0);
				setValue($this->GetIDforIdent("bezogeneEnergie"), $bezogeneEnergie + max($aktuellerVerbrauch - $aktuelleErzeugung - $maxLadeleistung,0)/60000);
				setValue($this->GetIDforIdent("fuellstand"), max($fuellstand + max($aktuelleErzeugung - $aktuellerVerbrauch, -1*$maxLadeleistung)/60000, 0));
			}
		} else {
			if ($fuellstand >= $kapazitaet) {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), 0);
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch,0));
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch,0)/1000/60);
				setValue($this->GetIDforIdent("fuellstand"), $kapazitaet);
			} else {
				setValue($this->GetIDforIdent("aktuellerNetzbezug"), 0);
				setValue($this->GetIDforIdent("aktuelleLadeleistung"), min($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung));
				setValue($this->GetIDforIdent("aktuelleEinspeisung"), max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung,0));
				setValue($this->GetIDforIdent("eingespeisteEnergie"), $eingespeisteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch - $maxLadeleistung,0)/60000);
				setValue($this->GetIDforIdent("fuellstand"), min($fuellstand + min($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/60000, $kapazitaet));
				setValue($this->GetIDforIdent("gespeicherteEnergie"), $gespeicherteEnergie + max($aktuelleErzeugung - $aktuellerVerbrauch, $maxLadeleistung)/60000);
			}
		}

		SetValue($this->GetIDforIdent("zyklen"), getValue($this->GetIDforIdent("gespeicherteEnergie")) / $kapazitaet);

		SetValue($this->GetIDforIdent("fuellstandProzent"), round((getValue($this->GetIDforIdent("fuellstand"))*100 / $kapazitaet)/5)*5);

		SetValue($this->GetIDforIdent("rollierendeGespeicherteEnergie"), RollierenderJahreswert($this->GetIDforIdent("gespeicherteEnergie")));

		SetValue($this->GetIDforIdent("rollierendeZyklen"), RollierenderJahreswert($this->GetIDforIdent("zyklen")));

		SetValue($this->GetIDforIdent("aktuelleEigennutzung"), min($aktuellerVerbrauch, $aktuelleErzeugung));

		SetValue($this->GetIDforIdent("selbstverbrauchteEnergie"), $selbstvertrauchteEnergie + min($aktuellerVerbrauch, $aktuelleErzeugung)/60000);

		SetValue($this->GetIDforIdent("EVGV"), ($selbstvertrauchteEnergie + $gespeicherteEnergie)*100 / ($bezogeneEnergie + $selbstvertrauchteEnergie + $gespeicherteEnergie));

		SetValue($this->GetIDforIdent("EVGP"), ($selbstvertrauchteEnergie + $gespeicherteEnergie)*100 / ($eingespeisteEnergie + $selbstvertrauchteEnergie + $gespeicherteEnergie));

	}


 }

