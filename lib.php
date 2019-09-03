<?php

class connection {

	function connect($server, $database, $user, $pass, $sql){

		$data = new ArrayObject();

		$connectionInfo = array("Database"=>"$database", "UID"=>"$user", "PWD"=>"$pass", "CharacterSet" => "UTF-8");
		$con = sqlsrv_connect($server, $connectionInfo);
		$stmt = sqlsrv_query($con, $sql);
		
		if($stmt === false) {
			die(print_r(sqlsrv_errors(), true));
		}

		while($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
			$data->append($row);
		}
		
		sqlsrv_free_stmt($stmt);

		sqlsrv_close($con);

		return $data;

	}

}

class logs {

	function saveLog($type, $data){

		$fp = fopen("log/" . $type . " - " . date("His") . ".txt", 'w');
		fwrite($fp, $data);
		fclose($fp);

	}

}

class dao {

	private $server;
	private $database;
	private $user;
	private $pass;

	function __construct($server, $database, $user, $pass) {
		$this->server = $server;
		$this->database = $database;
		$this->user = $user;
		$this->pass = $pass;
	}

	function checkConnection(){
		$con = new connection();
		$sql = "SELECT TOP 1 * FROM CADASTRO_PACIENTE;";
		$data = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
		if(isset($data[0]["CODPAC"])){
			return true;
		} else {
			return false;
		}
	}

	function readTextQuery($table, $field){

		$con = new connection();

		$sql = "SELECT DISTINCT $field FROM $table ORDER BY $field ASC";
		$data = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
		
		return $data;

	}

	function updateTextQuery($oldValue, $newValue, $table, $field){

		$con = new connection();
		
		$oldValue = str_replace(' ', '', $oldValue);
		$sql = "UPDATE $table SET $field = '$newValue' WHERE REPLACE($field, ' ', '') = '$oldValue'";
		$data = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		return $data;

	}

	function updateRgQuery(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();
		$logs = new logs();
		$aux = "";
		$x = 0;
		$counter = 0;
		$newValue = "";
			
		$sql = "SELECT DISTINCT REGPAC FROM CADASTRO_PACIENTE;";
		$oldReg = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		foreach($oldReg as $val){

			$x = 0;
			$counter = 0;
			$newValue = "";
			
			$breaks = array("'", '"', ",", ".", "-");

			$aux = str_replace($breaks,'',$val['REGPAC']);

			if(strlen($aux) > 6){
				
				for($i = 0; strlen($aux) > $i; $i++){
					$x++;
					if(is_numeric(substr($aux, $i, $x))){
						$counter++;
					} else {
						$counter = 0;
					}
					if($counter > 2){
						break;
					}
				}
			}

			if($x < 4){
				$newValue = preg_replace("/[^0-9,.]/", "", substr($aux, 0, strlen($aux)));
			} else {
				$newValue = preg_replace("/[^0-9,.]/", "", substr($aux, $x-3, strlen($aux)));
			}

			$data->append(array("old"=>$val["REGPAC"], "new"=>$newValue));

		}

		$logs->saveLog("RG", json_encode($data));

		$sql = "";
		$index = 0;

		foreach($data as $val){
			$newValue = $val['new'];
			$oldValue = $val['old'];
			if($newValue != $oldValue && $newValue != ""){
				$sql = $sql . "UPDATE CADASTRO_PACIENTE SET REGPAC = '$newValue' WHERE REGPAC = '$oldValue'; ";
			}
			$index++;
			if($index > 300){
				$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
				$sql = "";
				$index = 0;
			}
		}

		$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		return "ok";

	}

	function updateCpfQuery(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();
		$logs = new logs();

		$sql = "SELECT DISTINCT CPFPAC FROM CADASTRO_PACIENTE;";
		$oldReg = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		foreach($oldReg as $val){

			$newValue = str_replace('/','',str_replace(',','',str_replace('.','',str_replace('-','',$val['CPFPAC']))));
			$data->append(array("old"=>$val["CPFPAC"], "new"=>$newValue));

		}

		$logs->saveLog("CPF", json_encode($data));

		$sql = "";
		$index = 0;

		foreach($data as $val){
			$newValue = $val['new'];
			$oldValue = $val['old'];
			if($newValue != $oldValue && strlen($newValue) > 0){
				$sql = $sql . "UPDATE CADASTRO_PACIENTE SET CPFPAC = '$newValue' WHERE CPFPAC = '$oldValue'; ";
			}
			$index++;
			if($index > 300){
				$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
				$sql = "";
				$index = 0;
			}
		}

		$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

			return "ok";

	}
  
