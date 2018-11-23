<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List of all resource type modules in course
 *
 * @package   moodlecore
 * @copyright 2009 Petr Skoda (http://skodak.org)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once("$CFG->libdir/resourcelib.php");
require_once($CFG->dirroot . '/blocks/tutor/lib.php');

$id = required_param('id', PARAM_INT); // course id

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

$PAGE->set_pagelayout('course');
require_course_login($course, true);

// get list of all resource-like modules
$allmodules = $DB->get_records('modules', array('visible'=>1));
$modules = array();
foreach ($allmodules as $key=>$module) {
    $modname = $module->name; //echo " MÓDULO ".$modname;
    $libfile = "$CFG->dirroot/mod/$modname/lib.php";
    if (!file_exists($libfile)) {
        continue;
    }
    $archetype = plugin_supports('mod', $modname, FEATURE_MOD_ARCHETYPE, MOD_ARCHETYPE_OTHER);
    if ($archetype != MOD_ARCHETYPE_RESOURCE) {
        continue;
    }

    $modules[$modname] = get_string('modulename', $modname);
	
    //some hacky nasic logging
    add_to_log($course->id, $modname, 'view all', "index.php?id=$course->id", '');
	
}

$strresources    = get_string('resources');
$stractivities   = get_string('activities');
$strsectionname  = get_string('sectionname', 'format_'.$course->format);
$strname         = get_string('name');
$strintro        = get_string('moduleintro');
$strlastmodified = get_string('lastmodified');

//$PAGE->set_url('/course/resources.php', array('id' => $course->id));
$PAGE->set_title($course->shortname.': '.$strresources);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add($strresources.' + '.$stractivities);


echo $OUTPUT->header();

//Se obtienen los roles en el curso del usuario que intenta acceder
//Si tiene alguno distinto de 5(id del rol 'estudiante') se le permite el acceso
//Si el usuario unicamente es estudiante se le redirige a la página de inicio del curso
$context = get_context_instance(CONTEXT_COURSE,$course->id);
$can_access=false;
if ($roles = get_user_roles($context, $USER->id)) {
	foreach ($roles as $role) {
		if( $role->roleid != 5){ //El 5 es el id del rol estudiante
			$can_access=true;
		}
	}
}

if(!$can_access){
	$message= "No puedes acceder a esta página. Se te redirigirá de nuevo a la página del curso en 5 segundos.";
	redirect("$CFG->wwwroot/course/view.php?id=$course->id", $message, 5) ;

}else{
//Arrays que contienen los mensajes tanto en español como en portugues
$mensajes_es = array('Recurso y actividades iniciales', // 0
 			'<b>Atención</b>: Editar el recurso y actividad inicial implica borrar de la base de datos todos los requisitos definidos hasta el momento', // 1
			'Seleccione un recurso o una actividad para editar sus pre-requisitos', // 2
			'Recursos y actividades', // 3
			'Notas para los distintos perfiles', // 4
			'¿Mostrar notas sobre 100?', //5
			'Las notas mostradas en la tabla son las actuales, y para modificarlas habrá que rellenar todos los campos.', //6
			'Nota mínima', // 7
			'Nota máxima', // 8
			'Perfil básico', // 9
			'Perfil medio', // 10 
			'Perfil avanzado', // 11
			'Selecciona', // 12
			'Guardar valores', // 13
			'Recurso inicial: ', // 14
			'Actividad inicial: ', // 15
			'Seleccione el nivel de las actividades(General, Básico, Medio, Avanzado)', // 16
			'Recursos: ', // 17
			'Actividades:', // 18
			'Cuestionarios: ', // 19
			'Seleccione un recurso y una actividad para iniciar el estudio: ', // 20
			'Guardar perfil' //21
			);

$mensajes_pt_br = array('Recurso e atividade iniciais', // 0
			'<b>Atenção</b>: Editar os recursos e atividades iniciais implica em apagar no banco de dados todos os pré-requisitos definidos até o momento.', // 1
			'Selecione um recurso ou uma atividade para informar seus pré-requisitos', // 2
			'Recursos e atividades:', // 3
			'Notas para os diferentes perfis', // 4
			'Mostrar notas sobre 100?', // 5
			'As notas mostradas na tabela são as atuais, e para modificá-las você terá que preencher todos os campos', // 6
			'Nota mínima', // 7
			'Nota máxima', // 8
			'Perfil básico', // 9
			'Perfil médio', // 10
			'Perfil avançado', // 11
			'Seleciona', // 12
			'Salvar valores', //13
			'Recurso inicial: ', //14
			'Atividade inicial: ', //15
			'Selecione o nível das atividades (Geral, Básico, Médio, Avançado):', // 16
			'Recursos: ', // 17
			'Atividades:', // 18
			'Questionários: ', // 19
			'Selecione um recurso e uma atividade para iniciar o estudo: ', // 20
			'Grava perfil' // 21
			);
//Dependiendo del idioma establecido se selecciona un array de mensajes u otro
$lang = current_language();
if($lang == 'es'){
	$mensajes = $mensajes_es;
}
if($lang == 'pt_br'){
	$mensajes = $mensajes_pt_br;
}

$modinfo = get_fast_modinfo($course); // $modinfo->cms é um array

/*
$cms = array();
$resources = array();
$assign = array();
*/

if (isset($_GET['acao'])){
	$acao = $_GET['acao'];
}


foreach ($modinfo->cms as $cm) {

    if (!$cm->uservisible) {
        continue;
    }
	if (!array_key_exists($cm->modname, $modules)) {
        continue;
    }

    if (!$cm->has_view()) {
        // Exclude label and similar
        continue;
    }
	
    $cms[$cm->id] = $cm;	
    $resources[$cm->modname][] = $cm->instance;
	
    /*$cm->modname; //nome do recurso (page, resource, forum)
    $cm->name; //nome dado ao recurso (leitura1, etc)
    $cm->instance; //nro instancia do recurso
    $cm->id; //id do recurso*/
}

// preload instances

foreach ($resources as $modname=>$instances) {
	$resources[$modname] = $DB->get_records_list($modname, 'id', $instances, 'id', 'id,name,intro,introformat,timemodified');		
}

if (!$cms) {
    notice(get_string('thereareno', 'moodle', $strresources), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

if (isset($_POST['perfilRec'])){
	$arrayteste = $_POST['perfilRec'];		
	$params[] = $id;
	$DB->delete_records_select('tutor_rec_at_perfil', 'curso_id = ?', $params);
	foreach ($arrayteste as $aP){
		$var = explode(",",$aP);	
		$record = new stdClass();
		$record->curso_id = $id;
		$record->rec_ativ_id = $var[0];
		$record->perfil_id = $var[1];		
		$DB->insert_record('tutor_rec_at_perfil', $record, false);
	}	
}

$result2 = $DB->get_records_sql('SELECT * FROM {tutor_rec_at_perfil} WHERE curso_id = ?', array( $id));
foreach ($result2 as $res2){
	$ativComPerfil[] = $res2->rec_ativ_id;
	$perfilDaAtivID[$res2->rec_ativ_id] = $res2->perfil_id;
	$contaAtividadesComPerfil++;
}

foreach ($cms as $cm) {
	if (!isset($resources[$cm->modname][$cm->instance])) {
		continue;
	}
	$resource = $resources[$cm->modname][$cm->instance];
	if ($cm->id == $perfilDaAtivID[$res2->rec_ativ_id]){
		$perfilDaAtivNome[$res2->rec_ativ_id] = $cm->name;
	}
	$contaRecAtNoCurso++;
} 
			
foreach ($modinfo->instances['assign'] as $cm) {
	//echo $cm->name;
	if (!$cm->uservisible) {
		continue;
	}
	if ($cm->id == $perfilDaAtivID[$res2->rec_ativ_id]){
		$perfilDaAtivNome[$res2->rec_ativ_id] = $cm->name;
	}
	$contaRecAtNoCurso++;
}

if ($acao == 'editar'){
	$params[] = $id;
	$DB->delete_records_select('tutor_dependencia', 'curso_id = ?', $params);
}

//Si la accion es guardar, hay que modificar los valores de la tabla de notas en la base de datos
//Se comprueba que se ha introducido tanto la nota minima como la maxima de cada perfil
//Si la nota es menor a 10, hay que traducirla a un valor sobre 100 para guardarla en la base de datos
if ($acao == 'guardar'){

	if (isset($_POST['basico_minima']) && isset($_POST['basico_maxima'])){
		$record = new stdClass();
		$record->id = 1;
		$record->nome = 'Básico';
		if($_POST['basico_minima'] < 10){
			$_POST['basico_minima'] = $_POST['basico_minima'] * 10;
		}
		$record->nota_min = $_POST['basico_minima'];
		if($_POST['basico_maxima'] < 10){
			$_POST['basico_maxima'] = $_POST['basico_maxima'] * 10;
		}
		$record->nota_max = $_POST['basico_maxima'];		
		$DB->update_record('tutor_perfil', $record); 
	}
	if (isset($_POST['medio_minima']) && isset($_POST['medio_maxima'])){
		$record = new stdClass();
		$record->id = 2;
		$record->nome = 'Médio';
		if($_POST['medio_minima'] < 10){
			$_POST['medio_minima'] = $_POST['medio_minima'] * 10;		
		}
		$record->nota_min = $_POST['medio_minima'];	
		if ($_POST['medio_maxima'] < 10){
			$_POST['medio_maxima'] = $_POST['medio_maxima'] * 10;
		}
		$record->nota_max = $_POST['medio_maxima'];		
		$DB->update_record('tutor_perfil', $record);  
	}
	if (isset($_POST['avanzado_minima']) ){
		$record = new stdClass();
		$record->id = 3;
		$record->nome = 'Avançado';
		if($_POST['avanzado_minima'] < 10){
			$_POST['avanzado_minima'] = $_POST['avanzado_minima'] * 10;
		}
		$record->nota_min = $_POST['avanzado_minima'];
		$DB->update_record('tutor_perfil', $record);  
	}
}


$result3 = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND pre_req_id = ?', array( $id ,'0' ));
foreach ($result3 as $res3){
	$contIniciais++;
}	

$result4 = $DB->get_records_sql('SELECT * FROM {tutor_perfil}');

if ($contaAtividadesComPerfil < $contaRecAtNoCurso){
?>	
	<form name="recAtForPerfil" method="post" action="tutor_form.php?id=<?php echo $id ?>">
	<b></b><?php echo $mensajes[16];?></b><br><br>
	<b><?php echo $mensajes[17];?></b><br><br>	
	<?php 
     
	foreach ($cms as $cm) {
		if (!isset($resources[$cm->modname][$cm->instance])) {
				continue;
		}
		$resource = $resources[$cm->modname][$cm->instance];			
		?>
		<SELECT style="margin-right:10px; width:120px" NAME="perfilRec[]" required="required">
		<?php
		if (in_array($cm->id, $ativComPerfil)){
			foreach($result4 as $res4){	
				if ($res4->id == $perfilDaAtivID[$cm->id]) {?>		
					<OPTION SELECTED value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION> 
				<?php 
				}else{ 
				?>
					<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION>
				<?php				
				}
			}	
		}else{	
			?>
			<OPTION SELECTED></OPTION>
			<?php
			foreach($result4 as $res4){	
			?>		
				<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION><?php				
			}
		}?> 
		</SELECT> <?php
		echo checkTranslation($resource->name);
		echo "<br>";
			
	} ?>
		
	<br><br><b><?php echo $mensajes[18];?></b><br><br>
		
		
	<?php 
	foreach ($modinfo->instances['assign'] as $cm) {
		
		if (!$cm->uservisible) {
			continue;
		}	
		?>
		<SELECT style="margin-right:10px; width:120px" NAME="perfilRec[]" required="required">
		<?php
		if (in_array($cm->id, $ativComPerfil)){
			foreach($result4 as $res4){	
				if ($res4->id == $perfilDaAtivID[$cm->id]) {?>		
					<OPTION SELECTED value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION> 
				<?php 
				}else{ 
				?>
					<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION>
				<?php				
				}
			}
		}else{	
			?>
			<OPTION SELECTED></OPTION><?php
			foreach($result4 as $res4){	?>		
				<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION><?php				
			}
		}?> 
		</SELECT> 
		<?php
		echo checkTranslation($cm->name);
		echo "<br>";		
	}	
	?>
	
	<br><br><b><?php echo $mensajes[19];?></b><br><br>
		
		
	<?php 
	foreach ($modinfo->instances['quiz'] as $cm) {
	
		if (!$cm->uservisible) {
			continue;
		}	
		?>
		<SELECT style="margin-right:10px; width:120px" NAME="perfilRec[]" required="required">
		<?php
		if (in_array($cm->id, $ativComPerfil)){
			foreach($result4 as $res4){	
				if ($res4->id == $perfilDaAtivID[$cm->id]) {?>		
					<OPTION SELECTED value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION> 
				<?php 
				}else{ 
				?>
					<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION>
				<?php				
				}
			}
		}else{	
			?>
			<OPTION SELECTED></OPTION><?php
			foreach($result4 as $res4){	?>		
				<OPTION value="<?php echo $cm->id.",".$res4->id; ?>"><?php echo $res4->nome ?></OPTION><?php				
			}
		}?> 
		</SELECT> <?php
		echo checkTranslation($cm->name);
		echo "<br>";		
	}	
	?>
	
		
			<br><input type="submit" style="width: 100px;" value="<?php echo $mensajes[21];?>" />
		</form>

<?php	
	
} else if ($contIniciais < 1){
	?>
	<html>
	<form name="listaRecAtiv" method="post" action="tutor_lista.php?id=<?php echo $id ?>">
	<b></b><?php echo $mensajes[20];?></b><br><br>
	<b><?php echo $mensajes[14];?></b><br><br>
	<?php 
	foreach ($cms as $cm) {
		if (!isset($resources[$cm->modname][$cm->instance])) {
			continue;
		}
		$resource = $resources[$cm->modname][$cm->instance];
		?>
		<input type="radio" name="recurso" required="required" value="<?php echo $cm->id ?>"> <?php echo checkTranslation($resource->name) ?><br>
		<?php 
	}?>
		
	<br><br><b><?php echo $mensajes[15];?></b><br><br>
	<?php 
	foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	
		?>	
		<input type="radio" name="atividade" required="required" value="<?php echo $cm->id ?>"> <?php echo checkTranslation($cm->name) ?><br>	
		<?php  
	}	
		
	foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	
	 	?>	
		<input type="radio" name="atividade" required="required" value="<?php echo $cm->id ?>"> <?php echo checkTranslation($cm->name) ?><br>
		<?php  
	}	
		
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) { ?>	
		  	<input type="radio" name="atividade" required="required" value="<?php echo $cm->id?>"> <?php echo $cm->name ?><br>
			<?php  
		}
	}
	?>
		
	<br><input type="submit" style="width: 100px;" value="<?php echo $mensajes[12]?>" />
	</form>
	</html>
	<?php
} else{
	?>
	<p><b><?php echo $mensajes[0]?></b></p>
	<?php
	$result = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND pre_req_id = ?', array( $id , '0' ));
	foreach ($result as $res){
		foreach ($cms as $cm){
			if (($cm->id == $res->rec_ativ_id) and ($recLista < '1')){ // and (!$recu)
				$recLista++;
				echo $mensajes[14]. checkTranslation($cm->name) .'<br>';
				$_SESSION['idRecInic'] = $cm->id;
			}
		}
		foreach ($modinfo->instances['assign'] as $cm) {
			if (($cm->id == $res->rec_ativ_id) and ($ativLista < '1')){ // and (!$ati)
				$ativLista++;
				echo $mensajes[15]. checkTranslation($cm->name) .'<br>';
				$_SESSION['idForumInic'] = '';
				$_SESSION['idAtivInic'] = $cm->id;
			}
		}
	
		foreach ($modinfo->instances['quiz'] as $cm) {
		
			if (($cm->id == $res->rec_ativ_id) and ($quizLista < '1')){ // and (!$ati)
				$quizLista++;
				echo $mensajes[15]. checkTranslation($cm->name) .'<br>';
				$_SESSION['idForumInic'] = '';
				$_SESSION['idAtivInic'] = $cm->id;
			}
		}
	
		foreach ($modinfo->instances['forum'] as $cm) {
			if (($cm->id == $res->rec_ativ_id) and ($forLista < '1')){ // and (!$ati)
				$forLista++;
				$_SESSION['idAtivInic'] = '';
				echo $mensajes[15]. checkTranslation($cm->name) .'<br>';
				$_SESSION['idForumInic'] = $cm->id;
			}
		}
	}

	?>

	<form name="editar" method="post" action="<?php echo "tutor_form.php?id=".$id."&amp;acao=editar"?>" value="editar">
	<input type="submit" style="margin:10px 10px 10px 0; width: 100px;" value="Editar"/>
	</form>
	<p><?php echo $mensajes[1]?></p>
	<?php
	$result1 = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND pre_req_id > ?', array( $id , '0' ));
	foreach ($result1 as $res1){		
		foreach ($cms as $cm) {				
			if ($cm->id == $res1->rec_ativ_id){ 		
				$arrRec[$res1->rec_ativ_id]++; 			
			}		
		}
		foreach ($modinfo->instances['assign'] as $cm) {
			if ($cm->id == $res1->rec_ativ_id){ 
				$arrAtiv[$res1->rec_ativ_id]++; 			
			}
		}	
		foreach ($modinfo->instances['quiz'] as $cm) {
			if ($cm->id == $res1->rec_ativ_id){ 
				$arrQuiz[$res1->rec_ativ_id]++; 			
			}
		}
		foreach ($modinfo->instances['forum'] as $cm) {
			if ($cm->id == $res1->rec_ativ_id){ 
				$arrFor[$res1->rec_ativ_id]++; 			
			}
		}
	}

	?>
	<br>
<script language="JavaScript">
function Selecionar()
{
	document.listaRecAtiv.action="tutor_depend.php?id=<?php echo $id ?>";
	document.forms.listaRecAtiv.submit();
}
</script>
<script language="JavaScript">
function VerGrafo()
{
	document.listaRecAtiv.action="tutor_arvore.php?id=<?php echo $id ?>";
	document.forms.listaRecAtiv.submit();
}
</script>
	<form name="listaRecAtiv" method="post">
	<p><?php echo $mensajes[2]?></p>
	<p><b><?php echo $mensajes[3]?></b></p>
	<SELECT style="margin-right:15px; width:120px" NAME="recAtiv" required="required">
			<OPTION SELECTED></OPTION>
			<optgroup label="Recursos">
			 
	<?php	
	foreach ($cms as $cm) {
		if (!isset($resources[$cm->modname][$cm->instance])) {
			continue;
		}
		//$resource = $resources[$cm->modname][$cm->instance];
		
		if ($cm->id != $_SESSION['idRecInic']){
			if ($arrRec[$cm->id] > 0){?>
				<OPTION value="<?php echo $cm->id ?>"><?php echo '(*) '.checkTranslation($cm->name)?></OPTION>
				<?php } else { ?>
    		  		<OPTION value="<?php echo $cm->id ?>"><?php echo checkTranslation($cm->name) ?></OPTION>	
			<?php 	
			}
		}
	} ?>
	</optgroup>
	<optgroup label="Atividades">
	<?php 
	foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}
		if ($cm->id != $_SESSION['idAtivInic']){ 	
			if ($arrAt[$cm->id] > 0){?>
				<OPTION value="<?php echo $cm->id ?>"><?php echo '(*) '. checkTranslation($cm->name)?></OPTION>
				<?php } else { ?>
    		  		<OPTION value="<?php echo $cm->id ?>"><?php echo checkTranslation($cm->name) ?></OPTION>	
			<?php
	 		}
		}
	}	
	?>
	</optgroup>
	<optgroup label="Questionários">
	<?php
	foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}
		if ($cm->id != $_SESSION['idAtivInic']){ 	
			if ($arrQuiz[$cm->id] > 0){?>
				<OPTION value="<?php echo $cm->id ?>"><?php echo '(*) '.checkTranslation($cm->name)?></OPTION>
				<?php } else { ?>
    		  		<OPTION value="<?php echo $cm->id ?>"><?php echo checkTranslation($cm->name) ?></OPTION>	
			<?php 	
			}
		}
	}
	?>
	</optgroup>
	<optgroup label="Fóruns">
	<?php
	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) { 
			if ($cm->id != $_SESSION['idForumInic']) {
				if ($arrFor[$cm->id] > 0){?>
					<OPTION value="<?php echo $cm->id ?>"><?php echo '(*) '. checkTranslation($cm->name)?></OPTION>
				<?php 
				} else { ?>
					<OPTION value="<?php echo $cm->id ?>"><?php echo checkTranslation($cm->name) ?></OPTION>	
				<?php
	 			}
			}
		}
	}
	?>
	</optgroup>
	</SELECT>
		<br><br><input type="button"  style="width: 100px;" value="<?php echo $mensajes[12]?>" onClick="Selecionar()"/>
		<input type="button" style="margin-left:65px; width: 200px;" value="Ver Grafo Pré-requisitos" onClick="VerGrafo()"/>
	</form>

