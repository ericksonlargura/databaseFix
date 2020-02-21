<?php
session_start();
?>

<!DOCTYPE html>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<html>

<head>
	<title>Database Fix</title>

	<script src="https://code.jquery.com/jquery-3.4.1.min.js" integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo=" crossorigin="anonymous">
	</script>

	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">

	<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.bundle.min.js" integrity="sha384-xrRywqdh3PHs8keKZN+8zzc5TX0GRTLCcmivcbNJWm2rs5C8PRhcEn3czEjhAO9o" crossorigin="anonymous">
	</script>

	<style>
		.container {
			margin-top: 30px;
			margin-bottom: 30px;
		}

		.fix-height {
			min-height: 300px;
		}

		.card-footer {
			transition: 4s;
		}

		.mod {
			display: none;
			position: fixed;
			z-index: 1;
			padding-top: 100px;
			left: 0;
			top: 0;
			width: 100%;
			height: 100%;
			overflow: auto;
			background-color: rgb(0, 0, 0);
			background-color: rgba(0, 0, 0, 0.7);
		}

		.centralize {
			position: absolute;
			top: 50%;
			left: 50%;
			transform: translate(-50%, -50%);
		}

		.spinner-border {
			width: 400px;
			height: 400px;
		}

		.center-text {
			max-width: 250px;
			text-align: center;
		}
	</style>

</head>