	function logUserId(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();
		$logs = new logs();

		$sql = "SELECT CODPAC, COUNT(*) AS QNT FROM CADASTRO_PACIENTE WHERE LEN(CODPAC) > 0 GROUP BY CODPAC HAVING COUNT(*) > 1; ";
		$values = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
		foreach($values as $val){
			$data->append(array("CODPAC"=>$val['CODPAC'], "QUANTIDADE"=>$val['QNT']));
		}

		$logs->saveLog("CODPAC DUPLICADO", json_encode($data));

		return "ok";

	}
  
	function logExamId(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();
		$logs = new logs();

		$sql = "SELECT CODPRO, COUNT(*) AS QNT FROM PROTUARIO_PACIENTE WHERE LEN(CODPRO) > 0 GROUP BY CODPRO HAVING COUNT(*) > 1; ";
		$values = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
		foreach($values as $val){
			$data->append(array("CODPRO"=>$val['CODPRO'], "QUANTIDADE"=>$val['QNT']));
		}

		$logs->saveLog("CODPRO DUPLICADO", json_encode($data));

		return "ok";

	}
  
	function logSheetId(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();
		$logs = new logs();

		$sql = "SELECT CodLam, COUNT(*) AS QNT FROM ID_LAMINA WHERE LEN(CodLam) > 0 GROUP BY CodLam HAVING COUNT(*) > 1; ";
		$values = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
		foreach($values as $val){
			$data->append(array("CODLAM"=>$val['CodLam'], "QUANTIDADE"=>$val['QNT']));
		}

		$logs->saveLog("CODLAM DUPLICADO", json_encode($data));

		return "ok";

	}
  
	// function fixUserId($new, $old){

	// 	$con = new connection();
	// 	$data = new ArrayObject();
	// 	$logs = new logs();
	// 	$sql = "";
		
	// 	$sql = "SELECT * FROM CADASTRO_PACIENTE WHERE CODPAC = '$old'; ";
	// 	$data->append($con->connect($this->server, $this->database, $this->user, $this->pass, $sql));
	// 	$logs->saveLog("CODPAC DESCARTADO", json_encode($data));

	// 	$sql = "UPDATE BIOPSIA SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE BIOPSIA_RESULTADO SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE EVOLUCAO SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE EVOLUCAO_RESULTADO SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_COLO_VAGINA SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_COLPOCITOLOGIA SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_MAMA SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "UPDATE PROTUARIO_PACIENTE SET CODPAC = '$new' WHERE CODPAC = '$old'; ";
	// 	$sql = $sql . "DELETE FROM CADASTRO_PACIENTE WHERE CODPAC = '$old';";
	// 	$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
	// 	return "ok";

	// }

	// function fixRecordId($old){

	// 	$con = new connection();
	// 	$data = new ArrayObject();
	// 	$logs = new logs();
	// 	$sql = "";

	// 	$sql = "SELECT * FROM PROTUARIO_PACIENTE WHERE CODPRO = '$old'; ";
	// 	$data->append($con->connect($this->server, $this->database, $this->user, $this->pass, $sql));
	// 	$logs->saveLog("CODPRO DESCARTADO", json_encode($data));

	// 	$sql = "SELECT TOP 1 CODPRO FROM PROTUARIO_PACIENTE ORDER BY CODPRO DESC";
	// 	$new = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql)[0]["CODPRO"] + 1;

	// 	$sql = "UPDATE PROTUARIO_PACIENTE SET CODPRO = '$new' WHERE CODPRO = '$old'; ";
	// 	$sql = $sql . "UPDATE ID_LAMINA SET CODPRO = '$new' WHERE CODPRO = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_COLO_VAGINA SET CODPRO = '$new' WHERE CODPRO = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_COLPOCITOLOGIA SET CODPRO = '$new' WHERE CODPRO = '$old'; ";
	// 	$sql = $sql . "UPDATE EXAME_MAMA SET CODPRO = '$new' WHERE CODPRO = '$old'; ";
	// 	$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
	// 	return "ok";

