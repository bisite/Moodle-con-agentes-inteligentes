<?php
	require_once('../../config.php');
	require_once("$CFG->libdir/resourcelib.php");

	header('Content-Type: text/event-stream');
	header('Cache-Control: no-cache');

	function sendMsg($id, $id_alumno, $id_curso) {
		echo "id: $id" . PHP_EOL;
		echo "data: {\n";
		echo "data: \"id\": \"$id\", \n";
		echo "data: \"id_alumno\": \"$id_alumno\", \n";
		echo "data: \"id_curso\": $id_curso\n";
		echo "data: }\n";
		echo PHP_EOL;
		ob_flush();
		flush();
	}

	$startedAt = time();

	do {
		// Cap connections at 10 seconds. The browser will reopen the connection on close
		if ((time() - $startedAt) > 10) {
			die();
		}

		//Se obtienen todos los registros de la tabla para ver que alumnos tienen que actualizar
		$result = $DB->get_records_sql('SELECT * FROM {tutor_nota_perfil} where nro_calculo = 0');
	
		//Para cada registro se envian el id del registro, el id del alumno y el id del curso y se marca como ya procesado
		foreach ($result as $res){
			$id = $res->id;
			$aluno_id = $res->aluno_id;
			$curso_id = $res->curso_id;

			//se envian los datos para que los alumnos comprueben si se dirigen a ellos
			sendMsg($id, $aluno_id, $curso_id);

			//Se marca como ya procesado para las proximas veces que se recuperen los registros
			$record = new stdClass();
			$record->id = $id;
			$record->nro_calculo = 1;	
			$DB->update_record('tutor_nota_perfil', $record); 		
		}	

		sleep(5);

	} while(true);
?>
