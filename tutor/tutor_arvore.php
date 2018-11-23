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
	 redirect("$CFG->wwwroot/course/view.php?id=$course->id") ;

}else{
//Arrays que contienen los mensajes tanto en español como en portugues
$mensajes_es = array('Recurso y actividad iniciales', 
			'Recurso inicial: ',
			'Actividad inicial: ',
			'Árbol de pre-requisitos del curso');

$mensajes_pt_br = array('Recurso e atividade iniciais',
			'Recurso incial: ',
			'Atividade inicial: ',
			'Arvore de Pré-requisitos da turma');
//Dependiendo del idioma establecido se selecciona un array de mensajes u otro
$lang = current_language();
if($lang == 'pt_br'){
	$mensajes= $mensajes_pt_br;
}
if($lang == 'es'){
	$mensajes = $mensajes_es;
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
<p><b><?php echo $mensajes[0]?></b></p>
<?php

foreach ($cms as $cm) {	
	if ($cm->id == $_SESSION['idRecInic']){
		echo $mensajes[1] . checkTranslation($cm->name)."<br>";		
	}
}
foreach ($modinfo->instances['assign'] as $cm) {
	if (!$cm->uservisible) {
		continue;
	}		
	if ($cm->id == $_SESSION['idAtivInic']){		
		echo $mensajes[2] . checkTranslation($cm->name)."<br>";				
		}
	}
	
foreach ($modinfo->instances['quiz'] as $cm) {
	if (!$cm->uservisible) {
		continue;
	}		
	if ($cm->id == $_SESSION['idAtivInic']){		
		echo $mensajes[2]. checkTranslation($cm->name) ."<br>";				
		}
	}	
	
foreach ($modinfo->instances['forum'] as $cm) {
	if (!$cm->uservisible) {
		continue;
	}	

	if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) {
		if ($cm->id == $_SESSION['idForumInic']){
			echo $mensajes[2]. checkTranslation($cm->name)."<br>";					
		}
	}
}

?>
<br>
<p><?php echo $mensajes[3]?></p>
<?php  
 
$result = $DB->get_records_sql('SELECT * FROM {tutor_dependencia} WHERE curso_id = ?', array($id));
foreach ($result as $res){	
	$arrPreReq[] = $res->pre_req_id.",".$res->rec_ativ_id;		
} 


foreach($arrPreReq as $rec){
		 $what = array( 'ä','ã','à','á','â','ê','ë','è','é','ï','ì','í','ö','õ','ò','ó','ô','ü','ù','ú','û','À','Á','É','Í','Ó','Ú','ñ','Ñ','ç','Ç',' ','-','(',')',',',';',':','|','!','"','#','$','%','&','/','=','?','~','^','>','<','ª','º','.' );
		 $by   = array( 'a','a','a','a','a','e','e','e','e','i','i','i','o','o','o','o','o','u','u','u','u','A','A','E','I','O','U','n','n','c','C','_','','','','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','_','' );

	$var = explode(",",$rec);
	$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);
	if ($var[0]== '0'){
		$var[0] = "";//$course->shortname;
	}
	foreach ($cms as $cm) {	
		if ($cm->id == $var[0]){
			$string = str_replace($what,$by,$cm->name);
			$var[0] = $string;
			//echo "var 0 ".$var[0]; 			
		}else if($cm->id == $var[1]){			
			$string = str_replace($what,$by,$cm->name);
			$var[1] = $string;	
			//echo "var 1 ".$var[1];		
		}		
	}
	foreach ($modinfo->instances['assign'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
		if ($cm->id == $var[0]){			
			$string = str_replace($what,$by,$cm->name);
			$var[0] = $string;
		}else if($cm->id == $var[1]){			
			$string = str_replace($what,$by,$cm->name);
			$var[1] = $string;
		}
	}
	
	foreach ($modinfo->instances['quiz'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}		
		if ($cm->id == $var[0]){			
			$string = str_replace($what,$by,$cm->name);
			$var[0] = $string;
		}else if($cm->id == $var[1]){			
			$string = str_replace($what,$by,$cm->name);
			$var[1] = $string;
		}
	}
	
	foreach ($modinfo->instances['forum'] as $cm) {
		if (!$cm->uservisible) {
			continue;
		}

		if (($cm->name != 'News forum') && ($cm->name != 'Fórum de notícias')) {
			if ($cm->id == $var[0]){			
				$string = str_replace($what,$by,$cm->name);
				$var[0] = $string;		
			}else if($cm->id == $var[1]){			
				$string = str_replace($what,$by,$cm->name);
				$var[1] = $string;
			}
		}
	}
	//Para crear el grafo hay que tener en cuenta los dos elementos iniciales que tienen que ser distintos de los demás, ya que no tienen antecesor
	//Si no tienen antecesor(son los iniciales) unicamente se añade el elemento actual
	//Si si tienen antecesor se añaden el antecesor y el actual
	if($var[0] != ""){
		$var[0]=checkTranslation($var[0]);
		$var[1]=checkTranslation($var[1]);
		$graph = $graph.$var[0]."->".$var[1].";";
	}
	else{
		$var[1]=checkTranslation($var[1]);
		$graph = $graph.$var[1].";";
	}
	
//print_r($arrPreReq);
}

?>
<html>
	<img src="https://chart.googleapis.com/chart?cht=gv&chl=digraph{<?php echo $graph ?>}"  width=100%>
</html>
<?php
}
echo $OUTPUT->footer();