	// }

	function databaseStructure($val){

		$con = new connection();

		$cont = array(
			"CREATE TABLE CIDADE (CODCID int IDENTITY(1,1) PRIMARY KEY,NOMCID varchar(50)); ",
			"CREATE TABLE BAIRROS (CODBAI int IDENTITY(1,1) PRIMARY KEY,CODCID int null,NOMBAI varchar(50)); ",
			"CREATE TABLE MOVIMENTO_FINANCEIRO (COD_MOV int NOT NULL PRIMARY KEY,COD_LAN int,STA_LAN varchar(255),USUALT varchar(255),DATALT varchar(255),TIPMOV varchar(255),VLRLAN varchar(255),VLRDES varchar(255),VLRMUL varchar(255),VLRJUR varchar(255),VLRTOT varchar(255),SEQMOV varchar(255),TIPLAN varchar(255),SEQTIT varchar(255)); ",
			"CREATE TABLE AGENDA (CODAGE int NOT NULL PRIMARY KEY,DATINI varchar(255),DATFIM varchar(255),CODPAC varchar(255),NOMPAC varchar(255),CONPAC varchar(255),STATUS varchar(255),TIPAGE varchar(255)); ",
			"CREATE TABLE ESTOQUE (CODEMP int NOT NULL,CODFIL int NOT NULL,CODDEP varchar(50) NOT NULL,CODPRO varchar(50) NOT NULL,CODEST INT NOT NULL IDENTITY(1,1),DESPRO varchar(250) NULL,QTDEST decimal(18, 5) NULL,UNIMED varchar(50) NULL,VLRCOM decimal(18, 2) NULL,VLRVEN decimal(18, 2) NULL,VLRTCO decimal(18, 2) NULL,VLRTVE decimal(18, 2) NULL,CODAGR int NULL,SITPRO varchar(1) NOT NULL, PRIMARY KEY(CODEMP,CODFIL,CODDEP,CODPRO,CODEST)); ",
			"CREATE TABLE ESTOQUE_MOVIMENTO (CODEMP int NOT NULL,CODFIL int NOT NULL,CODDEP varchar(50) NOT NULL,CODPRO varchar(50) NOT NULL,CODEST int NOT NULL ,SEQMOV int IDENTITY(1,1) NOT NULL,TIPMOV varchar(1) NULL,QTDMOV decimal(18, 5) NULL,QTDANT decimal(18, 5) NULL,QTDEST decimal(18, 5) NULL,VLRMED decimal(18, 2) NULL,VLRMOV decimal(18, 2) NULL,PERIPI decimal(18, 2) NULL,PERICM decimal(18, 2) NULL,PERISS decimal(18, 2) NULL,DATREG datetime NULL,USUREG varchar(50) NULL,OBSMOV varchar(250) NULL,NUMDOC int NULL,EMIDOC datetime NULL,CHADOC varchar(150) NULL,CODSNF varchar(50) NULL,CODFOR int NULL,CODCLI int NULL, PRIMARY KEY(CODEMP, CODFIL, CODDEP, CODPRO, CODEST)); ",
			"CREATE TABLE AGRUPAMENTO (CODAGR int IDENTITY(1,1) NOT NULL PRIMARY KEY,DESAGR varchar(50) NULL,APLAGR varchar(50) NULL); ",
			"CREATE TABLE CADASTRO_FORNECEDORES (CodFor int NOT NULL PRIMARY KEY,NomFor varchar(250) NULL,ApeFor varchar(250) NULL,SenFor varchar(250) NULL,TipFor varchar(25) NULL,CodRam varchar(25) NULL,InsEst varchar(250) NULL,InsMun varchar(250) NULL,CgcCpf varchar(250) NULL,EndFor varchar(250) NULL,CplFor varchar(250) NULL,NumEnd int NULL,BaiFor varchar(250) NULL,SigUfs varchar(250) NULL,FonFor varchar(25) NULL,FonCl2 varchar(25) NULL,FonCl3 varchar(25) NULL,EmaFor varchar(250) NULL,DatHor datetime NULL,UsuCad varchar(250) NULL,SitFor varchar(25) NULL,ObsFor varchar(250) NULL,CepFor varchar(50) NULL); ",
			"CREATE TABLE FORMA_PAGAMENTO (CODFPG int PRIMARY KEY IDENTITY(1,1) NOT NULL, DESFPG varchar(250) NULL); ",
			"CREATE TABLE DEPOSITO (CODEMP int NOT NULL, CODFIL int NOT NULL, CODDEP varchar(250) NOT NULL, DESDEP varchar(250) NOT NULL, SITDEP varchar(50) NULL, DATCAD datetime NULL, HORCAD varchar(250) NULL, USUCAD varchar(250) NULL, DATALT datetime NULL, HORALT varchar(250) NULL, USUALT varchar(250) NULL, PRIMARY KEY(CODEMP, CODFIL, CODDEP)); ",
			"CREATE TABLE BIOPSIA_RESULTADO (CODEMP int NOT NULL, CODFIL int NOT NULL, CODPAC int NOT NULL, CODPRO int NOT NULL, CODBIO int NOT NULL,CODRES int IDENTITY(1,1) NOT NULL, MEDSOL text NULL, DADCLI text NULL, TXTMAT text NULL, TXTMAC text NULL, TXTCOL text NULL, TXTCON text NULL, USUREG varchar(250) NOT NULL, CRMREG varchar(250) NOT NULL, DATREG varchar(250) NOT NULL, PRIMARY KEY(CODEMP, CODFIL, CODPAC, CODPRO, CODBIO, CODRES)); ",
			"CREATE TABLE BIOPSIA (CODEMP int NOT NULL , CODFIL int NOT NULL , CODPAC int NOT NULL , CODPRO int NOT NULL , CODBIO int IDENTITY(1,1) NOT NULL , COLNOR int NULL, COLANO int NULL, ALTMEN int NULL, ALTMAI int NULL, SUGCAN int NULL, COLMIS int NULL, ZTTI1 int NULL, ZTTI2 int NULL, JECATE int NULL, JECALE int NULL, COLINS int NULL, ZTTI3 int NULL, COLATR int NULL, PROBIO int NULL, PROEXE int NULL, VERTRA int NULL, POSBIO int NULL, PROCON int NULL, PROOUT int NULL, TXTOUT varchar(50) NULL, TIPBIO int NULL, TIPEXE int NULL, TIPCON int NULL, TIPHIS int NULL, TIPUNI int NULL, TIPOUT int NULL, TXTOUT2 varchar(250) NULL, CNSLAB varchar(250) NULL, NOMLAB varchar(250) NULL, NUMEXA varchar(250) NULL, DATREC varchar(250) NULL, BIOMAC varchar(250) NULL, TIPBNF int NULL, TIPPTO int NULL, LOCEDO int NULL, LOCEND int NULL, LOCJEC int NULL, ADESAT int NULL, ADEINS int NULL, ADEESP int NULL, MICMET int NULL, MICCCI int NULL, MICPOL int NULL, MICHPV int NULL, LCNPN1 int NULL, LCNPN2 int NULL, LCNPN3 int NULL, LCNPN4 int NULL, LCNPN5 int NULL, LCNPN6 int NULL, LCNPN7 int NULL, LCNPN8 int NULL, LCNPN9 int NULL, LCNTXT varchar(50) NULL, GRANAO int NULL, GRANAC int NULL, GRABEM int NULL, GRAPOU int NULL, GRAIND int NULL, GRAMOD int NULL, VASSIM int NULL, VASNAO int NULL, PARSIM int NULL, PARNAO int NULL, PTASIM int NULL, PTANAO int NULL, CORSIM int NULL, CORNAO int NULL, VAGSIM int NULL, VAGNAO int NULL, MARLIV int NULL, MARCOM int NULL, MARIMP int NULL, DIADES varchar(50) NULL, DATREG varchar(50) NULL, USUREG varchar(50) NULL, USUCRM varchar(50) NULL, INFADI varchar(50) NULL, DATCOL varchar(50) NULL, RESCOL varchar(50) NULL, TXTBNF varchar(50) NULL, TXTTA1 varchar(50) NULL, TXTTA2 varchar(50) NULL, TXTDMR varchar(50) NULL, TXTPDI varchar(50) NULL, TXTFRA varchar(50) NULL, TXTBLO varchar(50) NULL, TXTDMP varchar(50) NULL, TXTADM varchar(50) NULL, PRIMARY KEY(CODEMP,CODFIL,CODPAC,CODPRO,CODBIO)); ",
			"ALTER TABLE CADASTRO_CLIENTE ALTER COLUMN InsEst varchar(250); ALTER TABLE CADASTRO_CLIENTE ALTER COLUMN InsMun varchar(250); ALTER TABLE CADASTRO_CLIENTE ALTER COLUMN CgcCpf varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ALTER COLUMN VLRLAN varchar(250); ALTER TABLE CADASTRO_CLIENTE ADD PONREF varchar(250); ALTER TABLE PROTUARIO_PACIENTE ADD PROBIO varchar(250); ALTER TABLE PROTUARIO_PACIENTE ADD DIAPAC text; ALTER TABLE EVOLUCAO ADD IMGEVO varchar(250); ALTER TABLE PROTUARIO_PACIENTE ADD ANTCAN varchar(250); ALTER TABLE PROTUARIO_PACIENTE ADD ONDCAN varchar(250); ALTER TABLE PROTUARIO_PACIENTE ADD TIPANT varchar(250); ALTER TABLE USUARIO ADD FOTUSU varchar(250); ALTER TABLE USUARIO_PERMISSOES ADD ALTEVO int; ALTER TABLE CADASTRO_PACIENTE ADD PONREF varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD RESPAG varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD DATPAG varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD FORPAG varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD CONBAN varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD VLRPAG varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD VLRACR varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD VLRDES varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD VLRJUR varchar(250); ALTER TABLE LANCAMENTO_FINANCEIRO ADD OBSPAG varchar(250); ALTER TABLE AGENDA_CADASTRO ADD usu_per varchar(250); ALTER TABLE EMPRESA ADD QTDUSU int; ALTER TABLE EMPRESA ADD VERSAO varchar(50);"
		);

		for($i = 0; count($val) > $i; $i++){
			$con->connect($this->server, $this->database, $this->user, $this->pass, $cont[$val[$i]]);
		}

		return "ok";

	}