<body>

	<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
		<a class="navbar-brand" href="#">WCARE DATABASE FIX</a>
	</nav>

	<div class="container">
		<div class="row">
			<div class="col">
				<div class="card text-center">
					<div class="card-header">
						Bem-vindo ao sistema de correção do banco de dados WCare
					</div>
					<div class="card-body">

						<?php if (!isset($_SESSION['page']) || $_SESSION['page'] == 0) { ?>

							<h5 class="card-title">Formulário para Correção</h5>
							<p class="card-text">Para realizar a correção, é necessário preencher o formulário abaixo:</p>
							<form method="post" action="controller.php" id="post-form">
								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Nome do Server</span>
									</div>
									<input name="server" type="text" class="form-control" <?php if (isset($_SESSION['server'])) { echo "value='" . $_SESSION['server'] . "'"; }; ?> required>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Nome do Banco</span>
									</div>
									<input name="database" type="text" class="form-control" <?php if (isset($_SESSION['database'])) { echo "value='" . $_SESSION['database'] . "'"; }; ?> required>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Login</span>
									</div>
									<input name="user" type="text" class="form-control" <?php if (isset($_SESSION['user'])) { echo "value='" . $_SESSION['user'] . "'"; } else { echo "value='sa'"; }; ?> required>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Senha</span>
									</div>
									<input name="pass" type="password" class="form-control" <?php if (isset($_SESSION['pass'])) { echo "value='" . $_SESSION['pass'] . "'"; } else { echo "value='Blxk-2346'"; }; ?> required>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<label class="input-group-text">Tipo</label>
									</div>
									<select name="type" class="custom-select" required id="select">
										<option value="" selected>Selecione</option>
										<option value="1">Texto</option>
										<option value="2">RG (CADASTRO_PACIENTE.REGPAC)</option>
										<option value="3">CPF (CADASTRO_PACIENTE.CPFPAC)</option>
										<option value="4">Localizar duplicados (CODPAC, CODPRO, CODLAM)</option>
										<option value="5" disabled>Corrigir CODPAC duplicado</option>
										<option value="6" disabled>Corrigir CODPRO</option>
										<option value="7">Alterações na estrutura</option>
										<option value="8">Adicionar CIDADES e BAIRROS no banco</option>
										<option value="9">Mover imagens de IMAGENS_PRONTUARIO para EVOLUCAO</option>
										<option value="10">Corrigir lâminas duplicadas</option>
										<option value="99">Rollback (Necessário configurar no php)</option>
									</select>
								</div>

								<div class="input-group mb-3 table" hidden>
									<div class="input-group-prepend">
										<span class="input-group-text">Tabela</span>
									</div>
									<input name="table" type="text" class="form-control table" <?php if (isset($_SESSION['table'])) { echo "value='" . $_SESSION['table'] . "'"; }; ?> required>
								</div>

								<div class="input-group mb-3 field" hidden>
									<div class="input-group-prepend">
										<span class="input-group-text">Campo</span>
									</div>
									<input name="field" type="text" class="form-control field" required>
								</div>

								<div class="input-group mb-3 old-codpac" hidden>
									<div class="input-group-prepend">
										<span class="input-group-text">CODPAC Antigo</span>
									</div>
									<input name="oldPac" type="text" class="form-control old-codpac" required>
								</div>

								<div class="input-group mb-3 new-codpac" hidden>
									<div class="input-group-prepend">
										<span class="input-group-text">CODPAC Novo</span>
									</div>
									<input name="newPac" type="text" class="form-control new-codpac" required>
								</div>

								<div class="row">
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="0" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela CIDADE" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="1" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela BAIRROS" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="2" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela MOVIMENTO_FINANCEIRO" disabled>
									</div>
								</div>

								<div class="row">
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="3" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela AGENDA" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="4" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela ESTOQUE" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="5" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela ESTOQUE_MOVIMENTO" disabled>
									</div>
								</div>

								<div class="row">
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="6" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela AGRUPAMENTO" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="7" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela CADASTRO_FORNECEDORES" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="8" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela FORMA_PAGAMENTO" disabled>
									</div>
								</div>

								<div class="row">
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="9" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela DEPOSITO" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="10" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela BIOPSIA_RESULTADO" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="11" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela BIOPSIA" disabled>
									</div>
								</div>

								<div class="row">
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="12" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Adicionar tabela CONTA_BANCARIA" disabled>
									</div>
									<div class="col input-group mb-3 checkboxes" hidden>
										<div class="input-group-prepend">
											<div class="input-group-text">
												<input name="checkb[]" value="13" type="checkbox">
											</div>
										</div>
										<input type="text" class="form-control" value="Alterações de tabela" disabled>
									</div>
								</div>

								<div class="input-group mb-3 old-pro" hidden>
									<div class="input-group-prepend">
										<span class="input-group-text">CODPRO Antigo</span>
									</div>
									<input name="oldPro" type="text" class="form-control old-pro" required>
								</div>


								<button name="verify" value="0" type="submit" href="#" class="btn btn-primary">Corrigir</button>
							</form>

						<?php } else if ($_SESSION['page'] == 1) { ?>

							<h5 class="card-title">Formulário para Correção de Texto</h5>
							<p class="card-text">Agora basta selecionar os campos, selecionar o novo nome, e todos os selecionados serão atualizados!</p>
							<form method="post" action="controller.php">

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Pesquisar...</span>
									</div>
									<input name="pass" type="text" class="form-control" id="search-input">
								</div>

								<div class="form-group">
									<label>Campos atuais:</label>
									<select name="oldValue[]" multiple class="form-control fix-height" id="multiselect">
										<?php foreach ($_SESSION['data'] as $data) {
											$val = substr(explode(':', json_encode($data, JSON_UNESCAPED_UNICODE))[1], 1, -2);
											if($val != null && $val != "ul" && $val != " " && strlen($val) > 0){
												echo "<option>$val</option>";
											}
										} ?>
									</select>
								</div>

								<div class="input-group mb-3">
									<div class="input-group-prepend">
										<span class="input-group-text">Nome</span>
									</div>
									<input name="newValue" type="text" class="form-control">
								</div>

								<button name="verify" value="1" type="submit" href="#" class="btn btn-primary">Confirmar</button>
								<button name="verify" value="99" type="submit" href="#" class="btn btn-secondary">Finalizar</button>
							</form>

						<?php } ?>

					</div>

					<?php
					if (isset($_SESSION['alert']) && strlen($_SESSION['alert']) > 0) {
						echo "<div class='card-footer text-muted alert-warning' id='alertDiv'><span>" . $_SESSION['alert'] . "</span>";
					} else {
						echo "<div class='card-footer text-muted'> Sistema em funcionamento!";
					}
					$_SESSION['alert'] = "";
					?>

				</div>
			</div>
		</div>
	</div>
	</div>

	<div id="mod-screen" class="mod">

		<div class="centralize">
			<div class="spinner-border text-light">
				<span class="sr-only">Carregando...</span>
			</div>
		</div>

		<div class="centralize center-text">
			<span class="text-light">Sistema em funcionamento! Favor não fechar a página, e de preferência, não mexer em nada! Assim que finalizar, você será redirecionado automaticamente para a página anterior. Este procedimento pode demorar de 1 minuto a 1 hora</span>
		</div>

	</div>

	<script>
		$("#select").change(function() { 
			$(".checkboxes").attr("hidden", true);
			$(".table").attr("hidden", true);
			$(".table").prop('disabled', true);
			$(".field").attr("hidden", true);
			$(".field").prop('disabled', true);
			$(".new-codpac").attr("hidden", true);
			$(".new-codpac").prop('disabled', true);
			$(".old-codpac").attr("hidden", true);
			$(".old-codpac").prop('disabled', true);
			$(".old-pro").attr("hidden", true);
			$(".old-pro").prop('disabled', true);
			if ($("#select").val() == 1) {
				$(".table").removeAttr("hidden");
				$(".table").prop('disabled', false);
				$(".field").removeAttr("hidden");
				$(".field").prop('disabled', false);
			} else if($("#select").val() == 5) {
				$(".new-codpac").removeAttr("hidden");
				$(".new-codpac").prop('disabled', false);
				$(".old-codpac").removeAttr("hidden");
				$(".old-codpac").prop('disabled', false);
			} else if($("#select").val() == 6) {
				$(".old-pro").prop('disabled', false);
				$(".old-pro").removeAttr("hidden");
			} else if($("#select").val() == 7) {
				$(".checkboxes").removeAttr("hidden");
			}
		});

		$("document").ready(function() {

			setTimeout(function() {
				$("#alertDiv").removeClass("alert-warning");
				$("#alertDiv span").fadeOut(function() {
					$(this).text("Sistema em funcionamento!").fadeIn();
				});
			}, 4000);

			$('#post-form').on("submit", function() {
				$('#mod-screen').css("display", "block");
			});

			$("#search-input").on("keyup", function() {
				var value = $(this).val().toLowerCase();
				$("#multiselect option").filter(function() {
					$(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
				});
			});

		});
	</script>

</body>

</html>