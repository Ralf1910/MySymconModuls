<?
//  Modul zur Berechnung und Anzeige der Abholtermine
//
//	Version 0.1
//
// ************************************************************

class BSR extends IPSModule {


	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();

		// Updates einstellen
		$this->RegisterTimer("UpdateAbholtermine", 60*60*1000, 'BSR_UpdateAbholtermine($_IPS[\'TARGET\']);');
	}


	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		$this->SetTimerInterval("UpdateAbholtermine", 60*60*1000);


		// Variablen aktualisieren
		$this->MaintainVariable("nextBSRDate", "nächster Abholtermin BSR", 1, "~UnixTimestampDate", 10, true);
		$this->MaintainVariable("BSRAbholungInTagen", "BSR Abholung in Tagen", 3, "", 20, true);
		$this->MaintainVariable("nextGruenerPunktDate", "nächster Abholtermin Grüner Punkt", 1, "~UnixTimestampDate", 30, true);
		$this->MaintainVariable("GruenerPunktAbholungInTagen", "Grüner Punkt Abholung in Tagen", 3, "", 40, true);

		//Instanz ist aktiv
		$this->SetStatus(102);
	}


	public function UpdateAbholtermine() {
		$AbholungHausmuell 	= array("11.01.2017", "25.01.2017", "08.02.2017", "22.02.2017", "08.03.2017", "22.03.2017", "05.04.2017", "20.04.2017", "04.05.2017", "17.05.2017", "31.05.2017", "14.06.2017", "28.06.2017",
									"12.07.2017", "26.07.2017", "09.08.2017", "23.08.2017", "06.09.2017", "20.09.2017", "05.10.2017", "18.10.2017", "02.11.2017", "15.11.2017", "29.11.2017", "13.12.2017", "28.12.2017");
		$AbholungWertstoffe	= array("12.01.2017", "26.01.2017", "09.02.2017", "23.02.2017", "09.03.2017", "23.03.2017", "06.04.2017", "21.04.2017", "05.05.2017", "18.05.2017", "01.06.2017", "15.06.2017", "29.06.2017",
									"13.07.2017", "27.07.2017", "10.08.2017", "24.08.2017", "07.09.2017", "21.09.2017", "06.10.2017", "19.10.2017", "03.11.2017", "16.11.2017", "30.11.2017", "14.12.2017", "29.12.2017");

		$heute 				= date("d.m.Y", time());
		$morgen 			= date("d.m.Y", time() + 3600*24);
 		$uebermorgen 		= date("d.m.Y", time() + 3600*24*2);

		foreach ($AbholungHausmuell as &$HausmuellTermin) {
			$dateTimestampNow	= time();
			$dateTimestampHausmuellTermin	= strtotime($HausmuellTermin);

			if ($dateTimestampHausmuellTermin > $dateTimestampNow) {
				SetValue($this->GetIDForIdent("nextBSRDate"), $HausmuellTermin);
				return;
			}
		}
	}


 }