	function insertCities(){

		$con = new connection();
		$sql = "";
		$aux = 0;

		$file = fopen("log/cities.txt", "r") or die("Unable to open file!");
		$content = fread($file,filesize("log/cities.txt"));
		fclose($file);

		$data = explode(",", $content);

		for($i = 0; count($data) > $i; $i++){
			$val = str_replace('"',"",str_replace("(","",str_replace(")","",$data[$i])));
			if(strlen($val) > 0){
				$aux++;
				$sql = $sql . "INSERT INTO CIDADE VALUES ('$val'); ";
				if($aux > 20){
					$aux = 0;
					$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
					$sql = "";
				}
			}
		}

		$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		$this->insertNeighbors();

		return "ok";

	}

	function insertNeighbors(){

		$con = new connection();
		$sql = "";
		$old = "";
		$id = 0;

		$file = fopen("log/neighbors.txt", "r") or die("Unable to open file!");
		$content = fread($file,filesize("log/neighbors.txt"));
		fclose($file);

		$data = explode("),", $content);

		for($i = 0; count($data) > $i; $i++){

			$city = explode(",",$data[$i])[0];
			$neig = explode(",",$data[$i])[1];

			if($old == "" || $old != $city){
				if($old == ""){
					$id = $con->connect($this->server, $this->database, $this->user, $this->pass, "SELECT CODCID FROM CIDADE WHERE NOMCID = '$city';")[0]['CODCID'];
					$sql = "INSERT INTO BAIRROS VALUES ($id, '$neig'); ";
				} else{
					$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
					$id = $con->connect($this->server, $this->database, $this->user, $this->pass, "SELECT CODCID FROM CIDADE WHERE NOMCID = '$city';")[0]['CODCID'];
					$sql = "";
					$sql = "INSERT INTO BAIRROS VALUES ($id, '$neig'); ";
				}
			} else {
				$sql = $sql . "INSERT INTO BAIRROS VALUES ($id, '$neig'); ";
			}

			$old = $city;

		}

		$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		return "ok";

	}