<script language="JavaScript">
function GuardarValores()
{
	var checkBox = document.getElementById("myCheck");

	var basicom = document.getElementById("basico_minima");
	var basicoM = document.getElementById("basico_maxima");
	var mediom = document.getElementById("medio_minima");
	var medioM = document.getElementById("medio_maxima");
	var avanzadom = document.getElementById("avanzado_minima");
	var avanzadoM = document.getElementById("avanzado_maxima");

	if( basicom.value.length == 0 || basicoM.value.length == 0  || 
		mediom.value.length == 0 || medioM.value.length == 0 ||
		avanzadom.value.lenth == 0){
		window.alert("Rellena todos los campos para cambiar los valores.\nAhora se están mostrando los actuales");
		return;
	}

	if (checkBox.checked == true){
	   	var basico_minima = parseInt(basicom.value);
		var basico_maxima = parseInt(basicoM.value);
		var medio_minima = parseInt(mediom.value);
		var medio_maxima = parseInt(medioM.value);
		var avanzado_minima = parseInt(avanzadom.value);
		var avanzado_maxima = parseInt(avanzadoM.value);

		if(basico_minima < 0 || basico_minima > 100 || basico_maxima < 0 || basico_maxima > 100 ||
			medio_minima < 0 || medio_minima > 100 || medio_maxima < 0 || medio_maxima > 100 ||
			avanzado_minima < 0 || avanzado_minima > 100){
	
			window.alert("Los valores deben estar entre 0 y 100");
			return;
		}
	}else {
		var basico_minima = parseFloat(basicom.value);
		var basico_maxima = parseFloat(basicoM.value);
		var medio_minima = parseFloat(mediom.value);
		var medio_maxima = parseFloat(medioM.value);
		var avanzado_minima = parseFloat(avanzadom.value);
		var avanzado_maxima = parseFloat(avanzadoM.value);
	
		if(basico_minima < 0 || basico_minima > 10 || basico_maxima < 0 || basico_maxima > 10 ||
			medio_minima < 0 || medio_minima > 10 || medio_maxima < 0 || medio_maxima > 10 ||
			avanzado_minima < 0 || avanzado_minima > 10){

			window.alert("Los valores deben estar entre 0 y 10");
			return;
		}	   	
	}
	//La nota minima de los perfiles debe ser inferior a la maxima
	if(basico_minima >= basico_maxima ){
		window.alert("El rango del perfil Basico no es valido, revísalo. ");
	}
	else if(medio_minima >= medio_maxima){
		window.alert("El rango del perfil Medio no es valido, revísalo. ");
	}
	else if(avanzado_minima >= avanzado_maxima){
		window.alert("El rango del perfil Avanzado no es valido, revísalo. ");
	}
	//La nota maxima de los perfiles debe ser menor que la minima de los siguientes	
	else if(basico_maxima != medio_minima){
		window.alert("La maxima del basico y la minima del medio deben ser iguales.");
	}
	else if(medio_maxima != avanzado_minima){
		window.alert("La maxima del medio y la minima del avanzado deben ser iguales.");
	}else {
		var c = window.confirm("Todo OK. Confirma para guardar los nuevos valores.");
		if(c== true){
			document.guardar.action="tutor_form.php?id=<?php echo $id ?>&acao=guardar";
			document.forms.guardar.submit();
		}
	}
}
</script>	
<script>
function cambiar() {
	var checkBox = document.getElementById("myCheck");

	var basicom = document.getElementById("basico_minima");
	var basicoM = document.getElementById("basico_maxima");
	var mediom = document.getElementById("medio_minima");
	var medioM = document.getElementById("medio_maxima");
	var avanzadom = document.getElementById("avanzado_minima");
	var avanzadoM = document.getElementById("avanzado_maxima");

	if (checkBox.checked == true){
		var basico_minima = parseFloat(basicom.value);
		var basico_maxima = parseFloat(basicoM.value);
		var medio_minima = parseFloat(mediom.value);
		var medio_maxima = parseFloat(medioM.value);
		var avanzado_minima = parseFloat(avanzadom.value);
		var avanzado_maxima = parseFloat(avanzadoM.value);

		basicom.value = parseInt(basico_minima*10);
		basicoM.value = parseInt(basico_maxima*10);
		mediom.value = parseInt(medio_minima*10);
		medioM.value = parseInt(medio_maxima*10);
		avanzadom.value = parseInt(avanzado_minima*10);
		avanzadoM.value = parseInt(avanzado_maxima*10);

		basicom.placeholder = basicom.placeholder * 10;
		basicoM.placeholder = basicoM.placeholder * 10;
		mediom.placeholder = mediom.placeholder * 10;
		medioM.placeholder = medioM.placeholder * 10;
		avanzadom.placeholder = avanzadom.placeholder * 10;

		basicom.step = 1;
		basicoM.step = 1;
		mediom.step = 1;
		medioM.step = 1;
		avanzadom.step = 1;
		avanzadoM.step = 1;
	} 
	else {
		var basico_minima = parseInt(basicom.value);
		var basico_maxima = parseInt(basicoM.value);
		var medio_minima = parseInt(mediom.value);
		var medio_maxima = parseInt(medioM.value);
		var avanzado_minima = parseInt(avanzadom.value);
		var avanzado_maxima = parseInt(avanzadoM.value);

		basicom.value=basico_minima/10;
		basicoM.value=basico_maxima/10;
		mediom.value=medio_minima/10;
		medioM.value=medio_maxima/10;
		avanzadom.value=avanzado_minima/10;
		avanzadoM.value=avanzado_maxima/10;

		basicom.placeholder = basicom.placeholder / 10;
		basicoM.placeholder = basicoM.placeholder / 10;
		mediom.placeholder = mediom.placeholder / 10;
		medioM.placeholder = medioM.placeholder / 10;
		avanzadom.placeholder = avanzadom.placeholder / 10;

		basicom.step=0.1;
		basicoM.step=0.1;
		mediom.step=0.1;
		medioM.step=0.1;
		avanzadom.step=0.1;
		avanzadoM.step=0.1;
	}
}
</script>	
	<?php 
	$basico = $DB->get_record('tutor_perfil', array('id'=>'1')); 	
	$medio = $DB->get_record('tutor_perfil', array('id'=>'2')); 
	$avanzado = $DB->get_record('tutor_perfil', array('id'=>'3')); 
	?>

	</br></br><p><b><?php echo $mensajes[4] ?></b></p>

	<input type="checkbox" id="myCheck"  onclick="cambiar()"><?php echo $mensajes[5] ?></br>

	<p><?php echo $mensajes[6] ?></p>

	<form name="guardar" method="post" action="<?php echo "tutor_form.php?id=".$id."&amp;acao=guardar"?>" value="guardar">

	<table>
	 	<tr>
	    		<td></td>
	    		<td style="text-align:center;"><?php echo $mensajes[7] ?></td>
	    		<td style="text-align:center;"><?php echo $mensajes[8] ?></td>
	  	</tr>
		<tr>
			<td><b><?php echo $mensajes[9] ?> </b> </td>
			<td><input type="number" id ="basico_minima" step="0.1" placeholder="<?php echo $basico->nota_min/10; ?>" name="basico_minima"></td>
			<td><input type="number" id ="basico_maxima" step="0.1" placeholder="<?php echo $basico->nota_max/10; ?>" name="basico_maxima"></td>
		</tr>
		<tr>
			<td><b><?php echo $mensajes[10] ?> </b> </td>
			<td><input type="number" id ="medio_minima" step="0.1" placeholder="<?php echo $medio->nota_min/10; ?>" name="medio_minima"></td>
			<td><input type="number" id ="medio_maxima" step="0.1" placeholder="<?php echo $medio->nota_max/10; ?>" name="medio_maxima"></td>
		</tr>
		<tr>
			<td><b><?php echo $mensajes[11] ?>  &nbsp;</b> </td>
			<td><input type="number" id ="avanzado_minima" step="0.1" placeholder="<?php echo $avanzado->nota_min/10; ?>" name="avanzado_minima"></td>
			<td><input type="number" id ="avanzado_maxima" value="10" disabled></td>
	  	</tr>
	</table>

	</br><input type="button" value="<?php echo $mensajes[13]?>" onClick="GuardarValores()"/>
	</form>
	</html>
<?php
}


}//fin del else de acceso a la pagina

echo $OUTPUT->footer();


