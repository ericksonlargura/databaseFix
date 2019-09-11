<?php
require "lib.php";
session_start();

if ($_POST['verify'] == 0) { //Tela inicial

	$_SESSION = $_POST;

	$dao = new dao($_SESSION['server'], $_SESSION['database'], $_SESSION['user'], $_SESSION['pass']);
	if ($dao->checkConnection()) {

		switch ($_SESSION['type']) {
			case 1:
				$data = $dao->readTextQuery($_POST['table'], $_POST['field']);
				$_SESSION['page'] = 1;
				$_SESSION['data'] = $data;
				break;
			case 2:
				$dao->updateRgQuery();
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Campo de RG atualizado com sucesso!";
				break;
			case 3:
				$dao->updateCpfQuery();
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Campo de CPF atualizado com sucesso!";
				break;
			case 4:
				$dao->logUserId();
				$dao->logExamId();
				$dao->logSheetId();
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Campos duplicados registrados!";
				break;
			case 5:
				$dao->fixUserId($_POST['newPac'], $_POST['oldPac']);
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Campos CODPAC duplicados atualizados!";
				break;
			case 6:
				$dao->fixRecordId($_POST['oldPro']);
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Campos CODPRO atualizados!";
				break;
			case 7:
				$dao->databaseStructure($_POST['checkb']);
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Banco de dados atualizado!";
				break;
			case 8:
				$dao->insertCities();
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Banco de dados atualizado!";
				break;
			case 99:
				$dao->rollbackQuery();
				$_SESSION['page'] = 0;
				$_SESSION['alert'] = "Rollback concluído!";
				break;
			default:
				break;
		}
	} else {
		$_SESSION['page'] = 0;
		$_SESSION['alert'] = "Não foi possível conectar-se ao banco!";
	}

	header("Location:index.php");
	exit();
} else if ($_POST['verify'] == 1) { //Tela do multiselect (Correção de texto)

	$dao = new dao($_SESSION['server'], $_SESSION['database'], $_SESSION['user'], $_SESSION['pass']);

	for ($i = 0; count($_POST['oldValue']) > $i; $i++) {
		$dao->updateTextQuery($_POST['oldValue'][$i], $_POST['newValue'], $_SESSION['table'], $_SESSION['field']);
	}

	$data = $dao->readTextQuery($_SESSION['table'], $_SESSION['field']);
	$_SESSION['data'] = $data;

	header("Location:index.php");
	exit();
} else if ($_POST['verify'] == 99) { //Limpa a sessão e sai

	$_SESSION['page'] = 0;
	header("Location:index.php");
	exit();
}