	function rollbackQuery(){

		set_time_limit(10000);

		$con = new connection();
		$data = new ArrayObject();

		$sql = "";
		$index = 0;

		$aux = file_get_contents('nome do arquivo.txt', true);
		$data = get_object_vars(json_decode($aux));

		foreach($data as $val){
			$newValue = $val->new;
			$oldValue = $val->old;
			if($newValue != $oldValue && $newValue != ""){
				$sql = $sql . "UPDATE CADASTRO_PACIENTE SET CPFPAC = '$oldValue' WHERE CPFPAC = '$newValue'; ";
			}
			$index++;
			if($index > 300){
				$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);
				$sql = "";
				$index = 0;
			}
		}
		$con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

		return "ok";

	}

}

//Código descartado que eu não quis jogar fora :D
// function fixSheetId(){

//   set_time_limit(10000);

//   $con = new connection();
//   $dataDel = new ArrayObject();
//   $dataUpd = new ArrayObject();
//   $dataCre = new ArrayObject();
//   $logs = new logs();
//   $fullSql = "";

//   $sql = "SELECT CodLam, COUNT(*) AS QNT FROM ID_LAMINA GROUP BY CodLam HAVING COUNT(*) > 1; ";
//   $oldReg = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

//   foreach($oldReg as $reg){

