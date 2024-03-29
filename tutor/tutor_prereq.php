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
    $modname = $module->name;
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
	 redirect("$CFG->wwwroot/course/view.php?id=$course->id") ;

}else{

$mensajes_es = array('Recurso y actividades iniciales', // 0
			'Recurso inicial: ', // 1
			'Actividad inicial: ', // 2
 			'<b>Atención</b>: Editar el recurso y actividad inicial implica borrar de la base de datos todos los requisitos definidos hasta el momento', // 3
			'Seleccione un recurso o una actividad para editar sus pre-requisitos', // 4
			'Recurso/actividad: ', // 5
			'Pre-requisitos seleccionados', // 6
			'Recursos: ', // 7
			'Actividades: ', // 8
			'Cuestionarios: ', //9
			'Foros: ', // 10
			'Indicar pre-requisitos de otros recursos y actividades' // 11
			);

$mensajes_pt_br = array('Recurso e atividade iniciais', // 0
			'Recurso inicial: ', // 1
			'Atividade inicial: ', //2
			'<b>Atenção</b>: Editar os recursos e atividades iniciais implica em apagar no banco de dados todos os pré-requisitos definidos até o momento.', // 3
			'Selecione um recurso ou uma atividade para informar seus pré-requisitos', // 4
			'Recurso/atividade: ', // 5
			'Pré-requisitos selecionados', // 6
			'Recursos: ', // 7
			'Atividades: ', // 8
			'Questionários: ', // 9
			'Fóruns: ', //10
			'Indicar pré-requisitos de outros recursos e atividades' //11
			);

$lang = current_language();
if($lang == 'es'){
	$mensajes = $mensajes_es;
}
if($lang == 'pt_br'){
	$mensajes = $mensajes_pt_br;
}

$modinfo = get_fast_modinfo($course); // $modinfo->cms é um array

$cms = array();
$resources = array();
$assign = array();


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

}

// preload instances

foreach ($resources as $modname=>$instances) {
	$resources[$modname] = $DB->get_records_list($modname, 'id', $instances, 'id', 'id,name,intro,introformat,timemodified');		
}

if (!$cms) {
    notice(get_string('thereareno', 'moodle', $strresources), "$CFG->wwwroot/course/view.php?id=$course->id");
    exit;
}

	?>
<html>

<p><b><?php echo $mensajes[0];?></b></p>
<?php

foreach ($cms as $cm) {	
	if ($cm->id == $_SESSION['idRecInic']){
			echo $mensajes[1] .checkTranslation($cm->name)."<br>";		
		}
}
?>
<?php
foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $_SESSION['idAtivInic']){		
		echo $mensajes[2].checkTranslation($cm->name)."<br>";				
		}
	}
	
foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
	if ($cm->id == $_SESSION['idAtivInic']){		
		echo $mensajes[2].checkTranslation($cm->name)."<br>";				
		}
	}
	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}	

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) {
			if ($cm->id == $_SESSION['idForumInic']){
				echo $mensajes[2].checkTranslation($cm->name)."<br>";					
			}
		}
	}

?>

<form name="editar" method="post" action="<?php echo "tutor_form.php?id=".$id."&amp;acao=editar"?>" value="editar">
	<input type="submit" style="margin:10px 10px 10px 0; width: 100px;" value="Editar"/>
</form>
<p><?php echo $mensajes[3];?></p>

 <br>
 <p><?php echo $mensajes[4];?></p>
	<p><b><?php echo $mensajes[5];?></b></p>
  <SELECT style="margin-right:15px; width:120px" NAME="selecionada">
			<OPTION SELECTED><?php echo checkTranslation($_SESSION['selecionada']) ?></OPTION></SELECT>
  <br><br><p><b><?php echo $mensajes[6];?></b></p>
   <?php  

$params[] = $id;
$params[] = $_SESSION['selecionada_id'];	
$DB->delete_records_select('tutor_dependencia', 'curso_id = ? AND rec_ativ_id = ?', $params);   
  
$preRequi = isset($_POST['prereq']) ? $_POST['prereq'] : NULL;

$result2 = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ? AND rec_ativ_id = ? AND pre_req_id > ?', array( $id , $_SESSION['selecionada_id'],'0'));
foreach ($result2 as $res2){	
			$arrPreReq[$res2->rec_ativ_id][] = $res2->pre_req_id;		
	}
foreach($arrPreReq as $rec){		
			foreach ($rec as $re){
				$array_rec[] = $re;
	}
}

foreach($preRequi as $pre){	
	foreach ($cms as $cm) {	
		if ($cm->id == $pre){
			$rec_cont++;
			if ($rec_cont == '1'){
				echo '<b>'.$mensajes[7].'</b><br>';}
				echo '<p style="margin-left:75px;">'.checkTranslation($cm->name).'</p>';
				if (!in_array($cm->id, $array_rec)){
					$record = new stdClass();
					$record->curso_id = $id;
					$record->rec_ativ_id = $_SESSION['selecionada_id'];
					$record->pre_req_id = $cm->id;
					$DB->insert_record('tutor_dependencia', $record, false);
				}
		}
	}
    foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
		if ($cm->id == $pre){
			$at_cont++;		
			if ($at_cont == '1'){
				echo '<b>'.$mensajes[8].'</b><br>';}
				echo '<p style="margin-left:75px;">'.checkTranslation($cm->name).'</p>';
				if (!in_array($cm->id, $array_rec)){
					$record = new stdClass();
					$record->curso_id = $id;
					$record->rec_ativ_id = $_SESSION['selecionada_id'];
					$record->pre_req_id = $cm->id;
					$DB->insert_record('tutor_dependencia', $record, false);
				}
		}
	}

    foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
		if ($cm->id == $pre){
			$quiz_cont++;		
			if ($quiz_cont == '1'){
				echo '<b>'.$mensajes[9].'</b><br>';}
				echo '<p style="margin-left:75px;">'.checkTranslation($cm->name).'</p>';
				if (!in_array($cm->id, $array_rec)){
					$record = new stdClass();
					$record->curso_id = $id;
					$record->rec_ativ_id = $_SESSION['selecionada_id'];
					$record->pre_req_id = $cm->id;
					$DB->insert_record('tutor_dependencia', $record, false);
				}
		}
	}
	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}			
		if ($cm->id == $pre){			
			$for_cont++;
			if ($for_cont == '1'){
				echo '<b>'. $mensajes[10] .'</b><br>';}
				echo '<p style="margin-left:75px;">'.checkTranslation($cm->name).'</p>';
				if (!in_array($cm->id, $array_rec)){
					$record = new stdClass();
					$record->curso_id = $id;
					$record->rec_ativ_id = $_SESSION['selecionada_id'];
					$record->pre_req_id = $cm->id;
					$DB->insert_record('tutor_dependencia', $record, false);
				}				
			}
		}
}
	?> 
 <br> 

 <form name="continuar" method="post" action="tutor_lista.php?id=<?php echo $id ?>&acao=continuar">  
	<?php echo $mensajes[11];?>
		<input type="submit" value="Continuar" style="width: 100px;" />
	</form>
 <form name="finalizar" method="post" action="tutor_arvore.php?id=<?php echo $id ?>">  
		<br><br><input type="submit" style="width: 100px;" value="Finalizar" />
	</form>
</html>
<?php

}
echo $OUTPUT->footer();