//     $code = $reg['CodLam'];
//     $sql = "SELECT * FROM ID_LAMINA WHERE CodLam = '$code';";
//     $values = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

//     foreach($values as $val){

//       $sheetCode = $val['CodLam'];
//       $examCode = $val['CodPro'];

//       if($val['CodPro'] == 0){

//         $fullSql = $fullSql . "DELETE TOP(1) FROM ID_LAMINA WHERE CodLam = '$sheetCode' AND CodPro = '0'; ";
//         $dataDel->append(array("CodLam"=>$val['CodLam'], "CodPro"=>$val['CodPro'], "DatGer"=>$val['DatGer']));

//       } else {

//         $sql = "SELECT TOP(1) CodLam FROM ID_LAMINA ORDER BY DatGer DESC; ";
//         $lastCode = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

//         $newCode = substr($lastCode['CodLam'], -4)+1;
//         for($i = strlen($newCode); $i < 4; $i++){
//           $newCode = "0" . $newCode;
//         }

//         $fixCode = substr($newCode, 0, 3) . $newCode;
//         $fullSql = $fullSql . "UPDATE ID_LAMINA SET CodLam = '$fixCode' WHERE CodLam = '$sheetCode' AND CodPro = '$examCode'; ";
//         $fullSql = $fullSql . "UPDATE PROTUARIO_PACIENTE SET RCITO_LAM = '$fixCode' WHERE CODPRO = '$examCode'; ";

//         $dataUpd->append(array("old"=>$sheetCode, "new"=>$fixCode));

//       }

//     }

//     $con->connect($this->server, $this->database, $this->user, $this->pass, $fullSql);
//     $fullSql = "";

//   }

//   $sql = "SELECT RCITO_LAM, COUNT(*) AS QNT FROM PROTUARIO_PACIENTE WHERE LEN(RCITO_LAM) > 0 GROUP BY RCITO_LAM HAVING COUNT(*) > 1; ";
//   $oldReg = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

//   foreach($oldReg as $reg){

//     $code = $reg['RCITO_LAM'];
//     $sql = "SELECT * FROM PROTUARIO_PACIENTE WHERE RCITO_LAM = '$code'; ";
//     $values = $con->connect($this->server, $this->database, $this->user, $this->pass, $sql);

//   }

//   $logs->saveLog("LAMINAS ALTERADAS", json_encode($dataUpd));
//   $logs->saveLog("LAMINAS EXCLUIDAS", json_encode($dataDel));

//   return "ok";

// }
